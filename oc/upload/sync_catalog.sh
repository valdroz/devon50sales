#!/usr/bin/env bash

cp -r catalog /var/www/html/
chown -R www-data:www-data /var/www/html/catalog
cp -r reports /var/www/html/
chown -R www-data:www-data /var/www/html/reports

