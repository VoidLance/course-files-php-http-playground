# PHP HTTP Playground

A small, dependency-free set of PHP pages for learning how HTTP requests and PHP superglobals work. The examples run locally and display request data, session and cookie behavior, server metadata, and conditional logic.

## Why use this project?

- Explore `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_SESSION`, and `$_COOKIE` in a browser.
- Try query parameters with predefined links or build a custom query string.
- See basic input handling and HTML output escaping in action.
- Learn from self-contained examples without installing packages or configuring a database.

## Get started

### Requirements

- PHP 8.0 or later
- A web browser

No Composer packages or other dependencies are required.

### Run locally

From the project directory, start PHP's built-in web server:

```sh
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/` in your browser, or visit one of the example pages:

| Page | What it demonstrates |
| --- | --- |
| [`superglobals_explorer.php`](superglobals_explorer.php) | GET and POST forms, request/server data, sessions, and cookies |
| [`query_links.html`](query_links.html) | Predefined query examples and a form for building query strings |
| [`query_handler.php`](query_handler.php) | Query parameter validation, sanitization, and escaped output |
| [`server_dashboard.php`](server_dashboard.php) | Server/request metadata, user-agent parsing, and environment values |
| [`user_profile.php`](user_profile.php) | PHP conditionals, validation, simulated login counts, and profile output |

For example, view a query handler response with parameters at:

```text
http://127.0.0.1:8000/query_handler.php?name=Alex&role=student
```

On the superglobals page, submit the POST and GET forms and use the reset buttons to explore session and cookie behavior. The first cookie request may require a refresh before the browser sends the cookie back.

## Help

For questions or problems, [open an issue](https://github.com/VoidLance/course-files-php-http-playground/issues). The PHP manual is also useful for further reading:

- [PHP superglobals](https://www.php.net/manual/en/language.variables.superglobals.php)
- [PHP built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php)

## Maintainers and contributing

This learning project is maintained by the repository owner and its contributors. See the [contributors page](https://github.com/VoidLance/course-files-php-http-playground/graphs/contributors) for contributor activity.

Contributions are welcome. Open an issue to discuss a change, or submit a pull request with a focused improvement and a description of how you verified it. Keep examples beginner-friendly and avoid adding dependencies unless they are necessary.

## Safety note

These pages are educational examples, not production-ready applications. In particular, `server_dashboard.php` displays server details and environment variable names and values (with sensitive-looking values masked); use it only in a trusted local environment, not on a public server.
