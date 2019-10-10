# Panubo PHP-Apache
#
# CentOS 7
# PHP 5.4
# Apache 2.4
#

FROM centos:7

ENV S6_RELEASE=1.20.0 S6_VERSION=2.5.1.0 S6_SHA1=b798972cbf46e28f1c5d238f6703aba6edded57e
ENV VOLTGRID_PIE=1.0.8 VOLTGRID_PIE_SHA1=c25926d4ac22ed7963f23463334bfac8e2d5e85f

# Fix the timezone to UTC
RUN ln -snf /usr/share/zoneinfo/UTC /etc/localtime && \
  echo -e "ZONE=\"UTC\"\nUTC=True" > /etc/sysconfig/clock

RUN \
  yum -q -y install epel-release && \
  yum -q -y install git tar && \
  yum -q -y install httpd python-jinja2 msmtp mod_xsendfile mod_security mod_security_crs php php-cli php-common php-dba php-fpm php-gd php-imap php-intl php-ldap php-mbstring php-mcrypt php-mysql php-pdo php-pear php-pecl-apcu php-pecl-igbinary php-pecl-imagick php-pecl-redis php-pspell php-recode php-suhosin php-xml php-xmlrpc php-pecl-memcached php-pecl-zendopcache php-soap php-pecl-mongo ghostscript ImageMagick && \
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
RUN DIR=$(mktemp -d) && cd ${DIR} && \
    curl -s -L https://github.com/just-containers/skaware/releases/download/v${S6_RELEASE}/s6-${S6_VERSION}-linux-amd64-bin.tar.gz -o s6.tar.gz && \
    echo "${S6_SHA1} s6.tar.gz" | sha1sum -c - && \
    tar -xzf s6.tar.gz -C /usr/local/ && \
    rm -rf ${DIR}

# ImageTragick CVE-2016-3714
COPY policy.xml /etc/ImageMagick/policy.xml
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
