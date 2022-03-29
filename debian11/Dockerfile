# Panubo PHP-Apache
#
# Debian bullseye
# PHP 7.4
# Apache 2.4
# Mongo support
#

FROM debian:bullseye

ENV S6_RELEASE=1.20.0 S6_VERSION=2.5.1.0 S6_SHA1=b798972cbf46e28f1c5d238f6703aba6edded57e
ENV VOLTGRID_PIE=1.0.8 VOLTGRID_PIE_SHA1=c25926d4ac22ed7963f23463334bfac8e2d5e85f

# Change the www-data use to uid and gid 48 to match other containers
RUN \
  usermod -u 48 www-data && \
  groupmod -g 48 www-data

# Install main packages
RUN \
  export DEBIAN_FRONTEND=noninteractive && \
  apt-get update && \
  apt-get install --no-install-recommends --no-install-suggests -y wget curl ca-certificates git gnupg openssh-client msmtp-mta python python-jinja2 apache2 libapache2-mod-xsendfile imagemagick ghostscript \
  php7.4-apcu \
  php7.4-cli \
  php7.4-curl \
  php7.4-dom \
  php7.4-fpm \
  php7.4-gd \
  php7.4-igbinary \
  php7.4-imagick \
  php7.4-imap \
  php7.4-intl \
  php7.4-ldap \
  php7.4-mbstring \
  php7.4-memcached \
  php7.4-mongodb \
  php7.4-mysql \
  php7.4-pgsql \
  php7.4-pspell \
  php7.4-redis \
  php7.4-sqlite \
  php7.4-xmlrpc \
  php7.4-zip && \
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
RUN DIR=$(mktemp -d) && cd ${DIR} && \
    curl -s -L https://github.com/just-containers/skaware/releases/download/v${S6_RELEASE}/s6-${S6_VERSION}-linux-amd64-bin.tar.gz -o s6.tar.gz && \
    echo "${S6_SHA1} s6.tar.gz" | sha1sum -c - && \
    tar -xzf s6.tar.gz -C /usr/local/ && \
    rm -rf ${DIR}

# Configure
RUN \
  mkdir /root/.ssh && \
  echo "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php/7.4/*/php.ini && \
  touch /etc/php/7.4/mods-available/auto.ini && \
  touch /var/log/msmtp.log && \
  chown www-data:www-data /var/log/msmtp.log && \
  sed -i -r 's/^Listen.*/Listen 8000/g' /etc/apache2/ports.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php/7.4/fpm/php-fpm.conf && \
  sed -i -E 's/^;?systemd_interval.*/systemd_interval = 0/' /etc/php/7.4/fpm/php-fpm.conf && \
  mv /etc/php/7.4/fpm/pool.d/www.conf /etc/php/7.4/fpm/pool.d/www.conf_orig && \
  mkdir -p /var/log/php-fpm

COPY php-fpm.conf /etc/apache2/conf-available/php7.4-fpm.conf
COPY php.d /etc/php/7.4/mods-available
COPY msmtprc /etc/msmtprc
COPY www.conf /etc/php/7.4/fpm/pool.d/www.conf
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
  phpenmod auto && \
  a2enconf php7.4-fpm && \
  a2enmod proxy_fcgi remoteip rewrite headers

ENV TMPDIR=/var/tmp TERM=dumb
EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/voltgrid.py"]
CMD ["/usr/local/bin/s6-svscan", "/etc/s6"]
