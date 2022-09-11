#!/usr/bin/env bash

cp -r catalog /var/www/html/
cp -r reports /var/www/html/
cp -r system /var/www/html/
cp -r admin/controller /var/www/html/admin/
cp -r admin/language /var/www/html/admin/
cp -r admin/model /var/www/html/admin/
cp -r admin/view /var/www/html/admin/
cp -r admin/index.php /var/www/html/admin/index.php

chown -R www-data:www-data /var/www/html/

