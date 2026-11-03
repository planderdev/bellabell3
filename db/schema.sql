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

alter table contact_attempts   enable row level security;
alter table business_inquiries enable row level security;
alter table research_slides    enable row level security;

alter table business_inquiries owner to bellabell_app;
grant usage, select on sequence business_inquiries_id_seq to bellabell_app;
alter table research_slides owner to bellabell_app;
grant usage, select on sequence research_slides_id_seq to bellabell_app;
