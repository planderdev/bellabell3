from pathlib import Path
from html.parser import HTMLParser
from urllib.request import urlopen
from urllib.error import HTTPError
from collections import Counter
import json

ROOT = Path(__file__).resolve().parents[1]
BASE = 'http://127.0.0.1:8086'
EXPECTED = {
    'intro': '프롬한라를 만드는 회사, 벨아벨팜입니다 / 한라산에서 찾은 천연물을 연구하는 반려동물 바이오기업',
    'meaning': "'벨아벨'은 제주어로 '다양하다'는 뜻입니다. 제주의 다양한 원료로 반려동물의 건강한 일상을 만듭니다",
    'mission': '반려동물과 사람이 함께 행복하게 살아가는 세상',
    'research': '한라산 조릿대를 반려동물 원료로 연구합니다',
    'social': '우리가 만드는 방식이 제주와 동물에게 돌아갑니다',
    'value-0': '인증 사회적기업·SVI 우수등급',
    'value-1': '지역 청년·취약계층 고용',
    'value-2': '플라스틱 제로 포장',
    'value-3': '지역 원물 제값 구매',
    'value-4': '유기동물 사회공헌',
    'family': '벨아벨팜이 만드는 브랜드',
    'brand-0-name': '프롬한라',
    'brand-0-description': '펫 웰니스',
    'brand-1-name': '아우아우',
    'brand-1-description': '제주 로컬 공간사업',
    'global': '제주에서 세계로',
    'partner': '함께할 파트너를 찾습니다',
    'contact': 'partner@jejubellabell.com · 064-803-0060',
}
TITLES = dict(zip(['intro','meaning','mission','research','social','history','family','global','partner'], ['인트로','이름의 뜻','미션','연구·기술','사회적 가치','연혁','브랜드 패밀리','글로벌','제안 & 협업']))
LOREM = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'

class Page(HTMLParser):
    def __init__(self, source):
        super().__init__()
        self.body = False
        self.tags = Counter()
        self.sections = []
        self.images = []
        self.urls = []
        self.copies = {}
        self.titles = {}
        self.lorem = {}
        self.ids = Counter()
        self.link_targets = []
        self.current = None
        self.text = []
        self.gray = 0
        self.brand_controls = 0
        self.feed(source)
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'body': self.body = True
        if self.body: self.tags[tag] += 1
        if a.get('id'): self.ids[a['id']] += 1
        if tag == 'a': self.link_targets.append(a.get('href',''))
        if tag == 'section': self.sections.append(a.get('data-section'))
        if tag == 'img': self.images.append(a)
        if a.get('data-media-state') == 'missing': self.gray += 1
        if tag == 'button' and 'data-brand-index' in a: self.brand_controls += 1
        if tag in ['img','link','script']:
            url = a.get('src') or a.get('href')
            if url: self.urls.append(url)
        if 'data-copy' in a:
            self.current = (self.copies, a['data-copy'])
            self.copies[a['data-copy']] = ''
        if 'data-section-title' in a:
            self.current = (self.titles, a['data-section-title'])
            self.titles[a['data-section-title']] = ''
        if 'data-lorem' in a:
            self.current = (self.lorem, a['data-lorem'])
            self.lorem[a['data-lorem']] = ''
    def handle_data(self, text):
        if self.body and text.strip(): self.text.append(text.strip())
        if self.current: self.current[0][self.current[1]] += text
    def handle_endtag(self, tag):
        if tag in ['h1','h2','h3','p']: self.current = None
        if tag == 'body': self.body = False

def fetch(path):
    try:
        with urlopen(BASE+path, timeout=10) as response:
            return response.status, response.read(), dict(response.headers)
    except HTTPError as error:
        return error.code, error.read(), dict(error.headers)

checks = []
def check(name, passed, detail=''):
    checks.append({'check':name,'pass':bool(passed),'detail':detail})

