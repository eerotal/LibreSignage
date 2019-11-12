#
# LibreSignage make entry point.
#

PHPUNIT_API_HOST ?= http://localhost:80
PHPUNIT_CONFIG := tests/phpunit.xml
PHPUNIT_FLAGS := -c "$(PHPUNIT_CONFIG)" --testdox --color=auto

# Define required dependency versions.
NPM_REQ_VER := 6.4.0
COMPOSER_REQ_VER := 1.8.0
MAKE_REQ_VER := 4.0
PANDOC_REQ_VER := 2.0
DOXYGEN_REQ_VER := 100.8.0
RSVG_REQ_VER := 2.40.0

# Caller supplied settings.
CONF ?= ""
TARGET ?=
PASS ?=

.PHONY: \
	configure-build \
	configure-system \
	configure \
	clean \
	realclean \
	LOC \
	test-api \
	doxygen-docs \
	build \
	php-dev-autoload \
	php-prod-autoload

.ONESHELL:

all: initchk php-prod-autoload
	+make -f makefile.build
	+make -f makefile.post

# Perform some initialization checks.
initchk:
	@:
	set +e
	tmp=0
	./build/scripts/check_shell.sh
	tmp=$$(expr $$tmp + $$?)
	./build/scripts/dep_checks/npm_version.sh $(NPM_REQ_VER)
	tmp=$$(expr $$tmp + $$?)
	./build/scripts/dep_checks/composer_version.sh $(COMPOSER_REQ_VER)
	tmp=$$(expr $$tmp + $$?)
	./build/scripts/dep_checks/make_version.sh $(MAKE_REQ_VER)
	tmp=$$(expr $$tmp + $$?)
	./build/scripts/dep_checks/pandoc_version.sh $(PANDOC_REQ_VER)
	tmp=$$(expr $$tmp + $$?)
	./build/scripts/dep_checks/rsvg_version.sh $(RSVG_REQ_VER)
	tmp=$$(expr $$tmp + $$?)

	$(call initchk_warn,$$tmp)

# Install deps from Composer.
vendor:
	@:
	set -e
	composer install

# Install deps from NPM.
node_modules:
	@:
	set -e
	npm install

# Dump production autoload files.
php-prod-autoload: vendor
	@:
	echo "[Info] Dump production autoload."
	composer dump-autoload --no-ansi --no-dev --optimize

# Dump development autoload files.
php-dev-autoload: vendor
	@:
	echo "[Info] Dump development autoload."
	composer dump-autoload --no-ansi

# Install deps and configure.
configure: vendor node_modules configure-build configure-system

# Create build configuration.
configure-build:
	@:
	set -e
	if [ -z "$(TARGET)" ]; then
		printf "[Error] Please specify a build target using 'TARGET=[target]'.\n" > /dev/stderr
		exit 1
	fi

	./build/scripts/configure_build.sh --target="$(TARGET)" --pass $(PASS)

# Create system configuration.
configure-system:
	@:
	set -e
	./build/scripts/configure_system.sh --config="$(CONF)"

# Install LibreSignage.
install:
	@:
	set -e
	./build/scripts/install.sh --config="$(CONF)" --pass $(PASS)

# Clean the source tree.
clean:
	@:
	set -e
	$(call status,rm,dist,none)
	rm -rf dist
	$(call status,rm,dep,none)
	rm -rf dep
	$(call status,rm,*.log,none)
	rm -f *.log

	for f in '__pycache__' '.sass-cache' '.mypy_cache'; do
		TMP="`find . -type d -name $$f -printf '%p '`"
		if [ ! -z "$$TMP" ]; then
			$(call status,rm,$$TMP,none)
			rm -rf $$TMP
		fi
	done

# Realclean the source tree.
realclean: clean
	@:
	set -e
	$(call status,rm,build/*.conf,none);
	rm -f build/*.conf
	$(call status,rm,build/link,none);
	rm -rf build/link
	$(call status,rm,node_modules,none);
	rm -rf node_modules
	$(call status,rm,package-lock.json,none);
	rm -f package-lock.json
	$(call status,rm,vendor,none)
	rm -rf vendor
	$(call status,rm,composer.lock,none)
	rm -f composer.lock
	$(call status,rm,server,none)
	rm -rf server
	$(call status,rm,.phpunit.result.cache,none)
	rm -f .phpunit.result.cache

	# Remove temporary nano files.
	TMP="`find . \
		\( -type d -path './node_modules/*' -prune \) \
		-o \( \
			-type f -name '*.swp' -printf '%p ' \
			-o  -type f -name '*.save' -printf '%p ' \
		\)`"
	if [ ! -z "$$TMP" ]; then
		$(call status,rm,$$TMP,none)
		rm -f $$TMP
	fi

	# Remove temporary emacs files.
	TMP="`find . \
		\( -type d -path './node_modules/*' -prune \) \
		-o \( \
			 -type f -name '\#*\#*' -printf '%p ' \
			-o -type f -name '*~' -printf '%p ' \
		\)`"
	if [ ! -z "$$TMP" ]; then
		$(call status,rm,$$TMP,none)
		rm -f $$TMP
	fi


# Count the lines of code in LibreSignage.
LOC:
	@:
	set -e
	printf 'Lines Of Code: \n'
	wc -l `find . \
		\( \
			-path "./dist/*" \
			-o -path "./node_modules/*" \
			-o -path "./vendor/*" \
			-o -path "./doxygen_docs/*" \
		\) -prune \
		-o -name ".#*" -printf '' \
		-o -name 'package-lock.json' -printf '' \
		-o -name 'composer.lock.json' -printf '' \
		-o -name "Dockerfile" -print \
		-o -name "makefile" -print \
		-o -name "*.py" -print \
		-o -name "*.php" -print \
		-o -name "*.js" -print \
		-o -name "*.html" -print \
		-o -name "*.css" -print \
		-o -name "*.scss" -print \
		-o -name "*.sh" -print \
		-o -name "*.json" -print`

# Run API integration tests.
test-api: php-dev-autoload
	@:
	set -e

	printf '[Info] Running API integration tests...\n'

	if [ ! -d 'dist/' ]; then
		echo "[Error] 'dist'/ doesn't exist. Did you compile first?"
		exit 1
	fi

	sh tests/setup.sh "API"

	export PHPUNIT_API_HOST="$(PHPUNIT_API_HOST)"
	vendor/bin/phpunit $(PHPUNIT_FLAGS) $(PASS) --testsuite "API"

	sh tests/cleanup.sh "API"

# Generate doxygen docs.
doxygen-docs:
	@:
	set -e

	./build/scripts/dep_checks/doxygen_version.sh $(DOXYGEN_REQ_VER)
	$(call initchk_warn,$$?)
	doxygen Doxyfile

include makefile.common
