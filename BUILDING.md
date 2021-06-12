# Building the development environment

cd into the project directory and run the following command: `sh bin/startdocker.sh`
This should start building the images and start the containers.

After that you need to go into the controlpanel_php container and run some commands:

Type `docker exec -it controlpanel_php ash` to go into the container and run the following commands:

```shell
composer install
cp .env.example .env
php artisan key:generate --force
php artisan storage:link
php artisan migrate --seed --force
```

## Setting up testing environment

Create the .env.testing file to your needs. Then once done you need to go into your phpmyadmin to create a new database named __controlpanel_test__.
Visit http://127.0.0.1:8080/ and create your database.

Now you're ready to run the following commands which switches to the testing config, migrates the test database and seeds it.
After that you can switch back to your dev environment again. Clear the config from cache so changes will be instantly available.

```shell
php artisan key:generate --force --env=testing
php artisan migrate:fresh --seed --env=testing
```

Now when running tests with PHPUnit it will use your testing database and not your local development one.
This is configured in the __phpunit.xml__. You can run your tests by running the command like this. Just type and enter.
`php artisan test`.


