import os
import time
import requests
import pymysql
from bs4 import BeautifulSoup
import re
import urllib3

urllib3.disable_warnings()
from merge_specialties_v2 import MERGE_RULES, GARBAGE_KWS, EXACT_GARBAGE

DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

def get_b_label(col, label_text):
    for b in col.find_all(["b", "strong"]):
        if label_text in b.get_text():
            nxt = b.next_sibling
            
            # Navigate next siblings until we find text
            while nxt:
                # If it's a string, return it stripped
                if isinstance(nxt, str):
                    s = nxt.strip()
                    if s and s != ":": 
                        return s.lstrip(':').strip()
                # If it's a span or p, get text
                elif getattr(nxt, 'name', None) in ['span']:
                    return nxt.get_text(strip=True)
                elif getattr(nxt, 'name', None) == 'br':
                    # sometimes the text is immediately after <br/>
                    pass 
                
                nxt = nxt.next_sibling
    return ""

import urllib.parse

def download_image(img_url, doc_id):
    if not img_url: return None
    
    # Safely escape arbitrary special characters, keeping slashes intact
    safe_img_url = urllib.parse.quote(img_url.strip(), safe='/:?=&')
    
    if not safe_img_url.startswith('http'):
        safe_img_url = "https://www.ibnsinatrust.com/" + safe_img_url.lstrip('/')
        
    try:
        os.makedirs("doctorbd_images", exist_ok=True)
        img_ext = safe_img_url.split('.')[-1]
        if '?' in img_ext: img_ext = img_ext.split('?')[0]
        if len(img_ext) > 5 or not img_ext: img_ext = "jpg"
            
        filename = f"ibnsina_{doc_id}_{int(time.time())}.{img_ext}"
        filepath = os.path.join("doctorbd_images", filename)
        
        headers = {"User-Agent": "Mozilla/5.0"}
        r = requests.get(safe_img_url, stream=True, headers=headers, timeout=12, verify=False)
        
        if r.status_code == 200:
            with open(filepath, 'wb') as f:
                for chunk in r.iter_content(1024):
                    f.write(chunk)
            return f"doctorbd_images/{filename}"
    except Exception as e:
        print(f"  [Image Fetch] Error {safe_img_url}: {e}")
        
    return safe_img_url

def get_or_create_location(cursor, name):
    cursor.execute("SELECT id FROM locations WHERE name = %s", (name,))
    res = cursor.fetchone()
    if res: return res['id']
    cursor.execute("INSERT INTO locations (name) VALUES (%s)", (name,))
    return cursor.lastrowid

def get_or_create_specialty(cursor, spec_name):
    if not spec_name: return None
    lower_s = spec_name.lower().strip()
    if lower_s in EXACT_GARBAGE or any(kw in lower_s for kw in GARBAGE_KWS): return None
        
    matched = spec_name.strip()
    for main_name, keywords in MERGE_RULES.items():
        if any(kw in lower_s for kw in keywords):
            matched = main_name
            break
            
    cursor.execute("SELECT id FROM specialties WHERE name = %s", (matched,))
    res = cursor.fetchone()
    if res: return res['id']
    cursor.execute("INSERT INTO specialties (name) VALUES (%s)", (matched,))
    return cursor.lastrowid

def get_or_create_hospital(cursor, name, loc_id):
    cursor.execute("SELECT id FROM hospitals WHERE name = %s", (name,))
    res = cursor.fetchone()
    if res: return res['id']
    cursor.execute("INSERT INTO hospitals (name, location_id) VALUES (%s, %s)", (name, loc_id))
    return cursor.lastrowid

def get_ibnsina_branches():
    url = "https://www.ibnsinatrust.com/find_doctor_branchwise.php"
    r = requests.get(url, headers={"User-Agent": "Mozilla/5.0"}, verify=False)
    soup = BeautifulSoup(r.text, "html.parser")
    branches = {}
    select = soup.find("select", id="brand")
    if select:
        for opt in select.find_all("option"):
            val = opt.get("value")
            text = opt.get_text(strip=True)
            if val and val != "55" and val != "":
                branches[val] = text
    return branches

