# Audit log

OpenVWR writes audit events, such as logins and account changes, to syslog. It
also writes them to the `audit_logs` table when `AUDIT_LOG_DBLOG_ENABLED` is set.

## Append-only table

The `audit_logs` table is append-only. A PostgreSQL trigger,
`audit_logs_append_only`, rejects every `UPDATE`, `DELETE` and `TRUNCATE` on the
table. Inserts still work. The trigger also blocks raw SQL, so a bug or an SQL
injection in the application cannot change or remove audit events.

The migration `make_audit_logs_append_only` creates the trigger.

## Limits

The table owner can disable or drop the trigger. In a default deployment, the
application connects as the table owner. Someone with the application's database
credentials can therefore still remove events.

For stronger protection, run migrations as a separate owner role. Give the
application role only the rights it needs on this table:

```sql
REVOKE ALL ON audit_logs FROM <app_role>;
GRANT SELECT, INSERT ON audit_logs TO <app_role>;
```

## Removing old events

The application never removes audit events. To remove events after their
retention period, a database administrator disables the trigger for one
transaction:

```sql
BEGIN;
ALTER TABLE audit_logs DISABLE TRIGGER audit_logs_append_only;
DELETE FROM audit_logs WHERE created_at < now() - interval '<retention period>';
ALTER TABLE audit_logs ENABLE TRIGGER audit_logs_append_only;
COMMIT;
```

`ALTER TABLE` locks the table until the transaction ends. Inserts by the
application wait during that time.
