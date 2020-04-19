FROM php:7.4-cli-alpine

# Ensure we update the base image
RUN apk update && apk add build-base
# Install pspell as we need the dictionaries
RUN apk add aspell-dev
RUN apk add php7-pspell \
    && docker-php-ext-install pspell
RUN apk add aspell-en aspell-de aspell-fr

# Install XDebug as we're in a test container
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.9.4 \
    && docker-php-ext-enable xdebug

# Download and install the latest composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/ \
    && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer \
    && composer global require hirak/prestissimo --no-plugins --no-scripts

# Copy the composer file and run composer install, this improves caching
# See https://medium.com/@c.harrison/speedy-composer-installs-in-docker-builds-41eea6d0172b for detail
COPY ./composer.json /app/composer.json
WORKDIR /app
RUN composer install --prefer-dist --no-scripts --no-autoloader

# Copy our code base, set the work directory, generate the autoloaders and set the path
COPY . /app
WORKDIR /app
RUN composer dump-autoload --no-scripts --optimize
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"

# By default we will run the test suite
CMD composer test