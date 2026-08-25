alter table
  "users"
add
  column "notification_exclusions" jsonb not null default '[]';

INSERT INTO "admin_log_entries" (
  "message", "created_at", "updated_at"
)
values
  (
    'Migrated "2026_08_24_100000_users_notification_exclusions"',
    now(),
    now()
  );