status, raw, headers = fetch('/')
source = raw.decode('utf-8')
page = Page(source)
check('PHP page returns 200', status == 200)
check('Exactly 9 sections in the specified order', page.sections == ['intro','meaning','mission','research','social','history','family','global','partner'])
check('Exactly the specified 18 copy fields', set(page.copies) == set(EXPECTED))
for key, value in EXPECTED.items():
    check('Verbatim text including spacing, punctuation and case', page.copies.get(key) == value, key)
allowed_text = set(EXPECTED.values()) | set(TITLES.values()) | {LOREM, LOREM.split('. ')[0]+'.', 'partner@jejubellabell.com', '064-803-0060'}
check('Only approved text, section labels, and requested Lorem', all(t in allowed_text for t in page.text))
check('Exactly the nine requested section titles', page.titles == TITLES)
check('One Lorem subcopy area in each section', set(page.lorem) == set(TITLES) and all(t == LOREM for t in page.lorem.values()))
check('Exactly one page H1', page.tags['h1'] == 1)
check('Header, footer, and full menu are present', page.tags['header'] == page.tags['footer'] == page.tags['dialog'] == 1)
check('All navigation targets resolve to actual sections', all(href.startswith('#') and href[1:] in TITLES and page.ids[href[1:]] == 1 for href in page.link_targets))
check('All IDs are unique', all(count == 1 for count in page.ids.values()))
check('Intro slash retained', ' / ' in page.copies.get('intro',''))
for tag in ['form','input','textarea','video','iframe']:
    check('No unspecified UI element', page.tags[tag] == 0, tag)
allowed_images = {'/assets/fromhalla.svg','/assets/bellabelfarm.svg','/assets/bongbong.webp'} | {'/assets/media/'+name+'.webp' for name in ['hero-haenyeo','hero-bamboo','meaning-bamboo','mission-bongbong','mission-beach','brand-ingredients']}
check('Only confirmed local photos and logos', all(i.get('src') in allowed_images for i in page.images))
check('Two brand selectors and two menu controls only', page.brand_controls == 2 and page.tags['button'] == 4)
check('Six missing image areas use gray surfaces', page.gray == 6)
check('No empty image source', all(i.get('src','').strip() for i in page.images))
check('Images have alt attributes', all('alt' in i for i in page.images))
check('Candidate dates and export statements are not public', all(x not in source for x in ['2025.12.17','2025.11.05','2025.10.28','2025.06.16','2024.11.13','30만 달러','100만 달러']))
check('No public editorial or placeholder text', all(x not in source for x in ['(신규)','자료 필요','준비 중','이미지 준비','후보','검토','심마니']))
check('No skeleton or image placeholder classes', all(x not in source.lower() for x in ['skeleton','placeholder','data-placeholder']))
check('No inline event handlers or scripts', 'onclick=' not in source and '<script>' not in source)
css = (ROOT/'public/assets/site.css').read_text(encoding='utf-8')
check('No automatic copy transformation', 'text-transform' not in css)
check('Motion uses the requested local libraries', all('/assets/vendor/'+name in source for name in ['gsap.min.js','ScrollTrigger.min.js','lenis.min.js','swiper-bundle.min.js','aos.js']))
check('CSP present', 'Content-Security-Policy' in headers)
for url in dict.fromkeys(page.urls):
    check('All page assets local', url.startswith('/assets/'), url)
    asset_status, _, _ = fetch(url)
    check('Asset loads successfully', asset_status == 200, url)
for path in ['/app/content.php','/config/materials.php','/company.php','/ingredients.php','/contact.php','/missing-page']:
    check('Unspecified routes and private data not exposed', fetch(path)[0] == 404, path)

report = {'date':'2026-10-28','passed':sum(c['pass'] for c in checks),'total':len(checks),'checks':checks}
(ROOT/'results').mkdir(exist_ok=True)
(ROOT/'results/checks.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({k:v for k,v in report.items() if k != 'checks'},ensure_ascii=False))
for c in checks:
    if not c['pass']: print('FAIL',c['check'],c['detail'])
raise SystemExit(0 if all(c['pass'] for c in checks) else 1)
