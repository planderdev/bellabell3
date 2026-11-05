<?php
declare(strict_types=1);

// What /admin/sections can edit: page > section > fields. "key" is the field's path in content.php.
$text = fn (string $key, string $label): array => ['key' => $key, 'label' => $label, 'type' => 'text'];
$long = fn (string $key, string $label): array => ['key' => $key, 'label' => $label, 'type' => 'textarea'];
$link = fn (string $key, string $label): array => ['key' => $key, 'label' => $label, 'type' => 'link'];
$image = fn (string $key, string $label): array => ['key' => $key, 'label' => $label, 'type' => 'image'];
$repeat = function (int $count, callable $fields): array {
    $all = [];
    for ($index = 0; $index < $count; $index++) {
        array_push($all, ...$fields($index, $index + 1));
    }
    return $all;
};

return [
    'home' => ['label' => '메인', 'url' => '/', 'sections' => [
        'hero' => ['label' => '첫 화면', 'anchor' => '#intro', 'note' => '배경 영상은 용량 제한으로 여기서 바꿀 수 없습니다.', 'fields' => [
            $text('intro_title', '제목'),
            $text('intro_body', '설명'),
        ]],
        'about' => ['label' => '브랜드 소개 링크', 'anchor' => '#about-reveal', 'fields' => [
            $long('about_title', '제목'),
            $text('about_highlight', '강조 문구'),
            $image('about_image', '배경 이미지'),
            ...$repeat(4, fn (int $i, int $n): array => [
                $text("about_links.$i.label", "링크 $n 문구"),
                $link("about_links.$i.href", "링크 $n 주소"),
            ]),
        ]],
        'pillars' => ['label' => '사업 분야', 'anchor' => '#pillars', 'note' => '영상은 용량 제한으로 여기서 바꿀 수 없습니다.', 'fields' => [
            $text('pillars.0', '라벨 1'),
            $text('pillars.1', '라벨 2'),
            $text('pillars.2', '라벨 3'),
        ]],
        'bongbong' => ['label' => '봉봉이 이야기', 'anchor' => '#bongbong-story', 'fields' => [
            $long('bongbong_title', '제목'),
            $long('bongbong_body', '본문'),
            ...$repeat(3, fn (int $i, int $n): array => [
                $image("bongbong_photos.$i.src", "사진 $n"),
                $text("bongbong_photos.$i.alt", "사진 $n 설명"),
            ]),
        ]],
        'research' => ['label' => '연구·기술', 'anchor' => '#research', 'note' => '슬라이드 이미지는 연구·기술 슬라이드 메뉴에서 관리합니다.', 'fields' => [
            $text('labels.research', '영문 라벨'),
            $text('research', '제목'),
            $long('research_statement', '설명'),
        ]],
        'social' => ['label' => '사회적 가치', 'anchor' => '#social', 'fields' => [
            $text('labels.social', '영문 라벨'),
            $text('social', '제목'),
            $long('social_body', '설명'),
            $image('social_image', '배경 이미지'),
            ...$repeat(5, fn (int $i, int $n): array => [
                $text("values.$i", "카드 $n 제목"),
                $long("value_descriptions.$i", "카드 $n 설명"),
            ]),
        ]],
        'family' => ['label' => '브랜드 패밀리', 'anchor' => '#family', 'fields' => [
            $text('family', '제목'),
            $text('brands.0.name', '브랜드 1 이름'),
            $text('brands.0.description', '브랜드 1 설명'),
            ...$repeat(3, fn (int $i, int $n): array => [
                $image("brands.0.photos.$i.src", "브랜드 1 사진 $n"),
                $text("brands.0.photos.$i.alt", "브랜드 1 사진 $n 설명"),
            ]),
            $text('brands.1.name', '브랜드 2 이름'),
            $text('brands.1.description', '브랜드 2 설명'),
            $image('brands.1.photos.0.src', '브랜드 2 사진'),
            $text('brands.1.photos.0.alt', '브랜드 2 사진 설명'),
        ]],
        'global' => ['label' => '글로벌', 'anchor' => '#global', 'fields' => [
            $text('labels.global', '영문 라벨'),
            $text('global_title', '제목'),
            $image('global_image', '배경 이미지'),
            ...$repeat(4, fn (int $i, int $n): array => [
                $text("global_cards.$i.title", "카드 $n 제목"),
                $long("global_cards.$i.desc", "카드 $n 설명"),
                $image("global_cards.$i.image", "카드 $n 이미지"),
            ]),
        ]],
        'partner' => ['label' => '제안 & 협업', 'anchor' => '#partner', 'note' => '문의 폼 항목은 전송 기능과 연결되어 있어 여기서 바꿀 수 없습니다.', 'fields' => [
            $text('labels.partner', '영문 라벨'),
            $text('partner_title', '제목'),
            $text('partner', '설명'),
            $text('contact_intro.0', '안내 문구 1'),
            $text('contact_intro.1', '안내 문구 2'),
        ]],
    ]],
    'history' => ['label' => '연혁', 'url' => '/history', 'sections' => [
        'history' => ['label' => '연혁', 'note' => '날짜는 2025.12 또는 2026.01.23 형식으로 적어 주세요. 앞 네 자리로 연도를 나눕니다.', 'fields' => [
            $text('labels.history', '영문 라벨'),
            $text('history_title', '제목'),
            ...$repeat(42, fn (int $i, int $n): array => [
                $text("history.$i.date", "항목 $n 날짜"),
                $text("history.$i.text", "항목 $n 내용"),
            ]),
        ]],
    ]],
    'brand' => ['label' => '브랜드', 'url' => '/brand', 'sections' => [
        'common' => ['label' => '제목·메뉴', 'fields' => [
            $text('brand_title', '페이지 제목'),
            $text('brand_tabs.0.name', '탭 1 이름'),
            $text('brand_tabs.1.name', '탭 2 이름'),
            $text('brand_tabs.2.name', '탭 3 이름'),
        ]],
        'bellabelfarm' => ['label' => '벨아벨팜', 'anchor' => '#bellabelfarm', 'fields' => [
            $text('brand_groups.0', '큰 제목 1'),
            $text('brand_groups.1', '큰 제목 2'),
            ...$repeat(6, fn (int $i, int $n): array => [
                $long("brand_slides.bellabelfarm.$i.title", "슬라이드 $n 제목"),
                $long("brand_slides.bellabelfarm.$i.body", "슬라이드 $n 본문"),
                $image("brand_slides.bellabelfarm.$i.image", "슬라이드 $n 배경"),
            ]),
            ...$repeat(3, fn (int $i, int $n): array => [
                $text("brand_slides.bellabelfarm.1.stats.$i.value", "슬라이드 2 숫자 $n (숫자만, 예: 1,800)"),
                $text("brand_slides.bellabelfarm.1.stats.$i.unit", "슬라이드 2 숫자 $n 단위"),
                $text("brand_slides.bellabelfarm.1.stats.$i.label", "슬라이드 2 숫자 $n 설명"),
            ]),
            $long('brand_slides.bellabelfarm.1.body_after', '슬라이드 2 본문 아래 문장'),
            $image('brand_slides.bellabelfarm.0.logo', '슬라이드 1 로고'),
            $text('brand_slides.bellabelfarm.0.page_link.label', '슬라이드 1 연혁 버튼 문구'),
            ...$repeat(2, fn (int $i, int $n): array => [
                $image("brand_slides.bellabelfarm.3.certificates.$i.image", "슬라이드 4 인증서 $n 이미지"),
                $text("brand_slides.bellabelfarm.3.certificates.$i.caption", "슬라이드 4 인증서 $n 이름"),
            ]),
        ]],
        'fromhalla' => ['label' => '프롬한라', 'anchor' => '#fromhalla', 'fields' => [
            ...$repeat(5, fn (int $i, int $n): array => [
                $long("brand_slides.fromhalla.$i.title", "슬라이드 $n 제목"),
                $long("brand_slides.fromhalla.$i.body", "슬라이드 $n 본문"),
                $image("brand_slides.fromhalla.$i.image", "슬라이드 $n 배경"),
            ]),
            $text('brand_slides.fromhalla.3.lineup.title', '슬라이드 4 버튼 묶음 제목'),
            ...$repeat(3, fn (int $i, int $n): array => [
                $text("brand_slides.fromhalla.3.lineup.items.$i.label", "슬라이드 4 버튼 $n 이름"),
            ]),
            $image('brand_slides.fromhalla.1.figure.image', '슬라이드 2 캐릭터 이미지'),
            $text('brand_slides.fromhalla.1.figure.caption', '슬라이드 2 캐릭터 설명'),
        ]],
        'awoo' => ['label' => '아우아우', 'anchor' => '#awoo', 'fields' => [
            ...$repeat(3, fn (int $i, int $n): array => [
                $long("brand_slides.awoo.$i.title", "슬라이드 $n 제목"),
                $long("brand_slides.awoo.$i.body", "슬라이드 $n 본문"),
                $image("brand_slides.awoo.$i.image", "슬라이드 $n 배경"),
            ]),
            ...$repeat(7, fn (int $i, int $n): array => [
                $image("brand_slides.awoo.1.space_gallery.$i", "슬라이드 2 공간 사진 $n"),
            ]),
        ]],
    ]],
    'product' => ['label' => '상품소개', 'url' => '/product', 'sections' => [
        'hero' => ['label' => '첫 화면', 'note' => '영상은 용량 제한으로 여기서 바꿀 수 없습니다.', 'fields' => [
            $text('product.label', '영문 라벨'),
            ...$repeat(4, fn (int $i, int $n): array => [$text("product.hero_lines.$i", "제목 $n 줄")]),
            $text('product.hero_body.0', '설명 1 줄'),
            $text('product.hero_body.1', '설명 2 줄'),
            $text('product.hero_button', '버튼 문구'),
        ]],
        'family' => ['label' => '제품 목록', 'anchor' => '#products', 'fields' => [
            $text('product.family_heading', '제목'),
            $text('product.card_link', '상품 보기 문구'),
            $link('product.store_url', '상품 보기 주소'),
            ...$repeat(5, fn (int $i, int $n): array => [$text("product_categories.$i.name", "분류 $n 이름")]),
        ]],
        'notes' => ['label' => '카드 4개', 'fields' => [
            $text('product.notes_heading', '제목'),
            ...$repeat(4, fn (int $i, int $n): array => [
                $text("product.notes.$i.title", "카드 $n 제목"),
                $long("product.notes.$i.body", "카드 $n 설명"),
            ]),
        ]],
        'stories' => ['label' => '제품 이야기', 'fields' => [
            $text('product.stories_heading', '제목'),
            ...$repeat(3, fn (int $i, int $n): array => [
                $text("product_stories.$i.tab", "탭 $n 이름"),
                $text("product_stories.$i.title", "탭 $n 제목"),
                $long("product_stories.$i.body", "탭 $n 설명"),
                $image("product_stories.$i.image.src", "탭 $n 사진"),
                $text("product_stories.$i.image.alt", "탭 $n 사진 설명"),
            ]),
        ]],
        'points' => ['label' => '카드 3개', 'fields' => [
            $text('product.points_heading', '제목'),
            ...$repeat(3, fn (int $i, int $n): array => [
                $text("product.points.$i.title", "카드 $n 제목"),
                $long("product.points.$i.body", "카드 $n 설명"),
            ]),
        ]],
        'closing' => ['label' => '마지막 문구', 'fields' => [
            $text('product.closing_lines.0', '제목 1 줄'),
            $text('product.closing_lines.1', '제목 2 줄'),
            $text('product.closing_button', '버튼 문구'),
        ]],
    ]],
    'ingredients' => ['label' => '원료소개', 'url' => '/ingredients', 'sections' => [
        'intro' => ['label' => '소개', 'note' => '원료 항목은 원료 관리 메뉴에서 관리합니다. 위쪽 영상은 용량 제한으로 여기서 바꿀 수 없습니다.', 'fields' => [
            $text('ingredients_title', '페이지 제목'),
            $text('ingredients_intro_title', '소개 제목'),
            $long('ingredients_intro_body', '소개 설명'),
        ]],
    ]],
    'footer' => ['label' => '푸터', 'url' => '/', 'sections' => [
        'company' => ['label' => '회사 정보', 'anchor' => '#footer', 'fields' => [
            $text('footer.company', '회사명'),
            $text('footer.ceo', '대표이사'),
            $text('footer.privacy', '개인정보보호책임자'),
            $text('footer.business_number', '사업자등록번호'),
            $text('footer.mail_order', '통신판매업 신고번호'),
            $text('footer.address', '주소'),
            $text('footer.phone', '전화번호 표시'),
            $link('footer.tel', '전화번호 링크 (tel:0648030060 형식)'),
            $text('footer.email', '문의 이메일'),
            $text('footer.copyright', '저작권 문구'),
        ]],
        'hours' => ['label' => '고객센터·카카오톡', 'anchor' => '#footer', 'fields' => [
            $text('footer.hours_title', '운영시간 제목'),
            $text('footer.hours', '운영시간'),
            $text('footer.lunch', '점심시간'),
            $text('footer.holiday', '휴무 안내'),
            $text('footer.kakao_note', '카카오톡 안내'),
        ]],
    ]],
];
