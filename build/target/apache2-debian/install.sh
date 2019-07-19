#!/bin/sh

#
# Installation script for the apache2-debian target.
#

set -e

. build/scripts/conf.sh
. build/scripts/ldconf.sh
. build/scripts/args.sh

#
# Setup and parse arguments.
#

script_help() {
	echo 'Usage:'
	echo '  make install'
	echo '  ./build/target/apache2-debian/install.sh [OPTION]...'
	echo ''
	echo 'Install LibreSignage.'
	echo ''
	echo 'Options:'
	echo '  OPTION (DEFAULT VALUE) ........... DESCRIPTION'
	echo "  --no-preserve-data ............... Don't preserve existing instance data."
	echo '  --config=FILE (last generated) ... Use a specific build config file.'
	echo '  --help ........................... Print this message and exit.'
}

NO_PRESERVE_DATA=0
TMP_DATA_DIR='/tmp/libresignage/'
APACHE_SITES_DIR='/etc/apache2/sites-available';
BUILD_CONFIG=''

while [ $# -gt 0 ]; do
	case "$1" in
		--no-preserve-data)
			NO_PRESERVE_DATA=1
			;;
		--config=*)
			BUILD_CONFIG="$(get_arg_value "$1")"
			;;
		--help)
			script_help
			exit 0
			;;
		*)
			echo "[Error] Unknown option '$1'."
			echo ''
			script_help
			exit 1
			;;
	esac

	set +e
	shift > /dev/null 2>&1
	if [ ! "$?" = 0 ]; then
		set -e
		break
	fi
	set -e
done

load_build_config "$BUILD_CONFIG"
APPROOT="${CONF_INSTALL_DIR:?}/${CONF_NAME:?}";

#
# Install LibreSignage.
#

if [ ! -d "${APACHE_SITES_DIR:?}" ]; then
	echo "[Error] Directory '${APACHE_SITES_DIR:?}' doesn't exist." > /dev/stderr
	echo '[Error] Is apache2 installed?' > /dev/stderr
	exit 1
fi

if [ ! -d "${CONF_DIR:?}" ]; then
	echo "[Error] Directory '${CONF_DIR:?}' doesn't exist." > /dev/stderr
	echo "[Error] Have you run 'make configure'?" > /dev/stderr
	exit 1
fi

# Remove current $APPROOT and store $APPROOT/data in $TMP_DATA_DIR if needed.
if [ -d "${APPROOT:?}" ]; then
	if [ "$NO_PRESERVE_DATA" = "0" ]; then
		echo "[Info] Copy existing data into ${TMP_DATA_DIR:?}."
		mkdir -p "${TMP_DATA_DIR:?}";

		set +e
		cp -Rp "${APPROOT:?}"/data/* "${TMP_DATA_DIR:?}"/.;
		if [ ! "$?" = "0" ]; then
			echo '[Info] No existing data directory exists.'
			echo '[Info] Will use --no-presere-data implicitly.'
			NO_PRESERVE_DATA=1
		fi
		set -e
	fi
	echo "[Info] Remove old files from ${APPROOT:?}."
	rm -rf "${APPROOT:?}";
else
	mkdir -p "${APPROOT:?}";
fi

# Copy new files to $APPROOT.
echo "[Info] Copy new files to ${APPROOT:?}."
cp -Rp "${DIST_DIR:?}"/ "${APPROOT:?}"/
cp "${CONF_DIR:?}"/libresignage/conf/* "${APPROOT:?}"/config/conf/.

# Replace new data with old data if needed.
if [ "$NO_PRESERVE_DATA" = "0" ]; then
	echo "[Info] Copy old data back to ${APPROOT:?}."
	rm -rf "${APPROOT:?}"/data/*
	cp -Rp "${TMP_DATA_DIR:?}"/* "${APPROOT:?}"/data/.
	rm -rf "${TMP_DATA_DIR:?}";
fi

# Set default file permissions.
echo '[Info] Set file permissions.'
chown -R "${OWNER:?}:${OWNER:?}" "${APPROOT:?}"
chown -R "${OWNER:?}:www-data" "${APPROOT:?}/data";
find "${APPROOT:?}" -type d -print0 | xargs -0 chmod 755
find "${APPROOT:?}/data" -type d -print0 | xargs -0 chmod 775
find "${APPROOT:?}" -type f -print0 | xargs -0 chmod 644
find "${APPROOT:?}/data" -type f -print0 | xargs -0 chmod 664

# Create $LOG_DIR.
echo "[Info] Create log directory in ${LOG_DIR:?}."
mkdir -p "${LOG_DIR:?}"
chown root:www-data "${LOG_DIR:?}"
chmod 775 "${LOG_DIR:?}"

# Copy apache2 vhost config file to $APACHE_SITES_DIR.
echo "[Info] Copy virtual host config to ${APACHE_SITES_DIR:?}."
cp "${CONF_DIR:?}/apache2/${CONF_NAME:?}.conf" "${APACHE_SITES_DIR:?}"/.

echo '[Info] Enable virtual host.'
a2ensite --quiet "${CONF_NAME:?}.conf"
echo '[Info] Enable mod_rewrite.'
a2enmod	--quiet	rewrite
echo '[Info] Restart apache2.'
apache2ctl restart
