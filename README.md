# Highly Configurable PHP and Apache Images

[![Build Status](https://github.com/panubo/docker-php-apache/actions/workflows/multi-build-push.yml/badge.svg)](https://github.com/panubo/docker-php-apache/actions/workflows/multi-build-push.yml)

These are configurable Docker images for running PHP applications with Apache. They are designed to be easy to use, with a focus on consistent configuration across the images.

These images are available from:

- [Quay.io](https://quay.io/repository/panubo/php-apache)
- [AWS ECR Public Gallery](https://gallery.ecr.aws/panubo/php-apache)

## Features

- **Multiple PHP Versions:** Support for a wide range of PHP versions, from modern PHP 8.x to legacy PHP 5.4.
- **Apache 2.4:** Comes with Apache 2.4 pre-configured to work with PHP-FPM.
- **Process Management:** Uses `s6` for process supervision, ensuring that both Apache and PHP-FPM are always running.
- **Configuration Templating:** Uses `gomplate` to allow for dynamic configuration of Apache, PHP, and other services using environment variables.
- **Common PHP Extensions:** Includes a wide range of commonly used PHP extensions, such as `mysql`, `pgsql`, `mongodb`, `redis`, `gd`, `imagick`, and more.
- **Email Delivery:** Includes `msmtp` for sending emails from PHP applications to an SMTP server.
- **X-Sendfile Support:** `mod_xsendfile` is enabled for efficient file serving.

## Supported Images

### Production Images

- **[Debian 13 (Trixie) Base](/debian13)** - Recommended for PHP applications that require PHP 8.4.
- **[Debian 12 (Bookworm) Base](/debian12)** - Recommended for PHP applications that require PHP 8.2.
- **[Debian 11 (Bullseye) Base](/debian11)** - For applications that require PHP 7.4.

### Legacy Images

- **[Debian 10 (Buster) Base](/debian10)** - For legacy PHP applications that require PHP 7.3.
- **[Debian 9 (Stretch) Base](/debian9)** - For legacy PHP applications that require PHP 7.0.
- **[CentOS 7 Base](/centos7)** - For legacy PHP applications that require PHP 5.4.

_NB. Images may not be feature identical depending on the base OS used and the level of development of the image._

## Usage

Here is a simple example of how to use the image with `docker-compose`:

```yaml
services:
  web:
    image: panubo/php-apache:debian13
    ports:
      - "8080:8000"
    volumes:
      - ./html:/html
    environment:
      # Example of using configuration
      httpd_root: /html/public
```

Place your PHP application code in the `html` directory. The web server will be available on `http://localhost:8080`.

## Configuration

### Environment Variables

The images can be configured using environment variables. These variables are processed by `gomplate` to generate configuration files.

For example, to change the document root, you can set the `httpd_root` environment variable.

For a full list of available variables and templates, please check the `etc` directory within each image's subdirectory.

### PHP Extensions

A wide range of PHP extensions are included by default. You can see the full list in the `Dockerfile` for each image.

### Apache Configuration

The Apache configuration can be extended by mounting your own `.conf` files into `/etc/apache2/conf-enabled/` (for Debian-based images) or `/etc/httpd/conf.d/` (for CentOS-based images).

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue.

## License

This project is licensed under the [MIT License](LICENSE).

## Status

Stable and used in production.
