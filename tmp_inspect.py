import requests
from bs4 import BeautifulSoup
import json

url = 'https://www.shajgoj.com/kids-growth-pain/'
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
}
resp = requests.get(url, headers=headers)
soup = BeautifulSoup(resp.text, 'html.parser')

# Find the main article or content area
tags = []
for el in soup.find_all(['div', 'article', 'section']):
    classes = el.get('class', [])
    if isinstance(classes, list):
        classes = " ".join(classes)
    if not classes:
        continue
    if len(el.find_all('p')) > 3: # Must contain some paragraphs to be content
        tags.append({
            'name': el.name,
            'classes': classes,
            'p_count': len(el.find_all('p'))
        })

with open('tmp_dom.json', 'w', encoding='utf-8') as f:
    json.dump(tags, f, indent=2)
