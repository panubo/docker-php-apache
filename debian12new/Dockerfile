# Panubo PHP-Apache
#
# Debian 12
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

# Install s6
RUN set -x \
  && S6_VERSION=2.11.0.0 \
  && EXECLINE_VERSION=2.8.1.0 \
  && SKAWARE_RELEASE=2.0.7 \
  && S6_CHECKSUM_X86_64=fcf79204c1957016fc88b0ad7d98f150071483583552103d5822cbf56824cc87 \
  && S6_CHECKSUM_AARCH64=64151e136f887c6c2c7df69e3100573c318ec7400296680cc698bc7b0ca36943 \
  && EXECLINE_CHECKSUM_X86_64=b216cfc4db928729d950df5a354aa34bc529e8250b55ab0de700193693dea682 \
  && EXECLINE_CHECKSUM_AARCH64=8cb1d5c2d44cb94990d63023db48f7d3cd71ead10cbb19c05b99dbd528af5748 \
  && if [ "$(uname -m)" = "x86_64" ] ; then \
        S6_CHECKSUM="${S6_CHECKSUM_X86_64}"; \
        EXECLINE_CHECKSUM="${EXECLINE_CHECKSUM_X86_64}"; \
        SKAWARE_ARCH="amd64"; \
      elif [ "$(uname -m)" = "aarch64" ]; then \
        S6_CHECKSUM="${S6_CHECKSUM_AARCH64}"; \
        EXECLINE_CHECKSUM="${EXECLINE_CHECKSUM_AARCH64}"; \
        SKAWARE_ARCH="aarch64"; \
      fi \
  && curl -sSf -L -o /tmp/s6-${S6_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz https://github.com/just-containers/skaware/releases/download/v${SKAWARE_RELEASE}/s6-${S6_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz \
  && curl -sSf -L -o /tmp/execline-${EXECLINE_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz https://github.com/just-containers/skaware/releases/download/v${SKAWARE_RELEASE}/execline-${EXECLINE_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz \
  && echo "${S6_CHECKSUM}  s6-${S6_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz" > /tmp/SHA256SUM \
  && echo "${EXECLINE_CHECKSUM}  execline-${EXECLINE_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz" >> /tmp/SHA256SUM \
  && ( cd /tmp; sha256sum -c SHA256SUM || ( echo "Expected S6: $(sha256sum s6-${S6_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz) Execline: $(sha256sum execline-${EXECLINE_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz)"; exit 1; )) \
  && tar -C /usr/local -zxf /tmp/s6-${S6_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz \
  && tar -C /usr/local -zxf /tmp/execline-${EXECLINE_VERSION}-linux-${SKAWARE_ARCH}-bin.tar.gz \
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
