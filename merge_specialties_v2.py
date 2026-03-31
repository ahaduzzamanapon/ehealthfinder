import pymysql
import re

DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

MERGE_RULES = {
    "Pulmonologist / Chest Specialist": ["pulmonolog", "chest", "asthma", "respiratory"],
    "Gynecologist": ["gynecolog", "gynaecolog", "gynae", "obstetric", "pregnancy"],
    "Radiologist": ["radiolog", "sonolog"],
    "Physical Medicine & Physiotherapist": ["physiotherap", "occupational therapist", "physical medicine", "rehabilitation"],
    "Medicine Specialist": ["medicine", "general practitioner", "general physician", "internal medicine"],
    "Nutritionist & Dietitian": ["nutritionist", "dietitian", "dietician"],
    "Homeopathy & Unani": ["homeopath", "homoeo", "unani"],
    "Cardiovascular & Thoracic Surgeon": ["cardiothoracic", "thoracic surgeon", "cardiovascular surgeon", "heart surgeon", "vascular surgeon"],
    "Neurosurgeon": ["neurosurgeon", "neurosurgery", "neurospine"],
    "Neurologist": ["neurologist", "neuromedicine", "brain"], 
    "Dermatologist": ["dermatolog", "skin", "vd", "hair"],
    "Oncologist": ["oncolog", "cancer", "tumor"],
    "Dentist / Dental Surgeon": ["dentist", "dental", "odontolog", "prosthodontist"],
    "Hematologist": ["hematolog", "blood"],
    "Pediatrician": ["pediatric", "paediatric", "child", "neonatolog", "newborn"],
    "Cardiologist": ["cardiolog"],
    "Ophthalmologist": ["ophthalmolog", "eye", "retina", "cornea", "oculoplastic"],
    "Endocrinologist": ["endocrinolog", "diabetes", "diabetolog", "hormone"],
    "Rheumatologist": ["rheumatolog", "arthritis"],
    "Gastroenterologist": ["gastro", "liver", "hepatolog"],
    "Urologist": ["urolog", "urinary"],
    "Nephrologist": ["nephrolog", "kidney"],
    "ENT Specialist": ["ent specialist", "ear", "nose", "throat", "otorhinolaryngolog", "otolaryngolog"],
    "Psychiatrist": ["psychiatr", "mental"],
    "Psychologist": ["psycholog"],
    "Orthopedic Surgeon": ["orthopedic", "orthopaedic", "ortho"],
    "Colorectal Surgeon": ["colorectal", "proctolog"],
    "Anesthesiologist": ["anesthesio", "pain"],
    "Pathologist": ["patholog"],
    "Plastic Surgeon": ["plastic"],
    "Sexologist": ["sexolog", "sexual"],
    # Put general surgeons at the bottom so specific ones are caught first
    "General Surgeon": ["general surgeon", "surgeon", "surgery", "laparoscopic", "breast surgeon", "hernia surgeon", "pancreatic surgeon"], 
}

GARBAGE_KWS = ["clinic", "hospital", "registration", "fellow", "certificate", "training", "trained", "technologist"]
EXACT_GARBAGE = ["specialist", "expert", "consultant"]

def get_or_create_main_specialty(cursor, main_name):
    cursor.execute("SELECT id FROM specialties WHERE name = %s", (main_name,))
    result = cursor.fetchone()
    if result:
        return result['id']
    cursor.execute("INSERT INTO specialties (name) VALUES (%s)", (main_name,))
    return cursor.lastrowid

def merge_specialties():
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    cursor.execute("SELECT id, name FROM specialties")
    all_specialties = cursor.fetchall()
    
    merged_count = 0
    doctors_moved = 0
    deleted_specialties = 0

    for spec in all_specialties:
        spec_id = spec['id']
        spec_name = spec['name'].lower()
        original_name = spec['name'].strip()
        
        # 1. Check garbage
        if any(kw in spec_name for kw in GARBAGE_KWS) or spec_name in EXACT_GARBAGE:
            print(f"Deleting Garbage: {original_name}")
            cursor.execute("UPDATE doctors SET specialty_id = NULL WHERE specialty_id = %s", (spec_id,))
            cursor.execute("DELETE FROM specialties WHERE id = %s", (spec_id,))
            deleted_specialties += 1
            continue
        
        # 2. Check merge rules
        matched_main = None
        for main_name, keywords in MERGE_RULES.items():
            if any(kw in spec_name for kw in keywords):
                matched_main = main_name
                break
                
        if matched_main:
            if original_name == matched_main:
                continue
                
            main_id = get_or_create_main_specialty(cursor, matched_main)
            if main_id != spec_id:
                # print(f"Merging '{original_name}' -> '{matched_main}'")
                cursor.execute("UPDATE doctors SET specialty_id = %s WHERE specialty_id = %s", (main_id, spec_id))
                doctors_moved += cursor.rowcount
                cursor.execute("DELETE FROM specialties WHERE id = %s", (spec_id,))
                deleted_specialties += 1
                merged_count += 1
                
    conn.commit()
    conn.close()
    print("-" * 30)
    print(f"Merge Complete! Processed {merged_count} specialty variations.")
    print(f"Moved {doctors_moved} doctors to standardized specialties.")
    print(f"Deleted {deleted_specialties} redundant/garbage specialty rows.")

if __name__ == '__main__':
    merge_specialties()
