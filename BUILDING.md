# Building the development environment

cd into the project directory and run the following command: `sh bin/startdocker`
This should start building the images and start the containers.

After that you need to go into the controlpanel_php container and run some commands:

Type `docker exec -it controlpanel_php ash` to go into the container and run the following commands:

```shell
composer install
cp .env.dev .env
php artisan key:generate --force
php artisan storage:link
php artisan migrate --seed --force
```
