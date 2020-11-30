#!/usr/bin/env bash

# Enduring html directory exists in case external volume is mouted
mkdir -p /var/www/html

# Copy open card distribution
cp -r /upload/* /var/www/html
chown -R www-data:www-data /var/www

