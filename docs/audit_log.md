# Audit log

OpenVWR records audit events in their own log stream, separate from the
application log. Audit events are logins, failed logins, logouts, account
changes, transfers and removed links between records.

## Where events go

The `audit` log channel writes each event to syslog:

- identifier `openvwr-audit`
- facility `authpriv`
- one JSON object per line

The level is fixed. `LOG_LEVEL` does not filter audit events. To use another
channel, set `AUDIT_LOG_CHANNEL`.

On a systemd host the events end up in journald. Read them with:

```sh
journalctl -t openvwr-audit -o cat
```

The application cannot change or remove events once journald has them. Forward
Secure Sealing (FSS) and the central logging protect them further.

## Routing and access

Route `authpriv`, or identifier `openvwr-audit`, to a separate destination. Give
that destination its own access rights and retention period. Only authorised
administrators may read it.

## What an event contains

| Field | Meaning |
|---|---|
| `created_at` | time of the event (ISO 8601) |
| `event_code` | what happened, for example `091111` for a login |
| `action_code` | `C` create, `R` read, `U` update, `D` delete, `E` execute |
| `failed` | whether the attempt failed |
| `failed_reason` | why it failed, for example `InvalidCode` |
| `user_id` | id of the user who acted, if any |
| `target_id` | id of the user who was affected, if any |
| `data` | ids and counts that belong to the event, such as record ids of a transfer |

Events contain no email addresses, names, codes or request data. A user id only
identifies a person through the database.

## The old `audit_logs` table

Earlier versions wrote events to the `audit_logs` table. The migration
`drop_audit_logs` removes the table when it is empty.

When the table still has rows, the migration leaves it in place, with its
append-only trigger. Archive the rows first. Then remove the table and the
trigger function as the table owner:

```sql
DROP TABLE audit_logs;
DROP FUNCTION audit_logs_append_only();
```
