##
##  LibreSignage makefile
##

# Note: This makefile assumes that $(ROOT) always has a trailing
# slash. (which is the case when using the makefile $(dir ...)
# function) Do not use the shell dirname command here as that WILL
# break things since it doesn't add the trailing slash to the path.
ROOT := $(dir $(realpath $(lastword $(MAKEFILE_LIST))))

SASSDEP := build/scripts/sassdep.py
SASS_IPATHS := $(ROOT) $(ROOT)src/common/css $(ROOT)/src/node_modules
SASS_FLAGS := --no-source-map $(addprefix -I,$(SASS_IPATHS))

POSTCSS_FLAGS := --config postcss.config.js --replace --no-map

PHPUNIT_API_HOST ?= http://localhost:80
PHPUNIT_CONFIG := tests/phpunit.xml
PHPUNIT_FLAGS := -c "$(PHPUNIT_CONFIG)" --testdox

# Define required dependency versions.
NPM_REQ_VER := 6.4.0
COMPOSER_REQ_VER := 1.8.0
MAKE_REQ_VER := 4.0
PANDOC_REQ_VER := 2.0
IMAGEMAGICK_REQ_VER := 6.0

# Caller supplied build settings.
VERBOSE ?= Y
NOHTMLDOCS ?= N
CONF ?= ""
TARGET ?=
PASS ?=
INITCHK_WARN ?= N

# Production JavaScript libraries.
JS_LIBS := $(filter-out \
	$(shell printf "$(ROOT)\n"|sed 's:/$$::g'), \
	$(shell npm ls --prod --parseable|sed 's/\n/ /g') \
)

# Production PHP libraries.
PHP_LIBS := $(addprefix vendor/,\
	$(shell \
		composer show | cut -d' ' -f1 \
	) composer autoload.php \
)

# Non-compiled sources.
SRC_NO_COMPILE := $(shell find src \
	\( -type f -path 'src/node_modules/*' -prune \) \
	-o \( -type f -path 'src/public/api/endpoint/*' -prune \) \
	-o \( \
		-type f ! -name '*.swp' \
		-a -type f ! -name '*.save' \
		-a -type f ! -name '.\#*' \
		-a -type f ! -name '\#*\#*' \
		-a -type f ! -name '*~' \
		-a -type f ! -name '*.js' \
		-a -type f ! -name '*.scss' \
		-a -type f ! -name '*.rst' -print \
	\) \
)

# RST sources.
SRC_RST := $(shell find src \
	\( -type f -path 'src/node_modules/*' -prune \) \
	-o -type f -name '*.rst' -print \
) README.rst CONTRIBUTING.rst AUTHORS.rst

# SCSS sources.
SRC_SCSS := $(shell find src \
	\( -type f -path 'src/node_modules/*' -prune \) \
	-o -type f -name '*.scss' -a ! -name '_*' -print \
)

# JavaScript sources.
SRC_JS := $(shell find src \
	\( -type f -path 'src/node_modules/*' -prune \) \
	-o \( -type f -name 'main.js' -print \) \
)

# API endpoint sources.
SRC_ENDPOINT := $(shell find src/public/api/endpoint \
	\( -type f -path 'src/node_modules/*' -prune \) \
	-o \( -type f -name '*.php' -print \) \
)

# Generated PNG logo paths.
GENERATED_LOGOS := $(addprefix dist/public/assets/images/logo/libresignage_,16x16.png 32x32.png 96x96.png text_466x100.png)

status = \
	if [ "`printf '$(VERBOSE)'|cut -c1|sed 's/\n//g'|\
		tr '[:upper:]' '[:lower:]'`" = "y" ]; then \
		printf "$(1): $(2) >> $(3)\n"|tr -s ' '|sed 's/^ *$///g'; \
	fi
makedir = mkdir -p $(dir $(1))

ifeq ($(NOHTMLDOCS),$(filter $(NOHTMLDOCS),y Y))
$(info [Info] Not going to generate HTML documentation.)
endif

.PHONY: initchk configure dirs server js css api \
		config libs docs htmldocs install clean \
		realclean LOC apitest
.ONESHELL:

all:: initchk server docs htmldocs js css api js_libs php_libs logo; @:
server:: $(subst src,dist,$(SRC_NO_COMPILE)); @:
js:: $(subst src,dist/public,$(SRC_JS)); @:
api:: $(subst src,dist,$(SRC_ENDPOINT)); @:
docs:: $(addprefix dist/doc/rst/,$(notdir $(SRC_RST))) dist/doc/rst/api_index.rst; @:
htmldocs:: $(addprefix dist/public/doc/html/,$(notdir $(SRC_RST:.rst=.html))); @:
css:: $(subst src,dist/public,$(SRC_SCSS:.scss=.css)); @:
js_libs:: $(subst $(ROOT)node_modules/,dist/public/libs/,$(JS_LIBS)); @:
php_libs:: $(addprefix dist/,$(PHP_LIBS)); @:
logo:: $(GENERATED_LOGOS); @:

