#!/usr/bin/env bash

cp -r catalog /var/www/fundraiser.devon50.org/
cp -r reports /var/www/fundraiser.devon50.org/
cp -r system /var/www/fundraiser.devon50.org/
cp -r admin/controller /var/www/fundraiser.devon50.org/admin/
cp -r admin/language /var/www/fundraiser.devon50.org/admin/
cp -r admin/model /var/www/fundraiser.devon50.org/admin/
cp -r admin/view /var/www/fundraiser.devon50.org/admin/
cp -r admin/index.php /var/www/fundraiser.devon50.org/admin/index.php

chown -R www-data:www-data /var/www/fundraiser.devon50.org/