def scrape_ibnsina():
    print("Fetching Ibn Sina Branches...")
    branches = get_ibnsina_branches()
    print(f"Found {len(branches)} branches.")
    
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    cursor.execute("DELETE FROM doctors WHERE url LIKE 'https://www.ibnsinatrust.com%'")
    conn.commit()
    print("Cleaned up duplicate/corrupt Ibn Sina references.")
    
    headers = {"User-Agent": "Mozilla/5.0"}
    
    for bid, branch_name in branches.items():
        print(f"\\n--- Processing Branch: {branch_name} (ID: {bid}) ---")
        
        url = "https://www.ibnsinatrust.com/view_find_doctor_branchwise.php"
        try:
            r = requests.post(url, data={"bid": bid}, headers=headers, verify=False, timeout=20)
        except Exception as e:
            continue
            
        soup = BeautifulSoup(r.text, "html.parser")
        
        docs_saved = 0
        img_cols = soup.find_all("div", class_="col-md-3")
        
        for col3 in img_cols:
            img_tag = col3.find("img")
            if not img_tag: continue
                
            col5 = col3.find_next_sibling("div", class_="col-md-5")
            col4 = col3.find_next_sibling("div", class_="col-md-4")
            if not col5 or not col4: continue
                
            name_p = col5.find("p", style=re.compile(r"color:#00E"))
            if not name_p: continue
            doc_name = name_p.get_text(strip=True)
            if not doc_name: continue
            
            # Robust labeling using get_b_label
            degrees = get_b_label(col5, "Qualifications")
            if not degrees: degrees = get_b_label(col5, "Qualification")
            
            specialty_str = get_b_label(col5, "Department Name")
            if not specialty_str: specialty_str = get_b_label(col5, "Specialty")
            
            designation = get_b_label(col5, "Designation")
            institute = get_b_label(col5, "Institute")
            
            experience = ""
            if designation and institute:
                experience = f"{designation} - {institute}"
            elif designation:
                experience = designation
            elif institute:
                experience = institute
                
            phone = get_b_label(col4, "Appointment")
            if not phone: phone = "10615"
                
            v_time = get_b_label(col4, "Chamber Time")
            off_day = get_b_label(col4, "Off Day")
            room = get_b_label(col4, "Room Number")
            address = get_b_label(col4, "Branch Name & Address")
            if not address: address = branch_name
            
            visiting_hour = v_time
            if off_day: visiting_hour += f" (Off: {off_day})"
            
            map_addr = address
            if room: map_addr += f", Room No: {room}"
            
            # Extract doc ID and specific URL
            doc_id = bid + "_" + re.sub(r'[^A-Za-z0-9]', '', doc_name)
            container_link = col4.find_next_sibling("div", class_="container")
            doc_url = "https://www.ibnsinatrust.com/"
            if container_link:
                a_tag = container_link.find("a", href=re.compile(r"view_doctor_profile_up\.php"))
                if a_tag:
                    doc_url = "https://www.ibnsinatrust.com/" + a_tag.get("href")
                    try: doc_id = doc_url.split("id=")[1]
                    except: pass
            
            # Database inserts
            loc_id = get_or_create_location(cursor, branch_name)
            hosp_name = branch_name
            hosp_id = get_or_create_hospital(cursor, hosp_name, loc_id)
            spec_id = get_or_create_specialty(cursor, specialty_str)
            
            cursor.execute("SELECT id FROM doctors WHERE name = %s AND location_id = %s", (doc_name, loc_id))
            if cursor.fetchone(): continue
                
            img_src = img_tag.get("src")
            local_img = download_image(img_src, doc_id) if img_src else None
            
            try:
                cursor.execute("""
                    INSERT INTO doctors (name, degrees, experience, image_path, location_id, specialty_id, url)
                    VALUES (%s, %s, %s, %s, %s, %s, %s)
                """, (doc_name, degrees, experience, local_img, loc_id, spec_id, doc_url))
                
                doc_insert_id = cursor.lastrowid
                cursor.execute("""
                    INSERT INTO chambers (doctor_id, hospital_id, address, visiting_hour, appointment_number)
                    VALUES (%s, %s, %s, %s, %s)
                """, (doc_insert_id, hosp_id, map_addr, visiting_hour, phone))
                
                conn.commit()
                docs_saved += 1
                print(f"    -> Saved doc: {doc_name} with local image: {local_img}")
            except Exception as e:
                print(f"  [DB Insert] {doc_name}: {e}")
                conn.rollback()

        print(f"  Saved {docs_saved} new doctors for branch {branch_name}")
        time.sleep(1)
        
    conn.close()
    print("Ibn Sina Full Scrape Complete!")

if __name__ == '__main__':
    scrape_ibnsina()
