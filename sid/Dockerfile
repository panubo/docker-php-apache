# Panubo PHP-Apache
#
# Debian Sid (Unstable)
# PHP 8.1
# Apache 2.4
# Mongo support
#

FROM debian:sid

# Component Versions
ENV \
  BASHCONTAINER_VERSION=0.7.2 BASHCONTAINER_SHA256=87c4b804f0323d8f0856cb4fbf2f7859174765eccc8b0ac2d99b767cecdcf5c6 \
  GOMPLATE_VERSION=3.10.0 GOMPLATE_SHA256=603539aac4e09f98a8ca5b6e5da0c21213221206dc7175a5644255c7a22b936d \
  S6_RELEASE=2.0.7 S6_VERSION=2.11.0.0 S6_SHA256=fcf79204c1957016fc88b0ad7d98f150071483583552103d5822cbf56824cc87 \
  PHPEXTRAS_VERSION=0.1.0 PHPEXTRAS_SHA256=515af5789d5180123acfac9b1090f46e07f355c8df51a34e27ada5f7da0495cc

# Change the www-data use to uid and gid 48 to match other containers
RUN \
  usermod -u 48 www-data && \
  groupmod -g 48 www-data

# Install bash-container functions
RUN set -x \
  && if ! command -v wget > /dev/null; then \
      fetchDeps="${fetchDeps} wget"; \
     fi \
  && apt-get update \
  && apt-get install -y --no-install-recommends ca-certificates curl ${fetchDeps} \
  && cd /tmp \
  && wget -nv https://github.com/panubo/bash-container/releases/download/v${BASHCONTAINER_VERSION}/panubo-functions.tar.gz \
  && echo "${BASHCONTAINER_SHA256}  panubo-functions.tar.gz" > /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum panubo-functions.tar.gz)"; exit 1; )) \
  && tar --no-same-owner -C / -zxf panubo-functions.tar.gz \
  && rm -rf /tmp/* \
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false ${fetchDeps} \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  ;

# Install gomplate
RUN set -x \
  && if ! command -v wget > /dev/null; then \
      fetchDeps="${fetchDeps} wget"; \
     fi \
  && apt-get update \
  && apt-get install -y --no-install-recommends ca-certificates ${fetchDeps} \
  && wget -nv -O /tmp/gomplate_linux-amd64-slim -L https://github.com/hairyhenderson/gomplate/releases/download/v${GOMPLATE_VERSION}/gomplate_linux-amd64-slim \
  && echo "${GOMPLATE_SHA256}  gomplate_linux-amd64-slim" > /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum gomplate_linux-amd64-slim)"; exit 1; )) \
  && mv /tmp/gomplate_linux-amd64-slim /usr/local/bin/gomplate \
  && chmod +x /usr/local/bin/gomplate \
  && rm -rf /tmp/* \
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false ${fetchDeps} \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  ;

# Install s6
RUN set -x \
  && DIR=$(mktemp -d) && cd ${DIR} \
  && curl -s -L https://github.com/just-containers/skaware/releases/download/v${S6_RELEASE}/s6-${S6_VERSION}-linux-amd64-bin.tar.gz -o s6.tar.gz \
  && echo "${S6_SHA256}  s6.tar.gz" > SHA256SUM \
  && ( sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum s6.tar.gz)"; exit 1; )) \
  && tar -xzf s6.tar.gz -C /usr/local/ \
  && rm -rf ${DIR}

# Install PHP Extras
RUN set -x \
  && if ! command -v wget > /dev/null; then \
      fetchDeps="${fetchDeps} wget"; \
     fi \
  && apt-get update \
  && apt-get install -y --no-install-recommends ${fetchDeps} \
  && cd /tmp \
  && wget -nv https://github.com/panubo/php-extras/releases/download/v${PHPEXTRAS_VERSION}/php-extras.tar.gz \
  && echo "${PHPEXTRAS_SHA256}  php-extras.tar.gz" > /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum php-extras.tar.gz)"; exit 1; )) \
  && mkdir -p /usr/share/php/ \
  && tar --no-same-owner -C /usr/share/php/ -zxf php-extras.tar.gz \
  && rm -rf /tmp/* \
  && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false ${fetchDeps} \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  ;

# Install main packages
RUN \
  export DEBIAN_FRONTEND=noninteractive && \
  apt-get update && \
  apt-get install --no-install-recommends --no-install-suggests -y wget curl ca-certificates git gnupg openssh-client msmtp-mta apache2 libapache2-mod-xsendfile imagemagick ghostscript \
  php8.1-apcu \
  php8.1-cli \
  php8.1-curl \
  php8.1-dom \
  php8.1-fpm \
  php8.1-gd \
  php8.1-igbinary \
  php8.1-imagick \
  php8.1-imap \
  php8.1-intl \
  php8.1-ldap \
  php8.1-mbstring \
  php8.1-memcached \
  php8.1-mongodb \
  php8.1-mysql \
  php8.1-pgsql \
  php8.1-pspell \
  php8.1-redis \
  php8.1-sqlite \
  php8.1-xmlrpc \
  php8.1-zip && \
  apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/www/html/*

# Configure
RUN \
  echo "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php/8.1/*/php.ini && \
  touch /etc/php/8.1/mods-available/auto.ini && \
  touch /var/log/msmtp.log && \
  chown www-data:www-data /var/log/msmtp.log && \
  sed -i -r 's/^Listen.*/Listen 8000/g' /etc/apache2/ports.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php/8.1/fpm/php-fpm.conf && \
  sed -i -E 's/^;?systemd_interval.*/systemd_interval = 0/' /etc/php/8.1/fpm/php-fpm.conf && \
  mv /etc/php/8.1/fpm/pool.d/www.conf /etc/php/8.1/fpm/pool.d/www.conf_orig && \
  mkdir -p /var/log/php-fpm

# Copy configs and templates
COPY etc /etc
COPY root /

# Enable modules / configs
RUN \
  phpenmod session mongodb && \
  a2dissite 000-default && \
  a2disconf security other-vhosts-access-log && \
  phpenmod auto && \
  a2enconf php8.1-fpm && \
  a2enmod proxy_fcgi remoteip rewrite headers

ENV TMPDIR=/var/tmp TERM=dumb
EXPOSE 8000
ENTRYPOINT ["/entry.sh"]
CMD ["s6"]
