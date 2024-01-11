# Panubo PHP-Apache
#
# Debian bookworm
# PHP 8.2
# Apache 2.4
# Mongo support
#

FROM debian:12

# Component Versions
ENV \
  BASHCONTAINER_VERSION=0.8.0 BASHCONTAINER_SHA256=0ddc93b11fd8d6ac67f6aefbe4ba790550fc98444e051e461330f10371a877f1 \
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
  && GOMPLATE_VERSION=v3.11.6 \
  && GOMPLATE_CHECKSUM_X86_64=7ce8f9f89a0b21fac05b8412af4dd8a06f9e5d8a2df70370549d2dde5f9f0d75 \
  && GOMPLATE_CHECKSUM_AARCH64=f41b6cfaebd9c744c3091993baf9ca44cd80e07d63143d2e78457a159fc22dc5 \
  && if [ "$(uname -m)" = "x86_64" ] ; then \
        GOMPLATE_CHECKSUM="${GOMPLATE_CHECKSUM_X86_64}"; \
        GOMPLATE_ARCH="amd64"; \
      elif [ "$(uname -m)" = "aarch64" ]; then \
        GOMPLATE_CHECKSUM="${GOMPLATE_CHECKSUM_AARCH64}"; \
        GOMPLATE_ARCH="arm64"; \
      fi \
  && curl -sSf -o /tmp/gomplate_linux-${GOMPLATE_ARCH} -L https://github.com/hairyhenderson/gomplate/releases/download/${GOMPLATE_VERSION}/gomplate_linux-${GOMPLATE_ARCH} \
  && echo "${GOMPLATE_CHECKSUM}  gomplate_linux-${GOMPLATE_ARCH}" > /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum gomplate_linux-${GOMPLATE_ARCH})"; exit 1; )) \
  && install -m 0755 /tmp/gomplate_linux-${GOMPLATE_ARCH} /usr/local/bin/gomplate \
  && rm -f /tmp/* \
  ;

RUN set -x \
  && apt-get update \
  && apt-get install -y --no-install-recommends xz-utils \
  ;

# Install s6
RUN set -x \
  && S6_OVERLAY_VERSION=3.1.6.2 \
  && S6_OVERLAY_CHECKSUM_X86_64=fcf79204c1957016fc88b0ad7d98f150071483583552103d5822cbf56824cc87 \
  && S6_OVERLAY_CHECKSUM_AARCH64=3fc0bae418a0e3811b3deeadfca9cc2f0869fb2f4787ab8a53f6944067d140ee \
  && if [ "$(uname -m)" = "x86_64" ] ; then \
        S6_OVERLAY_CHECKSUM="${S6_OVERLAY_CHECKSUM_X86_64}"; \
        S6_OVERLAY_ARCH="x86_64"; \
      elif [ "$(uname -m)" = "aarch64" ]; then \
        S6_OVERLAY_CHECKSUM="${S6_OVERLAY_CHECKSUM_AARCH64}"; \
        S6_OVERLAY_ARCH="aarch64"; \
      fi \
  && curl -sSf -L https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-${S6_OVERLAY_ARCH}.tar.xz -o /tmp/s6-OVERLAY-${S6_OVERLAY_ARCH}.tar.xz \
  && echo "${S6_OVERLAY_CHECKSUM}  s6-OVERLAY-${S6_OVERLAY_ARCH}.tar.xz" > /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected $(sha256sum s6-OVERLAY-${S6_OVERLAY_ARCH}.tar.xz)"; exit 1; )) \
  && tar -C /usr/local -xJf /tmp/s6-OVERLAY-${S6_OVERLAY_ARCH}.tar.xz \
  && rm -rf /tmp/* \
  ;

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
  php8.2-apcu \
  php8.2-cli \
  php8.2-curl \
  php8.2-dom \
  php8.2-fpm \
  php8.2-gd \
  php8.2-igbinary \
  php8.2-imagick \
  php8.2-imap \
  php8.2-intl \
  php8.2-ldap \
  php8.2-mbstring \
  php8.2-memcached \
  php8.2-mongodb \
  php8.2-mysql \
  php8.2-pgsql \
  php8.2-pspell \
  php8.2-redis \
  php8.2-sqlite \
  php8.2-xmlrpc \
  php8.2-zip && \
  apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/www/html/*

# Configure
RUN \
  mkdir -p /root/.ssh && \
  echo "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php/8.2/*/php.ini && \
  touch /etc/php/8.2/mods-available/auto.ini && \
  touch /var/log/msmtp.log && \
  chown www-data:www-data /var/log/msmtp.log && \
  sed -i -r 's/^Listen.*/Listen 8000/g' /etc/apache2/ports.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php/8.2/fpm/php-fpm.conf && \
  sed -i -E 's/^;?systemd_interval.*/systemd_interval = 0/' /etc/php/8.2/fpm/php-fpm.conf && \
  mv /etc/php/8.2/fpm/pool.d/www.conf /etc/php/8.2/fpm/pool.d/www.conf_orig && \
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
  a2enconf php8.2-fpm && \
  a2enmod proxy_fcgi remoteip rewrite headers

ENV TMPDIR=/var/tmp TERM=dumb
EXPOSE 8000
ENTRYPOINT ["/entry.sh"]
CMD ["s6"]
