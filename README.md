# 벨아벨팜 기업소개

PHP 단일 페이지입니다. 사용자 작업지시서에 지정된 기업소개 9개 섹션만 구현했습니다.

## 실행

PHP 8.2 이상에서 프로젝트 루트 기준으로 실행합니다.

```powershell
php -S 127.0.0.1:8086 -t public public/router.php
```

<http://127.0.0.1:8086/>

실제 호스팅에서도 웹 루트는 `public/`으로 지정합니다. `app/`, `config/`, `results/`, `tools/`는 웹에 노출하지 않습니다. Node 빌드, Composer, 데이터베이스는 필요하지 않습니다.

## 파일

- `public/index.php`: 기업소개 한 페이지, 9개 섹션.
- `app/chrome.php`, `public/assets/chrome.css`, `chrome.js`: 고정 헤더·전체 메뉴·푸터.
- `public/assets/sections.css`: 사용자 지정 섹션 타이틀과 Lorem 서브카피 배치.
- `public/assets/site.css`: 화면 스타일과 반응형 배치.
- `public/assets/history.js`: 확정 연혁 탭과 설정된 사진 자동 슬라이드.
- `public/assets/motion.js`, `motion.css`: 인트로 확대, 이미지 마스크 확장, 가로 핀스크롤, 브랜드 선택, 썸네일 전환 및 등장 효과.
- `public/assets/vendor/`: 로컬 GSAP·ScrollTrigger, Lenis, Swiper, AOS와 라이선스.
- `app/content.php`: 화면 노출 문구 및 비공개 연혁 후보 데이터.
- `config/materials.php`: 확인된 소재와 빈 값 관리.
- `app/history.php`: 확정 상태를 확인하는 연혁 렌더러.
- `app/bootstrap.php`: 공통 이스케이프, 로컬 이미지 검사, 응답 헤더.
- `results/작업결과.md`: 구현 항목, 비워둔 항목, 검수 결과.

자료가 없는 항목은 `null` 또는 빈 배열입니다. 이미지 누락은 회색 영역으로 표시합니다. 추가 요청에 따라 타이틀 9개와 Lorem 서브카피 9곳을 표시하며, 원래 지정된 문구 18개는 그대로 유지합니다. Lorem은 `app/content.php`의 `lorem`, 섹션명은 `titles`에서 관리합니다. 미확정 원문·연혁·실적은 빈 값으로 유지합니다.

연혁은 `history_approved` 안에서 `status`가 `approved`인 항목만 렌더링합니다. 날짜·본문 형식은 `['date'=>'YYYY.MM.DD', 'text'=>'확정된 원문', 'status'=>'approved']`입니다. 현재 채택 자료가 없으므로 기본 화면에 연도 탭과 후보 연혁을 표시하지 않습니다. 후보 11건은 `app/content.php`의 비공개 데이터로만 보관합니다.

사진 자동 전환은 연도별 확인된 로컬 사진이 2장 이상인 경우에 동작합니다. 현재 전환 간격 설정은 5초입니다. 공개 채택된 연혁과 사진이 없으므로 연혁 영역은 회색으로 표시합니다. 후보 데이터는 공개하지 않습니다.

사진 슬라이더는 Swiper의 페이드 전환·드래그·자동재생을 사용합니다. Lenis의 부드러운 휠 스크롤과 GSAP ScrollTrigger의 핀 구간을 연결했습니다. 등장 효과에는 GSAP와 AOS를 사용합니다. 동작 줄이기 환경에서는 핀과 자동재생을 해제합니다.

문의 필드·버튼·개인정보 문구·결과 문구·전송 API·수신 설정은 명세가 없어 구현하지 않았습니다. 화면 연락처는 표시용 문자열이며 메일 수신 설정으로 사용하지 않습니다.

## 검수

PHP 서버 실행 후:

```powershell
python -X utf8 tools/check.py
Get-ChildItem -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
node --check public/assets/history.js
node --check public/assets/motion.js
node --check public/assets/chrome.js
```

Python 점검은 표준 라이브러리만 사용합니다. 결과는 `results/checks.json`에 기록됩니다.

## 사용 소재

사용자 제공 로고 2종, 제주 해녀·조릿대·봉봉이·원물 사진을 사용했습니다. 인트로는 해녀와 수산 원물 사진, 조릿대 사진 순으로 구성했습니다. 사진은 방향·해상도·WebP 인코딩만 조정했습니다. 원본 폴더는 수정하지 않았습니다. 특허·협약·사회적 가치 증빙·연혁·아우아우·해외 박람회 이미지는 회색으로 처리했습니다. 아이콘은 Lucide의 Mail·Equal·X이며, 서체는 Pretendard Variable입니다. 라이브러리·서체·아이콘의 라이선스는 각 자산 폴더에 포함했습니다.
