from pathlib import Path
from html.parser import HTMLParser
from urllib.request import urlopen
from urllib.error import HTTPError
from concurrent.futures import ThreadPoolExecutor
import json,re
ROOT=Path.cwd();BASE='http://127.0.0.1:8086'
class Node:
    def __init__(self,tag='',attrs=(),parent=None):self.tag=tag;self.a=dict(attrs);self.parent=parent;self.children=[];self.parts=[]
    def text(self):return ''.join(x.text() if isinstance(x,Node) else x for x in self.parts)
    def has(self,c):return c in self.a.get('class','').split()
    def descendants(self):
        for child in self.children:
            yield child
            yield from child.descendants()
class Tree(HTMLParser):
    def __init__(self,html):super().__init__();self.root=Node();self.stack=[self.root];self.feed(html)
    def add(self,tag,attrs,push):
        n=Node(tag,attrs,self.stack[-1]);self.stack[-1].children.append(n);self.stack[-1].parts.append(n)
        if push:self.stack.append(n)
    def handle_starttag(self,t,a):self.add(t,a,t not in ('area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'))
    def handle_startendtag(self,t,a):self.add(t,a,False)
    def handle_endtag(self,t):
        for i in range(len(self.stack)-1,0,-1):
            if self.stack[i].tag==t:self.stack=self.stack[:i];break
    def handle_data(self,t):self.stack[-1].parts.append(t)
def fetch(path):
    try:
        with urlopen(BASE+path,timeout=10) as r:return r.status,r.read(),dict(r.headers)
    except HTTPError as e:return e.code,e.read(),dict(e.headers)
