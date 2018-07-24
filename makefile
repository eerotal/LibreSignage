##
##  LibreSignage makefile
##

NPMBIN=$(shell ./build/scripts/npmbin.sh)
ROOT=$(dir $(realpath $(lastword $(MAKEFILE_LIST))))

# Source file lists.
SRC_NORMAL := $(shell find src 							\
	\( -type f -path 'src/node_modules/*' -prune \)		\
	-o \( -type f -path 'src/api/endpoint/*' -prune \) 	\
	-o \(												\
		-type f ! -name '*.js'							\
		-a -type f ! -name 'config.php' -print 			\
	\)													\
)
SRC_JS := $(shell find src 							\
	\( -type f -path 'src/node_modules/*' -prune \)	\
	-o \( -type f -name 'main.js' -print \)			\
)
SRC_ENDPOINT := $(shell find src/api/endpoint 		\
	\( -type f -path 'src/node_modules/*' -prune \)	\
	-o \( -type f -name '*.php' -print \)			\
)

FILES := $(shell find src							\
	\( -type f -path 'src/node_modules/*' -prune \)	\
	-o \( -type f -print \)							\
)
DIRS := $(shell find src 							\
	\( -type d -path 'src/node_modules' -prune \)	\
	-o \( -type d -print \)							\
)

# Documentation sources.
HTML_DOCS := $(shell find src -type f -name '*.rst')
HTML_DOCS := $(addprefix dist/doc/html/,$(notdir $(HTML_DOCS)))
HTML_DOCS := $(HTML_DOCS:.rst=.html) dist/doc/html/README.html

ifndef INST
INST := ""
endif

ifndef NOHTMLDOCS
NOHTMLDOCS := N
endif

ifeq ($(NOHTMLDOCS),$(filter $(NOHTMLDOCS),y Y))
$(info [INFO] Won't generate HTML documentation.)
endif

.PHONY: install utest clean realclean LOC
.ONESHELL:

all:: $(subst src,dist,$(DIRS))				\
		$(subst src,dist,$(SRC_NORMAL))		\
		$(subst src,dist,$(SRC_JS))			\
		$(subst src,dist,$(SRC_ENDPOINT))	\
		dist/common/php/config.php			\
		dist/libs							\
		$(HTML_DOCS); @:

# Create directory structure in 'dist/'.
$(subst src,dist,$(DIRS)):: dist%: src%
	@:
	mkdir -p $@;

# Copy non-JS, non API endpoint, non-docs files to 'dist/'.
$(subst src,dist,$(SRC_NORMAL)):: dist%: src%
	@:
	cp -p $< $@;

# Copy normal PHP files to 'dist/.' and check the PHP syntax.
$(filter %.php,$(subst src,dist,$(SRC_NORMAL))):: dist%: src%
	@:
	php -l $< > /dev/null;
	cp -p $< $@;

# Copy API endpoint PHP files and generate corresponding docs.
$(subst src,dist,$(SRC_ENDPOINT)):: dist%: src%
	@:
	php -l $< > /dev/null;
	cp -p $< $@;

	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		# Generate reStructuredText documentation.
		mkdir -p dist/doc/rst/api;
		mkdir -p dist/doc/html/api;
		./build/scripts/gendoc.sh $(INST) $@ dist/doc/rst/api/

		# Compile rst docs into HTML.
		pandoc -f rst -t html \
			-o dist/doc/html/api/$(notdir $(@:.php=.rst)) \
			dist/doc/rst/api/$(notdir $(@:.php=.rst))
	fi

# Copy and prepare 'config.php'.
dist/common/php/config.php:: src/common/php/config.php
	@:
	echo "[INFO] Prepare 'config.php'.";
	cp -p $< $@;
	./build/scripts/prep.sh $(INST) $@
	php -l $@ > /dev/null;

# Generate makefiles w/ dependencies for JavaScript files.
dist/%/main.d:: src/%/main.js
	@:
	echo "[INFO] Gen makefile "$@;
	echo -n '$(notdir $<): ' > $@;
	$(NPMBIN)/browserify --list $<|tr '\n' ' ' >> $@;
	echo '\n\t@$(NPMBIN)/browserify $(ROOT)$< \
			-o $(ROOT)$(subst src,dist,$<)' >> $@;

# Compile JavaScript files.
dist/%/main.js: dist/%/main.d src/%/main.js
	@:
	make -C $(dir $<) -f main.d

# Compile normal (non-API) documentation files.
dist/doc/html/%.html:: src/doc/rst/%.rst
	@:
	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		mkdir -p dist/doc/html;
		pandoc -o $@ -f rst -t html $<;
	fi

# Compile README.rst
dist/doc/html/README.html:: README.rst
	@:
	if [ ! "$$NOHTMLDOCS" = "y" ] && [ ! "$$NOHTMLDOCS" = "Y" ]; then
		mkdir -p dist/doc/html;
		pandoc -o $@ -f rst -t html $<;
	fi

# Copy node_modules to 'dist/libs/'.
dist/libs:: node_modules
	@mkdir -p dist/libs
	@cp -Rp $</* dist/libs

install:; ./build/scripts/install.sh $(INST)

utest:; ./utests/api/main.py

clean:
	@:
	rm -rf dist;

realclean:
	@:
	rm -f build/*.iconf;
	rm -rf build/link

LOC:
	# Count the lines of code in LibreSignage.
	wc -l `find .									\
		\(											\
			-path "./dist/*" -o						\
			-path "./utests/api/.mypy_cache/*" -o	\
			-path "./node_modules/*"				\
		\) -prune 									\
		-o -name "*.py" -print						\
		-o -name "*.php" -print						\
		-o -name "*.js" -print						\
		-o -name "*.html" -print					\
		-o -name "*.css" -print						\
		-o -name "*.sh" -print						\
		-o -name "*.json" -print					\
		-o -name "*.py" -print						\
		-o -name "makefile" -print`

%:
	@:
	echo '[INFO] Ignore '$@;
