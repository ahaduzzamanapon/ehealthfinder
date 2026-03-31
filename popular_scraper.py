import os
import time
import requests
import pymysql
import re
from playwright.sync_api import sync_playwright

from merge_specialties_v2 import MERGE_RULES, GARBAGE_KWS, EXACT_GARBAGE

DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

def download_image(img_url, doc_id):
    if not img_url:
        return None
    try:
        os.makedirs("doctorbd_images", exist_ok=True)
        img_ext = img_url.split('.')[-1]
        if '?' in img_ext:
            img_ext = img_ext.split('?')[0]
        if not img_ext or len(img_ext) > 4:
            img_ext = "jpg"
            
        filename = f"popular_{doc_id}_{int(time.time())}.{img_ext}"
        filepath = os.path.join("doctorbd_images", filename)
        
        # Static image resources typically bypass Cloudflare bots, but we add basic headers just in case.
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
        r = requests.get(img_url, stream=True, headers=headers, timeout=10)
        
        if r.status_code == 200:
            with open(filepath, 'wb') as f:
                for chunk in r.iter_content(1024):
                    f.write(chunk)
            return f"doctorbd_images/{filename}"
    except Exception as e:
        print(f"  [Image Error] Failed downloading {img_url}: {e}")
        
    return img_url # fallback to URL

def get_or_create_location(cursor, name):
    cursor.execute("SELECT id FROM locations WHERE name = %s", (name,))
    result = cursor.fetchone()
    if result: return result['id']
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
    result = cursor.fetchone()
    if result: return result['id']
    cursor.execute("INSERT INTO specialties (name) VALUES (%s)", (matched,))
    return cursor.lastrowid

def get_or_create_hospital(cursor, name, loc_id):
    cursor.execute("SELECT id FROM hospitals WHERE name = %s", (name,))
    result = cursor.fetchone()
    if result: return result['id']
    cursor.execute("INSERT INTO hospitals (name, location_id) VALUES (%s, %s)", (name, loc_id))
    return cursor.lastrowid

def scrape_popular_doctors():
    print("Launching Stealth Browser to fetch from Playwright's DOM Context...")
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        )
        page = context.new_page()

        token = None
        def handle_request(request):
            nonlocal token
            if "api.populardiagnostic.com" in request.url and "token=" in request.url:
                try:
                    token_part = request.url.split('token=')[1]
                    token = token_part.split('&')[0]
                except Exception:
                    pass

        page.on("request", handle_request)
        page.goto("https://www.populardiagnostic.com/doctordetail/2095", wait_until="networkidle")
        page.wait_for_timeout(3000) 
        
        if not token:
            print("Failed to intercept security token!")
            browser.close()
            return

        print(f"Token acquired: {token}")

        branches_res = page.evaluate(f'''async () => {{
            try {{
                const res = await fetch("https://api.populardiagnostic.com/api/branches?token={token}");
                return await res.json();
            }} catch(e) {{ return {{}}; }}
        }}''')

        branches = branches_res.get('data', {}).get('data', [])
        print(f"Intercepted {len(branches)} branches.")

        for branch in branches:
            branch_id = branch['id']
            master_branch_name = branch['name'].strip()
            print(f"\\n--- Processing Branch: {master_branch_name} ---")
            
            page_idx = 1
            new_docs = 0
            
            while True:
                list_res = page.evaluate(f'''async () => {{
                    try {{
                        const res = await fetch("https://api.populardiagnostic.com/api/doctor-suggestions?token={token}&branches={branch_id}&page={page_idx}");
                        return await res.json();
                    }} catch(e) {{ return {{}}; }}
                }}''')
                
                json_response = list_res.get('data', {})
                items = json_response.get('data', [])
                if not items:
                    break
                    
                for api_doc in items:
                    doc_id = api_doc['id']
                    
                    detail_res = page.evaluate(f'''async () => {{
                        try {{
                            const res = await fetch("https://api.populardiagnostic.com/api/doctor/{doc_id}?token={token}");
                            return await res.json();
                        }} catch(e) {{ return {{}}; }}
                    }}''')
                    
                    detail_data = detail_res.get('data', {})
                    if not detail_data: continue
                        
                    d_name = detail_data.get('name', '').strip()
                    if not d_name: continue
                        
                    # Calculate Location & Specialty & Mapping
                    degrees = detail_data.get('degree', '')
                    experience = detail_data.get('experience_summery', '')
                    
                    image_url = detail_data.get('image', None)
                    local_img_path = download_image(image_url, doc_id) if image_url else None
                    
                    # Specialty extraction from array API layout
                    specialists_array = detail_data.get('specialists', [])
                    specialty_str = specialists_array[0].get('specialist_name', '') if specialists_array else ''
                    spec_id = get_or_create_specialty(cursor, specialty_str)
                    
                    # Extract branch & chamber data accurately from the JSON
                    doc_branches = detail_data.get('branches', [])
                    for b_info in doc_branches:
                        b_name = (b_info.get('name') or master_branch_name).strip()
                        map_address = (b_info.get('map') or '').strip()
                        phone = (b_info.get('phone') or '09666 787801').strip()
                        
                        loc_id = get_or_create_location(cursor, b_name)
                        hosp_name = f"Popular Diagnostic Centre, {b_name}"
                        hosp_id = get_or_create_hospital(cursor, hosp_name, loc_id)
                        
                        # Prevent duplicates based on name & location so we don't insert same doctor multiple times
                        cursor.execute("SELECT id FROM doctors WHERE name = %s AND location_id = %s", (d_name, loc_id))
                        if cursor.fetchone(): continue
                            
                        # Insert Doctor
                        try:
                            cursor.execute("""
                                INSERT INTO doctors (name, degrees, experience, image_path, location_id, specialty_id, url)
                                VALUES (%s, %s, %s, %s, %s, %s, %s)
                            """, (
                                d_name, degrees, experience, local_img_path, 
                                loc_id, spec_id, f"https://www.populardiagnostic.com/doctordetail/{doc_id}"
                            ))
                            doctor_inserted_id = cursor.lastrowid
                            
                            # Construct schedule visiting string safely
                            schedule_arr = detail_data.get('schedule', [])
                            schedule_str_parts = []
                            for day_obj in schedule_arr:
                                day = day_obj.get('day', '')
                                start = day_obj.get('start_time', '')
                                end = day_obj.get('end_time', '')
                                if day and start and end:
                                    schedule_str_parts.append(f"{day} {start} - {end}")
                            
                            visiting_hour = "; ".join(schedule_str_parts)
                            
                            cursor.execute("INSERT INTO chambers (doctor_id, hospital_id, address, visiting_hour, appointment_number) VALUES (%s, %s, %s, %s, %s)",
                                          (doctor_inserted_id, hosp_id, map_address, visiting_hour, phone))
                                          
                            conn.commit()
                            new_docs += 1
                        except Exception as e:
                            print(f"  [Error Insert] {d_name}: {e}")
                            conn.rollback()
                        
                if not json_response.get('next_page_url'):
                    break 
                    
                page_idx += 1
                time.sleep(1) # Soft delay for the JS fetch loops
            
            print(f"  Completed API loop for {master_branch_name} ({new_docs} saved docs).")

        browser.close()
        
    conn.close()
    print("Popular Diagnostic Scrape Complete!")

if __name__ == '__main__':
    scrape_popular_doctors()