checks=[]
def check(name,ok,detail=''):checks.append({'check':name,'pass':bool(ok),'detail':detail})
status,raw,headers=fetch('/');html=raw.decode();nodes=list(Tree(html).root.descendants())
byclass=lambda c:[n for n in nodes if n.has(c)]
check('PHP returns 200',status==200)
sections=[n for n in nodes if n.tag=='section' and 'hidden' not in n.a]
expected=['main-visual','about-reveal-section','bongbong-story-section','research-section','care-section','history-section','products-section','solution-section','contact-section']
check('History follows social and global immediately precedes contact',len(sections)==len(expected) and all(s.has(c) for s,c in zip(sections,expected)) and sections[-2].a.get('id')=='global')
for c,count in [('technology-item',0),('standard-card',4),('product-item',2),('products-photo-slide',4),('products-shape',0),('products-spec',0),('form-field',0),('contact-item',0)]:check('Component structure count',len(byclass(c))==count,c)
for section,c,count in [('care-section','care-item',5)]:check('Card structure count',sum(n.has(c) for n in byclass(section)[0].descendants())==count,section)
gnb=byclass('gnb')[0];depths=[n for n in gnb.descendants() if n.has('gnb-depth')]
check('Five navigation groups match the remaining sections',[len([a for a in d.children if a.tag=='a']) for d in depths]==[2,4,2,3,1])
check('Deleted global selectors removed',not byclass('form-select-menu'))
expected_copy={
'intro-title':'프롬한라를 만드는 회사, 벨아벨팜입니다',
'intro-body':'한라산에서 찾은 천연물을 연구하는 반려동물 바이오기업',
'about-title':"'벨아벨'은\n제주어로\n'다양하다'는 뜻입니다.",
'about-body':'제주의 다양한 원료로 반려동물의 건강한 일상을 만듭니다',
'bongbong-title':'반려동물과 사람이 함께\n행복하게 살아가는 세상',
'bongbong-body':'프롬한라는 시한부 고양이 ‘봉봉이’를 구조하며 탄생하게 되었습니다.\n\n구조 당시 피를 흘리고 있던 봉봉이는 주인에게 버려진 후 차에 치여 횡경막이 터지고 신부전증, 피부염을 앓고 있어 병원에서 수술도 회복도 불가능한 시한부 선고를 받았지만\n\n현재는 제주의 건강한 먹거리를 통해 수술 없이 건강을 되찾았습니다.',
'global-title':'제주에서 세계로',
'research':'한라산 조릿대를 반려동물 원료로 연구합니다',
'social':'우리가 만드는 방식이 제주와 동물에게 돌아갑니다','family':'벨아벨팜이 만드는 브랜드','brand-label':'프롬한라','brand-subtitle':'펫 웰니스','partner':'함께할 파트너를 찾습니다',
'contact':'partner@jejubellabell.com · 064-803-0060','value-0':'인증 사회적기업·SVI 우수등급','value-1':'지역 청년·취약계층 고용','value-2':'플라스틱 제로 포장','value-3':'지역 원물 제값 구매','value-4':'유기동물 사회공헌',
}
copies={n.a['data-copy']:n.text() for n in nodes if 'data-copy' in n.a}
for key,text in expected_copy.items():check('Confirmed copy is verbatim',copies.get(key)==text,key)
check('Two brand selectors use confirmed names',[n.a.get('data-name') for n in byclass('product-item-product')]==['프롬한라','아우아우'])
check('Brand selectors retain confirmed descriptions',[n.a.get('data-description') for n in byclass('product-item-product')]==['펫 웰니스','제주 로컬 공간사업'])
check('No source business facts',all(x not in html for x in ['KOREA NAKAJO','ICEVAN','5,500','137-81-33044']))
check('History has three year tabs',[n.text() for n in byclass('history-tab')]==['2025','2024','2022'])
check('History groups contain eight, two and one events',[len(n.children) for n in byclass('history-events')]==[8,2,1])
history_copy=[
('2025-12-17','SVI 우수등급'),('2025-11-05','농심과 제주 펫 산업 협력'),
('2025-10-28','제주대학교와 반려동물용 기능성 천연물 원료·제품 개발 업무협약'),
('2025-06-16','대만 30만 달러 수출 계약'),('2025-05-08','싱가포르 박람회 100만 달러 수출 상담'),
('2025-04-09','펫 샴푸바 싱가포르 수출'),('2025-02-12','개랑과자 싱가포르 수출'),
('2025-01-16','농촌융복합산업 인증'),('2024-11-13','애월아빠들 MOU'),
('2024-10-17','대만 Meow Expo 참가'),('2022','제주형 예비사회적기업 선정')]
history_rows=[row for events in byclass('history-events') for row in events.children]
check('History retains all eleven supplied dates and descriptions',[(row.children[0].a.get('datetime'),row.children[1].text()) for row in history_rows]==history_copy)
check('Unconfirmed history photos stay gray',len(byclass('history-photo'))==11 and all(n.a.get('data-media-state')=='missing' for n in byclass('history-photo')))
check('No source media paths',all(x not in html for x in ['/assets/images/','/assets/video/','/data/product_en/','/data/mainvisual']))
ids=[n.a['id'] for n in nodes if 'id' in n.a]
check('Unique IDs',len(ids)==len(set(ids)))
footer_sns=[
    ('https://www.instagram.com/from_halla/','/assets/icons/insta.svg'),
    ('https://blog.naver.com/jejubellabell','/assets/icons/blog.svg'),
    ('https://pf.kakao.com/_JTnxks','/assets/icons/kakaoch.svg'),
]
confirmed_destinations={
    'tel:0648030060','mailto:partner@jejubellabell.com',
    'https://m.fromhalla.com/article/%EB%B3%B4%EB%8F%84%EC%9E%90%EB%A3%8C/2/13641/#',
    *(url for url,icon in footer_sns),
}
for a in [n for n in nodes if n.tag=='a']:
    href=a.a.get('href','')
    check('Link uses a real section or confirmed destination',href in confirmed_destinations or (href.startswith('#') and href[1:] in ids),href)
assets=[]
for n in nodes:
    if n.tag in ('script','img','link'):url=n.a.get('src') or n.a.get('href');assets.append(url)
    if n.tag=='video':
        check('Video has no downloaded or external source','src' not in n.a and not any(c.tag=='source' for c in n.children))
        if n.a.get('poster'):assets.append(n.a['poster'])
