FROM php:7.4-fpm-alpine
ARG USER=default

RUN apk add --no-cache libzip-dev && docker-php-ext-configure zip && docker-php-ext-install zip && \
    apk --update --no-cache add git && \
    apk add --no-cache bash && \
    apk add --no-cache postgresql-client && \
    set -ex && apk --no-cache add postgresql-dev && \
    docker-php-ext-install pdo_pgsql && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin/ --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony/bin/symfony /usr/local/bin/symfony
ARG DATABASE_URL
WORKDIR /var/www
COPY . .
RUN  composer install
CMD  ./wait-for-it.sh gesco-database ; bin/console doctrine:database:drop --force ; bin/console doctrine:database:create ; bin/console doctrine:schema:update --force ; bin/console doctrine:fixtures:load -n; symfony server:start --no-tls

EXPOSE 8000
