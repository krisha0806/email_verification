#!/bin/bash

# Get absolute path to cron.php
CRON_PHP_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.php"

# Create a cron entry
CRON_JOB="*/5 * * * * php $CRON_PHP_PATH"

# Check if the job already exists to prevent duplication
( crontab -l 2>/dev/null | grep -v -F "$CRON_PHP_PATH" ; echo "$CRON_JOB" ) | crontab -

echo "✅ CRON job installed to run cron.php every 5 minutes."
