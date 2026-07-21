alter table
  "algorithm_records"
add
  column "meta_date_of_development" date null;

alter table
  "algorithm_records"
add
  column "meta_owner_algorithm" varchar(255) null;

alter table
  "algorithm_records"
add
  column "meta_product_owner_algorithm" varchar(255) null;

alter table
  "algorithm_records"
add
  column "impact_with_consequences" boolean null;

alter table
  "algorithm_records"
add
  column "impact_more_algorithms_applied" boolean null;

alter table
  "algorithm_records"
add
  column "impact_effect_on_outcome" boolean null;

alter table
  "algorithm_records"
add
  column "validation_answers_checked_by_product_owner" boolean null;

INSERT INTO "admin_log_entries" (
  "message", "created_at", "updated_at"
)
values
  (
    'Migrated "2026_05_19_135423_add_algorithm_record_metadata_and_impact_fields"',
    now(),
    now()
  );
