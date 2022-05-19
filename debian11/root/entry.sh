#!/usr/bin/env bash

set -e

source /panubo-functions.sh

TEMPLATE_FILES[0]=/etc/php/7.4/mods-available/apcu.ini.tmpl
TEMPLATE_FILES[1]=/etc/php/7.4/mods-available/auto.ini.tmpl
TEMPLATE_FILES[2]=/etc/php/7.4/mods-available/opcache.ini.tmpl
TEMPLATE_FILES[3]=/etc/php/7.4/mods-available/session.ini.tmpl
TEMPLATE_FILES[4]=/etc/apache2/conf-available/php7.4-fpm.conf.tmpl
TEMPLATE_FILES[5]=/etc/php/7.4/fpm/pool.d/www.conf.tmpl
TEMPLATE_FILES[6]=/etc/msmtprc.tmpl

# Mount data mounts (specifying an alternate mount point uid/gid)
MOUNTFILE_MOUNT_UID=48
MOUNTFILE_MOUNT_GID=48
run_mountfile

# Template files
render_templates "${TEMPLATE_FILES[@]}"

# Exec Procfile command, or if not found in Procfile execute the command passed to the entrypoint
exec_procfile "${1}" || exec "$@"
