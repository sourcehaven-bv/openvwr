alter table
  "public_website_tree"
add
  column "public_url" varchar(255) null;

INSERT INTO "admin_log_entries" (
  "message", "created_at", "updated_at"
)
values
  (
    'Migrated "2026_02_26_173816_add_public_url_to_public_website_tree"',
    now(),
    now()
  );