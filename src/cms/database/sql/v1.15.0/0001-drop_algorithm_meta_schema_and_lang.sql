alter table
  "algorithm_records"
drop
  constraint "algorith_records_algor_meta_schem_id_foreign";

alter table
  "algorithm_records"
drop
  column "algorithm_meta_schema_id",
drop
  column "meta_lang";

drop
  table if exists "algorithm_meta_schemas";

INSERT INTO "admin_log_entries" (
  "message", "created_at", "updated_at"
)
values
  (
    'Migrated "2026_07_21_120000_drop_algorithm_meta_schema_and_lang"',
    now(),
    now()
  );
