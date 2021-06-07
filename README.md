# ControlPanel's Dashboard
ControlPanel's Dashboard is a dashboard application designed to offer clients a management tool to manage their pterodactyl servers. This dashboard comes with a credit-based billing solution that credits users hourly for each server they have and suspends them if they run out of credits.

This dashboard is built with Laravel as it offers a very robust and secure development environment.


I copied a part of pterodactyl’s documentation for the installation because it uses the same dependencies and covers this area pretty good.



## Dependencies
* PHP `7.4` or `8.0` (recommended) with the following extensions: `cli`, `openssl`, `gd`, `mysql`, `PDO`, `mbstring`, `tokenizer`, `bcmath`, `xml` or `dom`, `curl`, `zip`, and `fpm` if you are planning to use NGINX.
* MySQL `5.7.22` or higher (MySQL `8` recommended) **or** MariaDB `10.2` or higher.
* Redis (`redis-server`)
* A webserver (Apache, NGINX, Caddy, etc.)
* `curl`
* `tar`
* `unzip`
* `git`
* `composer` v2

### Example Dependency Installation
if you already have pterodactyl installed you can skip this step!

The commands below are simply an example of how you might install these dependencies. Please consult with your
operating system's package manager to determine the correct packages to install.

``` bash
# Add "add-apt-repository" command
apt -y install software-properties-common curl apt-transport-https ca-certificates gnupg

# Add additional repositories for PHP, Redis, and MariaDB
LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
add-apt-repository -y ppa:chris-lea/redis-server
curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash

# Update repositories list
apt update

# Add universe repository if you are on Ubuntu 18.04
apt-add-repository universe

# Install Dependencies
apt -y install php8.0 php8.0-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip} mariadb-server nginx tar unzip git redis-server
```
### Extra dependency used on this dashboard
you need to install this, use the appropriate php version (php -v)
```bash
sudo apt-get install php8.0-intl
```

### Installing Composer
if you already have pterodactyl installed you can skip this step!

Composer is a dependency manager for PHP that allows us to ship everything you'll need code wise to operate the Panel. You'll
need composer installed before continuing in this process.

``` bash
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

## Download Files
The first step in this process is to create the folder where the panel will live and then move ourselves into that
newly created folder. Below is an example of how to perform this operation.

``` bash
mkdir -p /var/www/dashboard
cd /var/www/dashboard
```

``` bash
git clone https://github.com/ControlPanel-gg/dashboard.git ./
chmod -R 755 storage/* bootstrap/cache/
```

## Installation
Now that all of the files have been downloaded we need to configure some core aspects of the Panel.

You will need a database setup and a user with the correct permissions created for that database before
continuing any further.


First we will copy over our default environment settings file, install core dependencies, and then generate a
new application encryption key.

``` bash
cp .env.example .env
composer install

# Only run the command below if you are installing this Panel
php artisan key:generate --force


# you should create a symbolic link from public/storage to storage/app/public
php artisan storage:link
```


Back up your encryption key (APP_KEY in the `.env` file). It is used as an encryption key for all data that needs to be stored securely (e.g. api keys).
Store it somewhere safe - not just on your server. If you lose it all encrypted data is irrecoverable -- even if you have database backups.

### Environment Configuration
Simply edit the .env to your needs

Please **do not** forget to enter the database creds in here, or the next step won't work
Please **do not** forget to enter your pterodactyl api key in here, or the next steps won't work

``` bash
nano .env
```

### Database Setup
Now we need to setup all of the base data for the Panel in the database you created earlier. **The command below
may take some time to run depending on your machine. Please _DO NOT_ exit the process until it is completed!** This
command will setup the database tables and then add all of the Nests & Eggs that power Pterodactyl.

``` bash
php artisan migrate --seed --force
```

### Add The First User
``` bash
php artisan make:user
```

### Set Permissions
The last step in the installation process is to set the correct permissions on the Panel files so that the webserver can
use them correctly.

``` bash
# If using NGINX or Apache (not on CentOS):
chown -R www-data:www-data /var/www/dashboard/*

# If using NGINX on CentOS:
chown -R nginx:nginx /var/www/dashboard/*

# If using Apache on CentOS
chown -R apache:apache /var/www/dashboard/*
```

### Crontab Configuration
The first thing we need to do is create a new cronjob that runs every minute to process specific Dashboard tasks. like billing users hourly and suspending unpaid servers

```bash
* * * * * php /var/www/dashboard/artisan schedule:run >> /dev/null 2>&1
```


### Nginx
You should paste the contents of the file below, replacing <domain> with your domain name being used in a file called dashboard.conf and place it in /etc/nginx/sites-available/, or — if on CentOS, /etc/nginx/conf.d/.

```bash
server {
        listen 80;
        root /var/www/dashboard/public;
        index index.php index.html index.htm index.nginx-debian.html;
        server_name YOUR.DOMAIN.COM;

        location / {
                try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        }

        location ~ /\.ht {
                deny all;
        }
}
```

### Enable configuration
The final step is to enable your NGINX configuration and restart it.

```bash
# You do not need to symlink this file if you are using CentOS.
sudo ln -s /etc/nginx/sites-available/dashboard.conf /etc/nginx/sites-enabled/dashboard.conf

# Check for nginx errors
sudo nginx -t

# You need to restart nginx regardless of OS. only do this you haven't received any errors
systemctl restart nginx
```
