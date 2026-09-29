#!/bin/sh
# Jalankan inisialisasi database (aman diulang), lalu nyalakan Apache.
set -e
php /var/www/html/init.php || echo "init.php gagal, Apache tetap dinyalakan"
exec apache2-foreground
