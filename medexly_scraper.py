import requests
from bs4 import BeautifulSoup
import xml.etree.ElementTree as ET
import pymysql
import re
import time
import os
import ssl

# Connect to the local database
# Adjust credentials if needed natively on the machine
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

def get_db_connection():
    return pymysql.connect(**db_config)

def fetch_sitemap_index():
    url = "https://medexly.com/sitemap_index.xml"
    print(f"Fetching sitemap index: {url}")
    response = requests.get(url, headers=HEADERS)
    root = ET.fromstring(response.content)
    
    # Extract only doctors sitemaps
    doctor_sitemaps = []
    ns = {'ns': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
    for sitemap in root.findall('ns:sitemap/ns:loc', ns):
        if 'doctors-sitemap' in sitemap.text:
            doctor_sitemaps.append(sitemap.text)
            
    print(f"Found {len(doctor_sitemaps)} doctor sitemaps.")
    return doctor_sitemaps

def fetch_doctor_urls(sitemap_url):
    print(f"Fetching doctor URLs from {sitemap_url}...")
    response = requests.get(sitemap_url, headers=HEADERS)
    root = ET.fromstring(response.content)
    
    doctor_urls = []
    ns = {'ns': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
    for url in root.findall('ns:url/ns:loc', ns):
        doctor_urls.append(url.text)
        
    return doctor_urls

def get_or_create_specialty(cursor, specialty_name):
    if not specialty_name:
        return None
        
    specialty_name = specialty_name.strip()
    cursor.execute("SELECT id FROM specialties WHERE name = %s", (specialty_name,))
    result = cursor.fetchone()
    if result:
        return result['id']
        
    cursor.execute("INSERT INTO specialties (name) VALUES (%s)", (specialty_name,))
    return cursor.lastrowid

def get_or_create_location(cursor, location_name):
    if not location_name:
        return None
        
    location_name = location_name.strip()
    cursor.execute("SELECT id FROM locations WHERE name LIKE %s", (f"%{location_name}%",))
    result = cursor.fetchone()
    if result:
        return result['id']
        
    cursor.execute("INSERT INTO locations (name) VALUES (%s)", (location_name,))
    return cursor.lastrowid

def get_or_create_hospital(cursor, hospital_name, location_id):
    if not hospital_name:
        return None
        
    hospital_name = hospital_name.strip()
    cursor.execute("SELECT id FROM hospitals WHERE name = %s", (hospital_name,))
    result = cursor.fetchone()
    if result:
        return result['id']
        
    cursor.execute("INSERT INTO hospitals (name, location_id) VALUES (%s, %s)", (hospital_name, location_id))
    return cursor.lastrowid


def scrape_and_insert_doctor(doc_url, connection):
    try:
        html = requests.get(doc_url, headers=HEADERS, timeout=10).text
        soup = BeautifulSoup(html, 'html.parser')
        
        # Name
        name_tag = soup.find('h1')
        if not name_tag:
            return False # Invalid page
            
        name = name_tag.text.strip()
        
        # Location logic from breadcrumb or URL
        # e.g., "Dr. John in Dhaka" -> "Dhaka"
        location_name = None
        bread_div = soup.find('div', class_='flex items-center text-sm text-gray-600')
        if bread_div:
            links = bread_div.find_all('a')
            if len(links) >= 4:
                location_name = links[3].text.strip()
        
        # Fallback to URL parsing if not found in breadcrumbs correctly
        # Usually slug ends with the district
        if not location_name:
            slug = doc_url.strip('/').split('/')[-1]
            parts = slug.split('-')
            if len(parts) > 1:
                location_name = parts[-1].capitalize()
        
        if not location_name:
            location_name = 'Unknown'

        # Specialty logic
        # Looks for the prominent span inside h1 container block
        specialty_name = None
        for span in soup.select('.text-primary-600.font-semibold'):
            text = span.text.strip()
            # Heuristic to find the specialty tag, often next to SVG icons
            if len(text) > 3 and "Read" not in text:
                specialty_name = text
                break
                
        # Degrees
        degrees = None
        p_desc = soup.find('p', class_='text-base text-gray-600 dark:text-gray-400 mb-2')
        if p_desc:
            degrees = p_desc.text.strip()
            
        # Workplace / Designation
        workplace = None
        designation = None
        # Medexly shows current job beautifully linked
        job_link = soup.select_one('a.font-medium.text-primary-600.hover\\:text-primary-700')
        if job_link:
            workplace = job_link.text.strip()
            # If workplace exists, we can assume the role is "Specialist" or leave empty if not specified
            designation = "Specialist"
            
        # Image Path
        image_path = None
        img_tag = soup.select_one('img.wp-post-image')
        if img_tag and 'src' in img_tag.attrs:
            image_path = img_tag['src']
            
        # About Text
        about_text = None
        about_div = soup.select_one('div.single-content-area p')
        if about_div:
            about_text = about_div.text.strip()
            
        # Validate Database for Same Name & Same Location
        with connection.cursor() as cursor:
            # First, fetch location ID (or create if missing) to do realistic validation
            loc_id = get_or_create_location(cursor, location_name)
            
            # Check for duplicate
            cursor.execute("SELECT id FROM doctors WHERE name = %s AND location_id = %s", (name, loc_id))
            if cursor.fetchone():
                print(f"Skipping duplicate: {name} in {location_name}")
                return True
                
            # If we reached here, the doctor is NEW!
            spec_id = get_or_create_specialty(cursor, specialty_name)
            
            # Insert logic matching DB schema
            # Schema columns from earlier exploration 
            cursor.execute("""
                INSERT INTO doctors 
                (name, degrees, designation, workplace, about_text, image_path, location_id, specialty_id) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """, (name, degrees, designation, workplace, about_text, image_path, loc_id, spec_id))
            
            doctor_id = cursor.lastrowid
            
            # Extract Chambers
            chambers_cards = soup.select('div.bg-white.dark\\:bg-gray-800.rounded-lg.p-5.border')
            for card in chambers_cards:
                # Hospital Name is usually in an h3 -> a
                h_tag = card.find('h3')
                if not h_tag: continue
                hospital_name = h_tag.text.strip()
                hosp_id = get_or_create_hospital(cursor, hospital_name, loc_id)
                
                chamber_details = card.select('div.space-y-3 p.text-base')
                address = None
                hours = None
                phone = None
                
                if len(chamber_details) >= 1: address = chamber_details[0].text.strip()
                if len(chamber_details) >= 2: hours = chamber_details[1].text.strip()
                
                phone_tag = card.select_one('a[href^="tel:"]')
                if phone_tag:
                    phone = phone_tag.text.strip()
                
                cursor.execute("""
                    INSERT INTO chambers 
                    (doctor_id, hospital_id, address, visiting_hour, appointment_number) 
                    VALUES (%s, %s, %s, %s, %s)
                """, (doctor_id, hosp_id, address, hours, phone))
                
            connection.commit()
            print(f"Inserted New Doctor: {name} in {location_name} with {len(chambers_cards)} chambers.")
            
        return True
            
    except Exception as e:
        print(f"Failed parsing {doc_url}: {str(e)}")
        return False


def main():
    conn = get_db_connection()
    try:
        doctor_sitemaps = fetch_sitemap_index()
        for smap in doctor_sitemaps:
            urls = fetch_doctor_urls(smap)
            print(f"Found {len(urls)} doctors in {smap}. Processing...")
            for doc_url in urls:
                scrape_and_insert_doctor(doc_url, conn)
                # Polite scraping delay
                time.sleep(1)
    except Exception as e:
        print(f"Critical Scraper Error: {str(e)}")
    finally:
        conn.close()
        print("Scraping operation finished.")

if __name__ == "__main__":
    main()
