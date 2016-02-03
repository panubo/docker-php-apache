## PHP-Apache-CentOS 7

This is an apache and php-fpm image
Base: CentOS 7
Apache httpd: 2.4
PHP: 5.4

This image is designed to be quite configurable and as such is good for getting
started but probably not a great base if you want a highly optimised container.
This image also expects to be used behind a load balancers and as such does not
listen on port 80 but instead port 8000. Also make note of how to handle SSL
offloading to the load balancer and how this affects .htaccess rules.

## Options:

All options are optional the values shown here are the defaults that have been
set. The options listed here are also case sensitive.

### Global

```
timeout = 30
TZ = UTC
```

### HTTPD

```
httpd_mod_security_enable = (unset)

httpd_remoteipheader = X-Forwarded-For  
httpd_remoteipinternalproxy = (unset)

# Subdirectory within the git repository that contains the site root eg 'www'
httpd_root = (unset)  

# If unset the following is used
RemoteIPInternalProxy 10.0.0.0/8
RemoteIPInternalProxy 172.16.0.0/12
RemoteIPInternalProxy 192.168.0.0/16
```

### PHP/PHP-FPM

```
phpopts_ = (unset)

# phpopts Examples
phpopts_short_open_tag = off
phpopts_post_max_size = 32M
phpopts_upload_max_filezie = 32M
phpopts_memory_limit = 200M

# PHP Cache options
php_cache = (opcache|apc|none) default is opcache, none doesn't load any cache extensions.
php_apc_shm_size = 64M
php_apc_stat = 1
php_opcache_memory_consumption = 128
php_opcache_revalidate_freq = 2

# PHP Session options
php_session_save_handler = (unset)
php_session_save_path = (unset)

# PHP Session examples
php_session_save_handler = redis
php_session_save_path = "tcp://host1:6379?weight=1, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2"

php_session_save_handler = memcached
php_session_save_path = "host:11211"

# PHP-FPM options
phpfpm_pm_max_children = 3
phpfpm_pm_max_requests = 500
```

### Email/msmtp

msmtp expects a from address to be set either via this environment variable or
in the php mail() function. eg. `mail('nobody@example.com', 'the subject', 
'the message', null, '-fwebmaster@example.com');`

msmtp also need a host to send email via, it does not queue and forward mail
like postfix or exim. This should be defined via a docker link `--link
smtp:smtp`

```
msmtp_host = SMTP_PORT_25_TCP_ADDR or mail
msmtp_port = SMTP_PORT_25_TCP_PORT or 25
msmtp_from = (unset)
msmtp_user = (unset)
msmtp_pass = (unset)
```

## SSL Offloading

This container should be used behind a load balancing reverse proxy and as such
SSL should be offloaded to the load balancer. However this can cause issues
when your applications what to know if they are being served over SSL as the
local webserver can not tell. Bellow are workarounds for the two most common
issues.

If you want to redirect used from a non-ssl connection to a ssl connection with
htaccess and mod_rewrite the follow rules work both behind a ssl offloading
load balancer and when the local webserver is doing the ssl.

```
<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteCond %{HTTP:X-Forwarded-Proto} !=https
	RewriteCond %{HTTPS} !=on
	RewriteRule ^(.*) https://%{HTTP_HOST}/$1 [R=301,L]
</IfModule>
```

Some PHP application also check they are running on an SSL connection. As the
local webserver doesn't set $_SERVER['HTTPS'] correctly when behind a proxy the
following code can be used to fix the issue.

```
/* Set _SERVER['HTTPS'] correctly when behind a proxy setting HTTP_X_FORWARDED_PROTO */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}
```

## Notes

Had to use tcp socket between php-fpm and httpd as httpd 2.4.6 does not have unix socket capability this was added in httpd 2.4.9