# Copy over non-compiled, non-PHP sources.
$(filter-out %.php,$(subst src,dist,$(SRC_NO_COMPILE))):: dist%: src%
	@:
	set -e
	$(call status,cp,$<,$@)
	$(call makedir,$@)
	cp -p $< $@

# Copy and prepare PHP files and check the syntax.
$(filter %.php,$(subst src,dist,$(SRC_NO_COMPILE))):: dist%: src%
	@:
	set -e
	$(call status,cp,$<,$@)
	$(call makedir,$@)
	cp -p $< $@
	$(call status,prep.sh,<inplace>,$@)
	./build/scripts/prep.sh $(CONF) $@
	php -l $@ > /dev/null

# Copy API endpoint PHP files and generate corresponding docs.
$(subst src,dist,$(SRC_ENDPOINT)):: dist%: src%
	@:
	set -e
	php -l $< > /dev/null

	$(call status,cp,$<,$@)
	$(call makedir,$@)
	cp -p $< $@

	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		# Generate reStructuredText documentation.
		mkdir -p dist/doc/rst
		mkdir -p dist/public/doc/html
		$(call status,\
			gendoc.sh,\
			<generated>,\
			dist/doc/rst/$(notdir $(@:.php=.rst))\
		)
		./build/scripts/gendoc.sh $(CONF) $@ dist/doc/rst/

		# Compile rst docs into HTML.
		$(call status,\
			pandoc,\
			dist/doc/rst/$(notdir $(@:.php=.rst)),\
			dist/public/doc/html/$(notdir $(@:.php=.html))\
		)
		pandoc -f rst -t html \
			-o dist/public/doc/html/$(notdir $(@:.php=.html)) \
			dist/doc/rst/$(notdir $(@:.php=.rst))
	fi

# Generate the API endpoint documentation index.
dist/doc/rst/api_index.rst:: $(SRC_ENDPOINT)
	@:
	set -e
	$(call status,makefile,<generated>,$@)
	$(call makedir,$@)

	. build/scripts/conf.sh
	printf "LibreSignage API documentation (Ver: $$API_VER)\n" > $@
	printf '###############################################\n\n' >> $@

	printf "This document was automatically generated by the" >> $@
	printf "LibreSignage build system on `date`.\n\n" >> $@

	for f in $(SRC_ENDPOINT); do
		printf "\``basename $$f` </doc?doc=`basename -s '.php' $$f`>\`_\n\n" >> $@
	done

	# Compile into HTML.
	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		$(call status,pandoc,$(subst /rst/,/html/,$($:.rst=.html)),$@)
		$(call makedir,$(subst /rst/,/html/,$@))
		pandoc -f rst -t html -o $(subst /rst/,/html/,$(@:.rst=.html)) $@
	fi

# Copy over RST sources. Try to find prerequisites from
# 'src/doc/rst/' first and then fall back to './'.
dist/doc/rst/%.rst:: src/doc/rst/%.rst
	@:
	set -e
	$(call status,cp,$<,$@)
	$(call makedir,$@)
	cp -p $< $@

dist/doc/rst/%.rst:: %.rst
	@:
	set -e
	$(call status,cp,$<,$@)
	$(call makedir,$@)
	cp -p $< $@

# Compile RST sources into HTML. Try to find prerequisites
# from 'src/doc/rst/' first and then fall back to './'.
dist/public/doc/html/%.html:: src/doc/rst/%.rst
	@:
	set -e
	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		$(call status,pandoc,$<,$@)
		$(call makedir,$@)
		pandoc -o $@ -f rst -t html $<
	fi

dist/public/doc/html/%.html:: %.rst
	@:
	set -e
	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		$(call status,pandoc,$<,$@)
		$(call makedir,$@)
		pandoc -o $@ -f rst -t html $<
	fi

# Generate JavaScript deps.
dep/%/main.js.dep: src/%/main.js
	@:
	set -e
	$(call status,deps-js,$<,$@)
	$(call makedir,$@)

	TARGET="$(subst src,dist/public,$(<))"
	SRC="$(<)"
	DEPS=`npx browserify --list $$SRC | tr '\n' ' ' | sed 's:$(ROOT)::g'`

	# Printf dependency makefile contents.
	printf "$$TARGET:: $$DEPS\n" > $@
	printf "\t@:\n" >> $@
	printf "\t\$$(call status,compile-js,$$SRC,$$TARGET)\n" >> $@
	printf "\t\$$(call makedir,$$TARGET)\n" >> $@
	printf "\tnpx browserify $$SRC -o $$TARGET\n" >> $@

