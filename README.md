# MathsOnline RESTful APIs Demo

## About

This project is a demo of the centralized RESTful APIs for MathsOnline system. It is built using Laravel 10 and PHP 8.2.

## Installation

### Prerequisites

- [Docker & Docker Desktop](https://www.docker.com/products/docker-desktop)
- PHP 8.2 or later
- Composer

### Steps

1. Clone the repository and navigate to the project directory.
2. Duplicate the `.env.example` file and rename it to `.env`, then update the environment credentials in the new file.

    ```bash
    copy .env.example .env
    ```

3. Install the project dependencies using Composer.

    ```bash
    composer install
    ```

4. Start the Docker containers using the following command and then open a shell to the PHP container.

    ```bash
    sail up -d
    sail shell
    ```

5. Generate the application key.

    ```bash
    php artisan key:generate
    ```

6. Run the database migrations and seed the database.

    ```bash
    php artisan migrate:refresh --seed
    ```

7. Now you can access APIs, or run the tests using the following command.

    ```bash
    php artisan test
    ```
