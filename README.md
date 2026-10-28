# 벨아벨팜 기업소개

PHP 단일 페이지입니다. 본문은 9개 섹션으로 구성하며, 전체 메뉴·퀵 메뉴·푸터를 포함합니다.

## 실행

PHP 8.2 이상에서 프로젝트 루트 기준으로 실행합니다.

```powershell
php -S 127.0.0.1:8086 -t public public/router.php
```

<http://127.0.0.1:8086/>

웹 루트는 `public/`입니다. Composer, Node 빌드, 데이터베이스는 필요하지 않습니다.

## 구성

- `public/index.php`: 본문 9개 섹션과 PHP 콘텐츠 연결.
- `includes/common.php`: 공통 함수·응답 헤더·로컬 이미지 검사.
- `includes/head.php`: `<head>` 메타 태그·글꼴·CSS·JavaScript 연결.
- `includes/header.php`: 상단 헤더·내비게이션·전체 메뉴.
- `includes/contact-form.php`: B2B 문의 폼 화면. 유형·회사·담당자·연락처·제안 내용을 입력하며, 실제 제출 버튼은 비활성 상태입니다.
- `includes/footer.php`: 하단 로고·인증 이미지 영역·회사 정보·연락처.
- `includes/content.php`: 확정 카피와 비공개 연혁 후보.
- `includes/company-history.php`: 회사 연혁 섹션.
- `includes/research.php`: 조릿대 연구 본문·특허 출원사실증명원·파트너 로고 마키. 기존 연구 슬라이더는 숨김 상태로 보관합니다.
- `public/assets/css/base.css`: 디자인 토큰·리셋·공통 스타일.
- `public/assets/css/layout.css`: 헤더·전체 메뉴·푸터와 반응형 스타일.
- `public/assets/css/main.css`: 메인페이지 전체 섹션·반응형·모션 접근성 스타일.
- `public/assets/js/interaction.js`: 인트로, 핀스크롤, 제품 선택, 메뉴·드롭다운 동작.
- `public/assets/js/contact-form.js`: 입력값 확인·오류 안내·문의 내용 글자 수 표시. 전송이나 저장은 하지 않습니다.
- `public/assets/js/company-history.js`: 연도별 연혁 슬라이더. 화면에 보일 때 자동 재생하고 호버·포커스·모션 축소 설정에서는 정지합니다.
- `public/assets/css/fonts.css`: 영문 Poppins·한글 Pretendard 로컬 폰트.
- `public/assets/css/vendor/`, `public/assets/js/vendor/`: GSAP·ScrollTrigger·ScrollToPlugin 3.12.5, Lenis 1.3.4, Swiper 11.2.10.
- UI 아이콘: 버전을 고정한 Lucide 1.63.0 CDN 라이브러리. 햄버거 버튼은 기존 형태를 유지합니다.
- `tools/check.py`: 콘텐츠·구조·로컬 자산·비공개 경로 점검. Python 표준 라이브러리만 사용합니다.
- `results/작업결과.md`: 구현·검수 결과.

제공 자료의 해녀·조릿대·봉봉이 사진만 연결했습니다. 확보되지 않은 이미지·영상의 콘텐츠 영역은 회색으로 표시합니다. 회색 SVG는 영역 비율을 유지하기 위한 단색 파일이며, 실제 인증증서나 제품 사진이 아닙니다.

새로 필요한 텍스트는 Lorem입니다. 기존 확정 카피는 유지하고, 미채택 연혁·실적·회사 정보는 만들지 않았습니다. 제품 패널의 표와 세 가지 선택 도구는 레이아웃 확인용이며, 실제 제품 사양·추천 엔진·메일 전송 기능을 제공하지 않습니다.

## 검수

```powershell
python -X utf8 tools/check.py
Get-ChildItem -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
node --check public/assets/js/interaction.js
```

브라우저 검수 결과는 `results/responsive.json`, `results/interactions.json`과 화면 캡처에 기록합니다.

B2B 문의 폼은 화면과 클라이언트 입력 검증만 구현했습니다. 실제 온라인 접수를 활성화하려면 발송 서버/API와 확정된 개인정보 수집·이용 안내를 연결해야 합니다.
