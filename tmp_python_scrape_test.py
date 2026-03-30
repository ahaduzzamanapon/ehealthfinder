import requests
from bs4 import BeautifulSoup

url = "https://www.shajgoj.com/category/health/"
headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
}

response = requests.get(url, headers=headers)
soup = BeautifulSoup(response.text, 'html.parser')

articles = soup.select('article')
print(f"Found {len(articles)} articles.")

for i, article in enumerate(articles[:3]):
    title_elem = article.select_one('.entry-title a')
    if title_elem:
        print(f"Title: {title_elem.text.strip()}")
        print(f"Link: {title_elem['href']}")
        
    img_elem = article.select_one('img')
    if img_elem:
        print(f"Image: {img_elem.get('src')}")
    print("---")
