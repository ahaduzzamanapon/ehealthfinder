import pymysql

DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ehealthfinder',
    'charset': 'utf8mb4',
    'cursorclass': pymysql.cursors.DictCursor
}

def clean_duplicate_specialties():
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    # Get all exact same-named specialties
    cursor.execute("""
        SELECT name, GROUP_CONCAT(id ORDER BY id ASC) as ids
        FROM specialties
        GROUP BY name
        HAVING COUNT(id) > 1
    """)
    duplicates = cursor.fetchall()
    
    if not duplicates:
        print("No exact string duplicates found in specialties table!")
        conn.close()
        return

    total_doctors_updated = 0
    total_specialties_deleted = 0
    
    for row in duplicates:
        name = row['name']
        ids = [int(i) for i in row['ids'].split(',')]
        
        master_id = ids[0]
        duplicate_ids = ids[1:]
        
        print(f"Name: '{name}' | Keeping ID: {master_id} | Merging IDs: {duplicate_ids}")
        
        # 1. Update doctors from all duplicate IDs to the master ID
        format_strings = ','.join(['%s'] * len(duplicate_ids))
        update_query = f"UPDATE doctors SET specialty_id = %s WHERE specialty_id IN ({format_strings})"
        
        cursor.execute(update_query, [master_id] + duplicate_ids)
        updated = cursor.rowcount
        total_doctors_updated += updated
        
        # 2. Delete the duplicate specialty rows
        delete_query = f"DELETE FROM specialties WHERE id IN ({format_strings})"
        cursor.execute(delete_query, duplicate_ids)
        deleted = cursor.rowcount
        total_specialties_deleted += deleted
        
        conn.commit()

    print("\\n------------------------------")
    print("Cleanup Complete!")
    print(f"Mapped {total_doctors_updated} doctors to singular master IDs.")
    print(f"Deleted {total_specialties_deleted} duplicate specialty rows.")
    conn.close()

if __name__ == '__main__':
    clean_duplicate_specialties()
