FROM php:fpm

# Install PHP extensions
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions gd gmp intl bcmath zip pdo_mysql redis pcntl xdebug

# Install Supervisor & Ping & Nano
RUN apt-get update && apt-get install -y \
    supervisor \
    iputils-ping \
    nano

# Install Composer
RUN curl https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and NPM
RUN curl -L https://deb.nodesource.com/setup_current.x | bash -
RUN apt-get install -y nodejs

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

CMD ["php-fpm"]

EXPOSE 9000