lucide_url='https://unpkg.com/lucide@1.63.0/dist/umd/lucide.min.js'
for url in sorted(set(filter(None,assets))):check('Asset is local or the pinned Lucide library',url.startswith('/assets/') or url==lucide_url,url)
lucide_scripts=[n for n in nodes if n.tag=='script' and n.a.get('src')==lucide_url]
check('Lucide library uses a pinned version and integrity check',len(lucide_scripts)==1 and lucide_scripts[0].a.get('integrity')=='sha384-kqSVAzIdvqiedZsb49zLXfAO7CRNm8lsCSTdNjJoKfkO3jyWSO75XMdRIvOzn3H0' and lucide_scripts[0].a.get('crossorigin')=='anonymous')
check('Local icon images use the supplied SNS assets',all(n.a.get('src') in {icon for url,icon in footer_sns} for n in nodes if n.tag=='img' and n.a.get('src','').startswith('/assets/icons/')))
check('Hamburger retains its two original bars',len(byclass('menu-button')[0].children)==2 and all(n.tag=='span' for n in byclass('menu-button')[0].children))
local_assets=sorted({url for url in assets if url and url.startswith('/assets/')})
with ThreadPoolExecutor(max_workers=8) as pool:
    for url,result in zip(local_assets,pool.map(fetch,local_assets)):check('Local asset responds 200',result[0]==200,url)
certs=byclass('footer-certifications')[0]
check('Footer SNS links match the supplied local icons',[(n.a.get('href'),n.children[0].a.get('src') if n.children else None) for n in certs.children]==footer_sns)
check('Footer SNS links have accessible names and safe new tabs',all(n.a.get('aria-label') and n.a.get('target')=='_blank' and {'noopener','noreferrer'}<=set(n.a.get('rel','').split()) for n in certs.children))
check('Unused gray footer SVGs removed',not list((ROOT/'public/assets').glob('gray-*.svg')))
for name in ('site.css','motion.css','sections.css','chrome.css','motion.js','chrome.js','history.js'):check('Previous design file removed',not (ROOT/'public/assets'/name).exists(),name)
for name in ('base.css','layout.css','fonts.css','main.css'):
    css=(ROOT/'public/assets/css'/name).read_text(encoding='utf-8')
    check('CSS has no remote or source image URL',all(u.startswith(('#','/assets/')) for u in re.findall(r'url\([\"\']?([^\)\"\']+)',css)),name)
    for url in set(re.findall(r'url\([\"\']?([^\)\"\']+)',css)):
        if url.startswith('/assets/') and url not in assets: check('CSS asset responds 200',fetch(url)[0]==200,url)
js='\n'.join((ROOT/'public/assets/js'/name).read_text(encoding='utf-8') for name in ('interaction.js','brand-gallery.js'))
for distance in ('600','180'):check('Current pin distance retained',bool(re.search(r'end\s*:\s*[\"\']\+='+distance+r'%[\"\']',js)),distance)
check('No inline scripts or event handlers','<script>' not in html and not re.search(r'\son[a-z]+=',html))
check('Business contact form is a UI preview with submission disabled',len(byclass('business-contact-form'))==1 and len([n for n in nodes if n.tag=='button' and n.a.get('type')=='submit' and 'disabled' in n.a])==1 and "form-action 'none'" in headers.get('Content-Security-Policy',''))
for path in ['/includes/common.php','/includes/head.php','/includes/header.php','/includes/footer.php','/includes/content.php','/includes/company-history.php','/includes/research.php','/includes/contact-form.php','/config/materials.php','/company.php','/en/product/find.php']:
    check('Private and undefined routes return 404',fetch(path)[0]==404,path)
report={'date':'2026-10-29','passed':sum(c['pass'] for c in checks),'total':len(checks),'checks':checks}
(ROOT/'results/checks.json').write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({k:v for k,v in report.items() if k!='checks'},ensure_ascii=False))
for c in checks:
    if not c['pass']:print('FAIL',c['check'],c['detail'])
raise SystemExit(0 if all(c['pass'] for c in checks) else 1)
