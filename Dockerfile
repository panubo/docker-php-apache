# Panubo
#
# CentOS 7
# PHP 5.4
# Apache 2.4
#

FROM centos:7
MAINTAINER Tim Robinson <tim@panubo.com>

ENV VOLTGRID_PIE=1.0.4

# Fix the timezone
RUN cp -a /usr/share/zoneinfo/UTC /etc/localtime; \
  echo -e "ZONE=\"UTC\"\nUTC=True" > /etc/sysconfig/clock

RUN \
  yum -q -y install epel-release && \
  yum -q -y install tar httpd python-jinja2 git msmtp mod_xsendfile mod_security mod_security_crs php php-cli php-common php-dba php-fpm php-gd php-imap php-intl php-ldap php-mbstring php-mcrypt php-mysql php-pdo php-pear php-pecl-apcu php-pecl-igbinary php-pecl-imagick php-pecl-redis php-pspell php-recode php-suhosin php-xml php-xmlrpc php-pecl-memcached php-pecl-zendopcache php-soap ghostscript ImageMagick && \
  yum -q -y clean all

EXPOSE 8000

RUN \
  yum -q -y install php-devel gcc make && \
  pecl install apc && \
  yum -q -y history undo last && \
  yum -q -y clean all

RUN \
  mkdir /root/.ssh && \
  echo -e "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config && \
  sed -i -e '/^session.save_/ s/^/;/' /etc/php.ini && \
  sed -i '/^sendmail_path = /csendmail_path = \/usr\/bin\/msmtp -t -i' /etc/php.ini && \
  touch /var/log/msmtp.log && \
  chown apache:apache /var/log/msmtp.log && \
  sed -i -e 's/^Listen.*/Listen 8000/g' -e '/^\s*CustomLog/d' /etc/httpd/conf/httpd.conf && \
  sed -i 's/^error_log.*/error_log = \/dev\/stderr/' /etc/php-fpm.conf && \
  echo 'systemd_interval=0' >> /etc/php-fpm.conf

RUN curl -L https://github.com/voltgrid/voltgrid-pie/archive/v${VOLTGRID_PIE}.tar.gz | tar -C /usr/local/bin --strip-components 1 -zxf - voltgrid-pie-${VOLTGRID_PIE}/voltgrid.py && \
  curl -L https://github.com/just-containers/skaware/releases/download/v1.10.0/s6-2.1.3.0-linux-amd64-bin.tar.gz | tar -C / -zxf - && \
  pear install HTTP_Request2

COPY conf.modules.d /etc/httpd/conf.modules.d
COPY conf.d /etc/httpd/conf.d
COPY php.d /etc/php.d
COPY msmtprc /etc/msmtprc
COPY php-fpm.conf /etc/php-fpm.d/www.conf
COPY voltgrid.conf /usr/local/etc/voltgrid.conf
COPY s6 /etc/s6/
COPY php-extras /usr/share/php/

ENV TMPDIR /var/tmp
ENTRYPOINT ["/usr/local/bin/voltgrid.py"]
CMD ["/usr/bin/s6-svscan","/etc/s6"]
