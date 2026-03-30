import os
import re
import uuid
import datetime
import requests
from bs4 import BeautifulSoup
import mysql.connector
from datetime import datetime
from urllib.parse import urlparse

# Database config
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder'
}

# Image storage path
storage_dir = os.path.join(os.getcwd(), 'storage', 'app', 'public', 'blog')
os.makedirs(storage_dir, exist_ok=True)

# Headers to bypass bot protections
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36'
}

def clean_html(html_content):
    soup = BeautifulSoup(html_content, 'html.parser')
    for div in soup.find_all(['script', 'iframe', 'style']):
        div.decompose()
    for ad in soup.find_all('div', class_=re.compile(r'ad|social-share|related')):
        ad.decompose()
    return str(soup)

def main():
    try:
        conn = mysql.connector.connect(**db_config)
    except mysql.connector.Error as err:
        print(f"Error connecting to MySQL: {err}")
        return
        
    cursor = conn.cursor(dictionary=True)
    
    # Delete existing scraped items to ensure a fresh scrape as requested
    print("Deleting previously scraped Shajgoj posts...")
    cursor.execute("DELETE FROM blog_posts WHERE author_name='Shajgoj Desk'")
    conn.commit()
    print("Deleted successfully!")
    
    # Check if a specific target category exists
    cursor.execute("SELECT id FROM blog_categories WHERE slug='health-tips'")
    result = cursor.fetchone()
    if not result:
        cursor.execute("INSERT INTO blog_categories (name, slug, created_at, updated_at) VALUES ('Health Tips', 'health-tips', NOW(), NOW())")
        conn.commit()
        cat_id = cursor.lastrowid
    else:
        cat_id = result['id']

    base_url = "https://www.shajgoj.com/category/health/"
    page = 1

    while True:
        print(f"Scraping Page {page}...")
        url = base_url if page == 1 else f"{base_url}page/{page}/"
        try:
            resp = requests.get(url, headers=headers)
            resp.raise_for_status()
        except requests.exceptions.RequestException as e:
            if getattr(resp, 'status_code', None) == 404:
                print(f"  -> Reached the last page. Scraping complete.")
                break
            print(f"Failed to fetch {url}: {e}. Stopping.")
            break

        soup = BeautifulSoup(resp.text, 'html.parser')
        
        articles = soup.find_all('article')
        if not articles:
            articles = soup.find_all('div', class_=re.compile(r'post|post-item'))

        if not articles:
            print("  -> No articles found on this page. Reached the end.")
            break

        print(f"  Found {len(articles)} posts on this page.")

        for article in articles:
            title_node = article.find(['h2', 'h3'], class_=re.compile(r'entry-title|post-title'))
            if not title_node or not title_node.find('a'):
                continue
            
            a_tag = title_node.find('a')
            post_title = a_tag.get_text(strip=True)
            post_href = a_tag.get('href')
            
            if not post_href or not post_href.startswith('http'):
                continue

            # Check for existing post
            cursor.execute("SELECT id FROM blog_posts WHERE title = %s", (post_title,))
            if cursor.fetchone():
                print(f"  [Skip] Already exists: {post_title}")
                continue

            print(f"  [Scraping] {post_title}")
            try:
                p_resp = requests.get(post_href, headers=headers)
                p_resp.raise_for_status()
            except requests.exceptions.RequestException as e:
                print(f"    Failed to fetch single post: {e}")
                continue
            
            p_soup = BeautifulSoup(p_resp.text, 'html.parser')
            
            # Content
            content_div = p_soup.find('div', class_=re.compile(r'sf-entry-content|entry-content'))
            if not content_div:
                content_div = p_soup.find('div', class_=re.compile(r'sf-main|articleBody'))
            
            if not content_div:
                print("    Content body not found! Skipping.")
                continue
                
            content_html = clean_html(str(content_div))
            fallback_excerpt = content_div.get_text(separator=' ', strip=True)[:250] + "..."
            
            # Find featured image
            img_url = None
            thumbnail_div = p_soup.find(['div', 'figure'], class_=re.compile(r'post-thumbnail|entry-image|sf-post-thumbnail|sf-featured-image'))
            if thumbnail_div and thumbnail_div.find('img'):
                img = thumbnail_div.find('img')
                img_url = img.get('data-src') or img.get('src')
                
            if not img_url:
                first_img = content_div.find('img')
                if first_img:
                    img_url = first_img.get('data-src') or first_img.get('src')

            if img_url and img_url.startswith('data:'):
                img_url = None

            # Extract SEO Description 
            seo_desc = fallback_excerpt
            meta_desc_tag = p_soup.find('meta', attrs={'name': 'description'}) or p_soup.find('meta', attrs={'property': 'og:description'})
            if meta_desc_tag and meta_desc_tag.get('content'):
                seo_desc = meta_desc_tag.get('content')

            # Extract SEO Keywords
            tags_list = []
            meta_keywords_tag = p_soup.find('meta', attrs={'name': 'keywords'})
            if meta_keywords_tag and meta_keywords_tag.get('content'):
                kws = meta_keywords_tag.get('content').split(',')
                tags_list.extend([k.strip() for k in kws if k.strip()])
            
            # Extract Tags from Body
            tags_div = p_soup.find(['span', 'div'], class_=re.compile(r'tags-links|post-tags|sf-tags'))
            if tags_div:
                for t in tags_div.find_all('a'):
                    tags_list.append(t.get_text(strip=True))

            # Remove duplicates & join
            tags_list = list(dict.fromkeys(tags_list))
            tags_str = ", ".join(tags_list)
            if not tags_str:
                tags_str = 'health tips, bangladesh'

            # Extract exact slug from the shajgoj URL (e.g. https://www.shajgoj.com/kids-growth-pain/ -> kids-growth-pain)
            # Remove trailing slash and grab last part
            parsed_url = urlparse(post_href)
            path_parts = [p for p in parsed_url.path.split('/') if p]
            if path_parts:
                extracted_slug = path_parts[-1]
            else:
                extracted_slug = re.sub(r'[^a-zA-Z0-9-]', '', post_title.lower().replace(' ', '-'))[:50].strip('-') + '-' + str(uuid.uuid4().hex[:4])

            # Download Image
            local_img_path = None
            if img_url and img_url.startswith('http'):
                ext = img_url.split('.')[-1].split('?')[0]
                if ext.lower() not in ['jpg', 'jpeg', 'png', 'webp', 'gif']:
                    ext = 'jpg'
                filename = f"scraped_{uuid.uuid4().hex[:8]}.{ext}"
                try:
                    img_resp = requests.get(img_url, headers=headers, stream=True, timeout=10)
                    if img_resp.status_code == 200:
                        with open(os.path.join(storage_dir, filename), 'wb') as f:
                            for chunk in img_resp.iter_content(1024):
                                f.write(chunk)
                        local_img_path = f"blog/{filename}"
                except Exception as e:
                    print(f"    Failed to download image: {e}")

            # Insert into blog_posts
            now = datetime.now()
            try:
                cursor.execute(
                    """
                    INSERT INTO blog_posts 
                    (blog_category_id, title, slug, excerpt, featured_image, author_name, is_published, seo_title, seo_description, tags, created_at, updated_at) 
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """,
                    (cat_id, post_title, extracted_slug, fallback_excerpt, local_img_path, 'Shajgoj Desk', 1, post_title, seo_desc, tags_str, now, now)
                )
                post_id = cursor.lastrowid
                
                # Insert content into sections
                if content_html:
                    cursor.execute(
                        """
                        INSERT INTO blog_post_sections 
                        (blog_post_id, order_index, heading, content, created_at, updated_at) 
                        VALUES (%s, %s, %s, %s, %s, %s)
                        """,
                        (post_id, 0, None, content_html, now, now)
                    )

                conn.commit()
                print(f"    -> Saved successfully as ID {post_id} with Slug '{extracted_slug}'")
            except Exception as ex:
                print(f"    -> DB Error: {ex}")
                conn.rollback()

        # Increment page
        page += 1

    cursor.close()
    conn.close()
    print("Scraping completed!")

if __name__ == '__main__':
    main()
