#!/usr/bin/env bash
# Stops what run.sh started. Leaves Postgres and Mailpit alone: they were not
# ours to start.
set -uo pipefail

RUN="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/.run"

for name in openvwr pratique; do
  pid_file="$RUN/$name.pid"
  [ -f "$pid_file" ] || continue
  pid="$(cat "$pid_file")"
  if kill -0 "$pid" 2>/dev/null; then
    kill "$pid" 2>/dev/null && echo "stopped $name ($pid)"
  fi
  rm -f "$pid_file"
done

# artisan serve spawns a child php process that outlives the parent.
pkill -f "artisan serve --host=127.0.0.1 --port=8000" 2>/dev/null || true
