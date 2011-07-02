#!/bin/sh
/usr/local/bin/php -d include_path='.:/usr/local/lib/php:/virtual/accessmwc/public_html:/virtual/access/public_html/pear/php' /virtual/access/public_html/cron/msnote/exe.php >/dev/null
# /usr/local/bin/php -d include_path='.:/usr/local/lib/php:/virtual/accessmwc/public_html:/virtual/access/public_html/pear/php' /virtual/access/public_html/cron/question/exe.php >/dev/null
exit
