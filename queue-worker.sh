#!/usr/bin/env bash
#
# Queue worker for cron. Processes ONE queued job per invocation with a
# generous per-job timeout so image generation (FAL / WaveSpeed) is not
# killed mid-run. Point cron at this file instead of a raw artisan call:
#
#   * * * * * cd /path/to/vividpersona && ./queue-worker.sh >> /dev/null 2>&1
#
# Optionally pass the PHP binary as the first argument or set QUEUE_WORKER_PHP
# (cron has a limited PATH), e.g.:
#
#   ./queue-worker.sh /usr/bin/php8.5

# set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP="${1:-${QUEUE_WORKER_PHP:-php}}"

cd "$DIR"

"$PHP" "$DIR/artisan" queue:work \
    --once \
    --name=default \
    --queue=default \
    --backoff=0 \
    --memory=128 \
    --sleep=3 \
    --tries=1 \
    --timeout=300
