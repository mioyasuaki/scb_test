#!/bin/sh
/usr/local/bin/php -d include_path='.:/usr/local/lib/php:/virtual/access/public_html:/virtual/access/public_html/pear/php' /virtual/access/public_html/cron/get_tweet/exe.php >/dev/null
exit
