#!/bin/sh
/usr/local/bin/php -d include_path='.:/usr/local/lib/php:/virtual/mamasnote/public_html:/virtual/mamasnote/public_html/pear/php' /virtual/mamasnote/public_html/cron/msnote/exe.php >/dev/null
exit
