-- Bellabell V2 business inquiries. Applied to the Supabase project `bellabell` alongside the V1 tables.
-- The website connects as bellabell_app, which owns the tables, so RLS blocks only Supabase's API roles.
-- contact_attempts (rate limit + single-use tokens) is shared with V1 and already exists there.

create table if not exists contact_attempts (
    id         bigserial primary key,
    ip_hash    text        not null,
    nonce      text        unique,
    created_at timestamptz not null default now()
);
create index if not exists contact_attempts_ip_time on contact_attempts (ip_hash, created_at desc);

create table if not exists business_inquiries (
    id            bigserial primary key,
    inquiry_types text[]      not null check (cardinality(inquiry_types) > 0 and inquiry_types <@ array['distribution','export','bulk','research','partnership','other']),
    company       text        not null,
    contact_name  text        not null,
    email         text        not null,
    phone         text        not null default '',
    website       text        not null default '',
    subject       text        not null,
    message       text        not null,
    ip_hash       text,
    user_agent    text,
    handled_at    timestamptz,
    created_at    timestamptz not null default now()
);
create index if not exists business_inquiries_created on business_inquiries (created_at desc);

-- Research section slider images, uploaded at /admin/slides and served from /media/research/<id>.
create table if not exists research_slides (
    id         bigserial primary key,
    image      bytea       not null,
    mime       text        not null check (mime in ('image/jpeg','image/png','image/webp')),
    width      integer     not null check (width > 0),
    height     integer     not null check (height > 0),
    alt        text        not null default '',
    position   integer     not null default 0,
    created_at timestamptz not null default now()
);

-- A slide is either an uploaded image or, for the temporary slides, a file path under /assets.
alter table research_slides add column if not exists src text;
alter table research_slides alter column image drop not null;

-- Admin edits of site copy and images (/admin/sections). content.php holds the defaults; rows here override them.
-- key is the field's path in content.php; locale is ko, en, zh, or all for images.
create table if not exists site_content (
    key        text        not null,
    locale     text        not null check (locale in ('ko','en','zh','all')),
    value      text        not null,
    updated_at timestamptz not null default now(),
    primary key (key, locale)
);

-- Images uploaded at /admin/sections, served from /media/site/<id>.
create table if not exists site_images (
    id         bigserial primary key,
    image      bytea       not null,
    mime       text        not null check (mime in ('image/jpeg','image/png','image/webp')),
    width      integer     not null check (width > 0),
    height     integer     not null check (height > 0),
    created_at timestamptz not null default now()
);

-- Header menu (/admin/menu). Empty until the page is first opened; the site uses content.php's menu until then.
create table if not exists site_menu_items (
    id        bigserial primary key,
    parent_id bigint references site_menu_items (id) on delete cascade,
    label_ko  text    not null,
    label_en  text    not null default '',
    label_zh  text    not null default '',
    href      text    not null,
    visible   boolean not null default true,
    position  integer not null default 0
);

-- Ingredients on /ingredients, managed at /admin/ingredients. Empty until that page is first opened.
create table if not exists ingredient_items (
    id       bigserial primary key,
    name_ko  text    not null,
    name_en  text    not null default '',
    name_zh  text    not null default '',
    body_ko  text    not null default '',
    body_en  text    not null default '',
    body_zh  text    not null default '',
    image    text    not null default '',
    position integer not null default 0
);

alter table contact_attempts   enable row level security;
alter table business_inquiries enable row level security;
alter table research_slides    enable row level security;

alter table business_inquiries owner to bellabell_app;
grant usage, select on sequence business_inquiries_id_seq to bellabell_app;
alter table research_slides owner to bellabell_app;
grant usage, select on sequence research_slides_id_seq to bellabell_app;
alter table site_content    enable row level security;
alter table site_images     enable row level security;
alter table site_menu_items enable row level security;
alter table site_content owner to bellabell_app;
alter table site_images owner to bellabell_app;
alter table site_menu_items owner to bellabell_app;
grant usage, select on sequence site_images_id_seq to bellabell_app;
grant usage, select on sequence site_menu_items_id_seq to bellabell_app;
alter table ingredient_items enable row level security;
alter table ingredient_items owner to bellabell_app;
grant usage, select on sequence ingredient_items_id_seq to bellabell_app;