# Generate SCSS deps.
dep/%.scss.dep: src/%.scss
	@:
	set -e
	# Don't create deps for partials.
	if [ ! "`basename '$(<)' | cut -c 1`" = "_" ]; then
		$(call status,deps-scss,$<,$@)
		$(call makedir,$@)

		TARGET="$(subst src,dist/public,$(<:.scss=.css))"
		SRC="$(<)"
		DEPS=`./$(SASSDEP) -l $$SRC $(SASS_IPATHS)|sed 's:$(ROOT)::g'`

		# Printf dependency makefile contents.
		printf "$$TARGET:: $$SRC $$DEPS\n" > $@
		printf "\t@:\n" >> $@
		printf "\t\$$(call status,compile-scss,$$SRC,$$TARGET)\n" >> $@
		printf "\t\$$(call makedir,$$SRC)\n" >> $@

		printf "\tnpx sass $(SASS_FLAGS) $$SRC $$TARGET\n" >> $@
		printf "\tnpx postcss $$TARGET $(POSTCSS_FLAGS)\n" >> $@
	fi

# Copy production node modules to 'dist/public/libs/'.
dist/public/libs/%:: node_modules/%
	@:
	set -e
	mkdir -p $@
	$(call status,cp,$<,$@)
	cp -Rp $</* $@

# Copy PHP libraries to dist/vendors.
dist/vendor/%:: vendor/%
	@:
	set -e
	$(call status,cp,$<,$@)
	mkdir -p $$(dirname $@)
	cp -Rp $< $@

# Convert the LibreSignage SVG logos to PNG logos of various sizes.
.SECONDEXPANSION:
$(GENERATED_LOGOS): dist/%.png: src/$$(shell printf '$$*\n' | rev | cut -f 2- -d '_' | rev).svg
	@:
	set -e
	. build/scripts/convert_images.sh
	SRC_DIR=`dirname $(@) | sed 's:dist:src:g'`
	DEST_DIR=`dirname $(@)`
	NAME=`basename $(lastword $^)`
	SIZE=`printf "$(@)\n" | rev | cut -f 2 -d '.' | cut -f 1 -d '_' | rev`
	svg_to_png "$$SRC_DIR" "$$DEST_DIR" "$$NAME" "$$SIZE"

##
##  PHONY targets
##

install:
	@:
	set -e
	./build/scripts/install.sh $(CONF)

configure:
	@:
	set -e
	if [ -z "$(TARGET)" ]; then
		printf "[Error] Specify a target using 'TARGET=[target]'.\n"
		exit 1
	fi
	target="--target $(TARGET)"

	./build/scripts/configure_build.sh $$target $(PASS)
	./build/scripts/configure_system.sh

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
	rm composer.lock
	$(call status,rm,server,none)
	rm -rf server

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
			-path "./dist/*" -o \
			-path "./node_modules/*" \
		\) -prune \
		-o -name ".#*" \
		-o -name "*.py" -print \
		-o -name "*.php" -print \
		-o -name "*.js" -print \
		-o -name "*.html" -print \
		-o -name "*.css" -print \
		-o -name "*.scss" -print \
		-o -name "*.sh" -print \
		-o -name "Dockerfile" -print \
		-o -name "makefile" -print \
		-o ! -name 'package-lock.json' -name "*.json" -print \
		-o -name "*.py" -print`

LOD:
	@:
	set -e
	printf '[Info] Make sure your 'dist/' is up to date!\n'
	printf '[Info] Lines Of Documentation: \n'
	wc -l `find dist -type f -name '*.rst'`

apitest:
	@:
	set -e
	printf '[Info] Running API integration tests...\n'
	export PHPUNIT_API_HOST="$(PHPUNIT_API_HOST)"
	vendor/bin/phpunit $(PHPUNIT_FLAGS) --testsuite "API"

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
	./build/scripts/dep_checks/imagemagick_version.sh $(IMAGEMAGICK_REQ_VER)
	tmp=$$(expr $$tmp + $$?)


	if [ "$(INITCHK_WARN)" = "N" ] || [ "$(INITCHK_WARN)" = "n" ]; then
		if [ "$$tmp" -ne "0" ]; then
			echo "[Info] To continue anyway, pass INITCHK_WARN=Y to make."
			exit 1
		fi
	else
		if [ "$$tmp" -ne "0" ]; then
			echo "[Warning] Continuing anyway. You're on your own."
		fi
	fi

# Include the dependency makefiles from dep/. If the files don't
# exist, they are built by running the required targets.
ifeq (,$(filter LOC LOD clean realclean configure initchk,$(MAKECMDGOALS)))
include $(subst src,dep,$(SRC_JS:.js=.js.dep))
include $(subst src,dep,$(SRC_SCSS:.scss=.scss.dep))
endif
