FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        zip \
        unzip \
        curl \
        vim \
        supervisor \
        sudo \
        procps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Pin a specific version for reproducible builds: check https://github.com/mlocati/docker-php-extension-installer/releases
RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions xdebug pgsql pdo_pgsql intl zip apcu mbstring sodium

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENV USER=docker
ENV HOME=/home/$USER
ENV WORKDIR=/var/www/html

RUN useradd -ms /bin/bash $USER \
    && adduser $USER sudo \
    && echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && mkdir -p $HOME \
    && chown $USER:$USER $HOME

WORKDIR $WORKDIR

COPY ./messenger-workers.conf /etc/supervisor/conf.d/messenger-workers.conf
COPY ./run_php.sh $HOME/run_php.sh
RUN chown docker:docker $HOME/run_php.sh && chmod 755 $HOME/run_php.sh

EXPOSE 9000

USER $USER

CMD ["/home/docker/run_php.sh"]
