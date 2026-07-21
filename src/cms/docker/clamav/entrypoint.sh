#!/bin/sh
set -e

# clamd refuses to start without a signature database, and the Debian package
# ships none. Fetch it on first boot; on later boots the volume already has it
# and we just refresh in the background.
if [ ! -f /var/lib/clamav/main.cvd ] && [ ! -f /var/lib/clamav/main.cld ]; then
    echo "clamav: no signature database found, downloading (this takes a few minutes on first run)..."
    freshclam --foreground --stdout
fi

# Keep signatures current alongside clamd.
freshclam --daemon --stdout &

exec clamd --foreground
