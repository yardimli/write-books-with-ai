
## About Playground

Playground is a application that uses different AI models to write books. You can use the application to generate books, stories, and other content. You can use OpenRouter or OpenAI or Anthropic to generate content currently.

## Contributing

Thank you for considering contributing to the Playground framework! The contribution guide can be found in the [Laravel documentation](https://writebookswithai.com/contributions).

## Code of Conduct

In order to ensure that the Playground community is welcoming to all, please review and abide by the [Code of Conduct](https://writebookswithai.com/docs/contributions#code-of-conduct).

## License

Playground is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

#
### Playground SETUP

run `sudo find /var/www/writebookswithai -type d -exec chmod 775 {} \;`

run `sudo find /var/www/writebookswithai -type f -exec chmod 775 {} \;`

run `sudo chown -R ubuntu:www-data /var/www/writebookswithai`

run `composer install`

run `composer require pgvector/pgvector`

run `php artisan key:generate`

edit `the .env file to match your database credentials for boty mysql and postgres`

run `php artisan migrate`

run `php artisan storage:link`

run `php artisan serve`

run `edit the .env file to include the various AI api keys`

#
### Importing Data

import the postgres dump manually. 
