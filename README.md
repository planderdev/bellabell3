# 벨아벨팜 기업소개

PHP 단일 페이지입니다. 본문은 9개 섹션으로 구성하며, 전체 메뉴·퀵 메뉴·푸터를 포함합니다.

## 실행

PHP 8.2 이상에서 프로젝트 루트 기준으로 실행합니다.

```powershell
php -S 127.0.0.1:8086 -t public public/router.php
```

<http://127.0.0.1:8086/>

웹 루트는 `public/`입니다. Composer와 Node 빌드는 필요하지 않습니다.

## 배포와 문의 접수

- Vercel 프로젝트 `bellabell3`(planderdevs-projects, icn1). `main` 푸시 = 운영 배포. `vercel.json`이 `/assets/*`는 정적 파일로, 나머지는 `api/index.php` → `public/router.php`로 보냅니다.
- `public/assets/media/photo/` 원본 사진은 저장소에만 두고 `.vercelignore`로 배포에서 뺍니다. 페이지에서 새 사진을 쓰면 `.vercelignore`에 예외(`!` 줄)를 추가하세요.
- Vercel 프로젝트에 `VERCEL_FORCE_NO_BUILD_CACHE=1`이 설정되어 있습니다. 빌드 캐시를 복원하면 vercel-php 빌더 설치가 건너뛰어져 함수가 `launcher.launcher` 오류(500)로 깨지므로 지우지 마세요.
- 문의 폼은 `POST /api/contact`로 PostgreSQL(Supabase) `business_inquiries` 테이블에 저장합니다. `db/schema.sql`을 먼저 적용합니다.
- 환경변수: `APP_SECRET`, `DATABASE_URL`, `ADMIN_PASSWORD_HASH` (`.env.example` 참고). 앞의 둘이 없으면 폼 제출 버튼이 비활성으로 남습니다.
- 접수 내역은 `/admin`에서 비밀번호로 로그인해 보고, 처리 완료를 표시합니다.

## 구성

- `public/index.php`: 본문 9개 섹션과 PHP 콘텐츠 연결.
- `includes/common.php`: 공통 함수·응답 헤더·로컬 이미지 검사.
- `includes/head.php`: `<head>` 메타 태그·글꼴·CSS·JavaScript 연결.
- `includes/header.php`: 상단 헤더·내비게이션·전체 메뉴.
- `includes/contact-form.php`: B2B 문의 폼 화면과 개인정보 수집·이용 안내.
- `includes/contact-handler.php`: 문의 검증·서명 토큰·전송 횟수 제한·DB 저장.
- `includes/admin.php`: `/admin` 문의 관리 화면.
- `includes/footer.php`: 하단 로고·인증 이미지 영역·회사 정보·연락처.
- `includes/content.php`: 확정 카피와 비공개 연혁 후보.
- `includes/company-history.php`: 회사 연혁 섹션.
- `includes/research.php`: 조릿대 연구 본문·특허 출원사실증명원·파트너 로고 마키. 기존 연구 슬라이더는 숨김 상태로 보관합니다.
- `public/assets/css/base.css`: 디자인 토큰·리셋·공통 스타일.
- `public/assets/css/layout.css`: 헤더·전체 메뉴·푸터와 반응형 스타일.
- `public/assets/css/main.css`: 메인페이지 전체 섹션·반응형·모션 접근성 스타일.
- `public/assets/js/interaction.js`: 인트로, 핀스크롤, 제품 선택, 메뉴·드롭다운 동작.
- `public/assets/js/contact-form.js`: 입력값 확인·오류 안내·글자 수 표시·`/api/contact` 전송과 결과 안내.
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

개인정보 보유기간(응대 완료 후 1년 이내 삭제)은 V1 운영 기준을 옮긴 것으로, 공개 전 확정이 필요합니다. 메일 알림(SMTP)은 연결하지 않았습니다.
