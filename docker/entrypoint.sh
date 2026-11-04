#!/bin/sh
set -e

# storage/ é um volume persistente. Em volumes novos (ou após rodar artisan
# como root) os diretórios podem ficar com dono errado e o php-fpm (www-data)
# recebe "Permission denied" ao gravar cache/views. Garantimos a estrutura e o
# dono a cada boot.
mkdir -p /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/framework/cache/data \
         /var/www/storage/logs \
         /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# As views Blade compiladas sobrevivem ao deploy e ficam com mtime mais novo
# que os fontes da imagem, então o Laravel nunca as recompila. Limpar no boot
# garante que o código novo apareça.
rm -f /var/www/storage/framework/views/*.php

exec "$@"
