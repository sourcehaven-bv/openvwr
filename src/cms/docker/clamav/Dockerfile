# Multi-arch ClamAV image.
#
# The official clamav/clamav image is amd64-only, which forces emulation on
# arm64 hosts (Apple Silicon). Debian ships clamav-daemon for both amd64 and
# arm64, so building on debian:bookworm-slim gives us a native image on every
# platform the base image supports.
FROM debian:bookworm-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        clamav-daemon \
        clamav-freshclam \
        ca-certificates \
        netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# The app connects over TCP (VIRUSSCANNER_SOCKET=tcp://clamav:3310). The Debian
# package defaults to a unix socket in /var/run/clamav, which the unprivileged
# clamav user cannot create in a container, so drop LocalSocket entirely and
# listen on TCP only. Same for freshclam's NotifyClamd, which uses that socket.
RUN sed -i '/^LocalSocket /d; /^LocalSocketGroup /d; /^LocalSocketMode /d; /^FixStaleSocket /d' /etc/clamav/clamd.conf \
    && sed -i '/^NotifyClamd/d' /etc/clamav/freshclam.conf \
    && { \
        echo "TCPSocket 3310"; \
        echo "TCPAddr 0.0.0.0"; \
        echo "Foreground true"; \
        echo "StreamMaxLength 100M"; \
    } >> /etc/clamav/clamd.conf \
    && echo "Foreground true" >> /etc/clamav/freshclam.conf

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 3310

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
