# Panubo PHP-Apache
#
# CentOS 7
# PHP 5.4
# Apache 2.4
#

FROM centos:7

ENV VOLTGRID_PIE=1.0.8 VOLTGRID_PIE_SHA1=c25926d4ac22ed7963f23463334bfac8e2d5e85f

# Fix the timezone to UTC
RUN ln -snf /usr/share/zoneinfo/UTC /etc/localtime && \
  echo -e "ZONE=\"UTC\"\nUTC=True" > /etc/sysconfig/clock

RUN \
  yum -q -y install epel-release && \
  yum -q -y install git tar && \
  yum -q -y install httpd python-jinja2 msmtp mod_xsendfile mod_security mod_security_crs php php-cli php-common php-dba php-fpm php-gd php-imap php-intl php-ldap php-mbstring php-mcrypt php-mysql php-pdo php-pear php-pecl-apcu php-pecl-igbinary php-pecl-imagick php-pecl-redis php-pspell php-recode php-suhosin php-xml php-xmlrpc php-pecl-memcached php-pecl-zendopcache php-soap ghostscript ImageMagick && \
  yum -q -y clean all && \
  rm -rf /tmp/* /var/cache/yum/

RUN \
  yum -q -y install php-devel gcc make && \
  pecl install apc && \
  yum -q -y history undo last && \
  yum -q -y clean all && \
  rm -rf /tmp/* /var/cache/yum/

RUN \
  mkdir /root/.ssh && \
  echo -e "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php.ini && \
  sed -i '/^sendmail_path = /csendmail_path = \/usr\/bin\/msmtp -t -i' /etc/php.ini && \
  touch /var/log/msmtp.log && \
  chown apache:apache /var/log/msmtp.log && \
  sed -i -e 's/^Listen.*/Listen 8000/g' -e '/^\s*CustomLog/d' /etc/httpd/conf/httpd.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php-fpm.conf && \
  echo 'systemd_interval=0' >> /etc/php-fpm.conf && \
  pear install HTTP_Request2 && \
  rm -rf /tmp/*

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

COPY conf.modules.d /etc/httpd/conf.modules.d
COPY conf.d /etc/httpd/conf.d
COPY php.d /etc/php.d
COPY msmtprc /etc/msmtprc
COPY php-fpm.conf /etc/php-fpm.d/www.conf
COPY voltgrid.conf /usr/local/etc/voltgrid.conf
COPY s6 /etc/s6/

# Install PHP Extras
RUN cd /tmp \
  && curl -s -L https://github.com/panubo/php-extras/archive/master.tar.gz -o master.tar.gz \
  && tar --wildcards -C /usr/share/php/ -xvf master.tar.gz --strip 1 '*.php'  \
  && rm -f /tmp/master.tar.gz

ENV TMPDIR=/var/tmp TERM=dumb
EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/voltgrid.py"]
CMD ["/usr/local/bin/s6-svscan", "/etc/s6"]
