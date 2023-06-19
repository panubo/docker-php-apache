# Panubo PHP-Apache
#
# Debian 9
# PHP 7.0
# Apache 2.4
# Mongo support
#

FROM debian:9

ENV VOLTGRID_PIE=1.0.8 VOLTGRID_PIE_SHA1=c25926d4ac22ed7963f23463334bfac8e2d5e85f

# Change the www-data user to uid and gid 48 to match other containers
RUN \
  usermod -u 48 www-data && \
  groupmod -g 48 www-data

# Install main packages
RUN \
  export DEBIAN_FRONTEND=noninteractive && \
  apt-get update && \
  apt-get install --no-install-recommends --no-install-suggests -y wget curl ca-certificates git gnupg openssh-client msmtp-mta python-jinja2 apache2 libapache2-mod-xsendfile imagemagick ghostscript php7.0-fpm php7.0-cli php7.0-curl php7.0-apcu php7.0-gd php7.0-imap php7.0-intl php7.0-ldap php7.0-mcrypt php7.0-mongodb php7.0-mysql php7.0-pgsql php7.0-sqlite php7.0-redis php7.0-igbinary php7.0-imagick php7.0-pspell php7.0-recode php7.0-xmlrpc php7.0-memcached php-http-request2 && \
  apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/www/html/*

# Add Volt Grid .py / .conf
RUN DIR=$(mktemp -d) && cd ${DIR} && \
    curl -s -L https://github.com/voltgrid/voltgrid-pie/archive/v${VOLTGRID_PIE}.tar.gz -o voltgrid-pie.tar.gz && \
    sha1sum voltgrid-pie.tar.gz && \
    echo "${VOLTGRID_PIE_SHA1} voltgrid-pie.tar.gz" | sha1sum -c - && \
    tar -C /usr/local/bin --strip-components 1 -zxf voltgrid-pie.tar.gz voltgrid-pie-${VOLTGRID_PIE}/voltgrid.py && \
    rm -rf ${DIR} && \
    echo '{"user":{"uid":0,"gid":0}}' > /usr/local/etc/voltgrid.conf

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

# Configure
RUN \
  mkdir /root/.ssh && \
  echo "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php/7.0/*/php.ini && \
  touch /var/log/msmtp.log && \
  chown www-data:www-data /var/log/msmtp.log && \
  sed -i -r 's/^Listen.*/Listen 8000/g' /etc/apache2/ports.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php/7.0/fpm/php-fpm.conf && \
  sed -i -E 's/^;?systemd_interval.*/systemd_interval = 0/' /etc/php/7.0/fpm/php-fpm.conf && \
  mv /etc/php/7.0/fpm/pool.d/www.conf /etc/php/7.0/fpm/pool.d/www.conf_orig

COPY php-fpm.conf /etc/apache2/conf-available/php7.0-fpm.conf
COPY php.d /etc/php/7.0/mods-available
COPY msmtprc /etc/msmtprc
COPY www.conf /etc/php/7.0/fpm/pool.d/www.conf
COPY voltgrid.conf /usr/local/etc/voltgrid.conf
COPY s6 /etc/s6/

# Install PHP Extras
RUN cd /tmp \
  && wget https://github.com/panubo/php-extras/archive/master.tar.gz \
  && tar --wildcards -C /usr/share/php/ -xvf master.tar.gz --strip 1 '*.php'  \
  && rm -f /tmp/master.tar.gz

RUN \
  phpenmod session mongodb && \
  a2dissite 000-default && \
  a2disconf security other-vhosts-access-log && \
  a2enconf php7.0-fpm && \
  a2enmod proxy_fcgi remoteip rewrite headers

ENV TMPDIR=/var/tmp TERM=dumb
EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/voltgrid.py"]
CMD ["/usr/local/bin/s6-svscan", "/etc/s6"]
