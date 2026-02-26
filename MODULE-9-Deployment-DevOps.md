## TASK: Create Deployment Configuration

FILE STRUCTURE:
istanbul-airport-transfer/
├── assets/
│   ├── css/ (admin.css, public.css)
│   ├── js/ (admin.js, public.js)
│   └── images/
├── includes/
│   ├── class-iat.php (Main plugin class)
│   ├── class-iat-regions.php
│   ├── class-iat-pricing.php
│   ├── class-iat-booking.php
│   ├── class-iat-geocoding.php
│   ├── class-iat-api-rotator.php
│   ├── class-iat-emails.php
│   └── class-iat-shortcodes.php
├── admin/
│   ├── class-iat-admin.php
│   └── views/ (region-editor.php, pricing-matrix.php, api-settings.php)
├── languages/ (.pot, .po, .mo files)
├── vendor/ (composer deps: GeoPHP)
├── tests/ (phpunit)
├── istanbul-airport-transfer.php
├── uninstall.php
└── readme.txt

BUILD SCRIPT (package.json):
{
  "scripts": {
    "build": "webpack --mode=production",
    "dev": "webpack --mode=development --watch",
    "test": "phpunit",
    "zip": "npm run build && composer install --no-dev && zip -r plugin.zip . -x '*.git*' 'node_modules/*' 'tests/*'"
  }
}

WORDPRESS ORG PREP:
- readme.txt with changelog
- screenshots (assets/)
- banner-772x250.jpg
- icon-256x256.jpg

DOCKER (local dev):
version: '3'
services:
  wordpress:
    image: wordpress:latest
    ports: ["8080:80"]
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
    volumes:
      - ./:/var/www/html/wp-content/plugins/istanbul-airport-transfer
  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress