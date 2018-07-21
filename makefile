SRC_DIR=src
SRC_DOCS_DIR=$(SRC_DIR)/doc/rst

DIST_DIR=dist
DIST_DOCS_DIR=$(DIST_DIR)/doc/html

BUILD_CONF=build/scripts/conf.sh
NPMBIN=$(shell build/scripts/npmbin.sh)

SRC_NO_JS=$(shell find $(SRC_DIR) ! -name "*.js") README.rst

JS_SRC=$(shell find $(SRC_DIR) -name "main.js")
DOC_SRC=$(shell find $(SRC_DOCS_DIR) -name "*.rst")

JS_DIST=$(shell echo $(JS_SRC)|sed 's/src/dist/g')
DOC_DIST=$(shell echo $(DOC_SRC)|sed 's/src/dist/g')

ifndef SRC_DIR
$(error SRC_DIR not set)
endif

ifndef DIST_DIR
$(error DIST_DIR not set)
endif

.PHONY: LOC clean realclean verify utest configure

.SILENT: install distrib $(DIST_DIR) $(DIST_DOCS_DIR)
	configure verify utest clean realclean LOC

.ONESHELL:

all: distrib

install:
	# Uses root, no need to source BUILD_CONF.
	@echo '[INFO] Install LibreSignage...'
	./build/scripts/install.sh $(INST)

distrib: $(DIST_DIR) $(JS_DIST) $(DIST_DOCS_DIR)

# Setup dist/.
$(DIST_DIR): $(SRC_NO_JS)
	@. $(BUILD_CONF)
	echo '[INFO] Create LibreSignage distribution...'
	sudo -u $$OWNER ./build/scripts/dist.sh $(INST)

# Compile docs.
$(DIST_DOCS_DIR): $(DOC_DIST) README.rst
	@. $(BUILD_CONF)
	if [ "$$NODOCS" != "y" ]; then
		echo '[INFO] Compile LibreSignage documentation...'
		sudo -u $$OWNER ./build/scripts/docs.sh
	else
		echo "[INFO] Won't compile docs."
	fi

# Compile JavaScript files.
$(JS_DIST): $(shell $(NPMBIN)/browserify --list $(shell echo "$@"|sed 's/dist/src/g'))
	@. $(BUILD_CONF)
	sudo -u $$OWNER ./build/scripts/compilejs.sh $(shell echo "$@"|sed 's/dist/src/g');

configure:
	@. $(BUILD_CONF)
	echo '[INFO] Configure LibreSignage...'
	sudo -u $$OWNER ./build/scripts/configure.sh

verify: $(DIST_DIR)
	@. $(BUILD_CONF)
	echo '[INFO] Verify LibreSignage sources...'
	sudo -u $$OWNER ./build/scripts/verify.sh

utest:
	@. $(BUILD_CONF)
	echo '[INFO] Unit testing LibreSignage...'
	sudo -u $$OWNER ./utests/api/main.py

clean:
	@. $(BUILD_CONF)
	echo '[INFO] Clean LibreSignage build files...'
	sudo -u $$OWNER rm -rf $(DIST_DIR)

realclean: clean
	@. $(BUILD_CONF)
	echo '[INFO] Clean all LibreSignage build files...'
	sudo -u $$OWNER rm -f build/*.iconf
	sudo -u $$OWNER rm -rf build/link

LOC:
	# Count the lines of code in LibreSignage.
	wc -l `find .                                     \
            \(                                        \
                -path "./dist/*" -o                   \
                -path "./utests/api/.mypy_cache/*" -o \
                -path "./node_modules/*"              \
            \) -prune                                 \
            -o -name "*.py" -print                    \
            -o -name "*.php" -print                   \
            -o -name "*.js"	-print                    \
            -o -name "*.html" -print                  \
            -o -name "*.css" -print                   \
            -o -name "*.sh"	-print                    \
            -o -name "*.json" -print                  \
            -o -name "*.py" -print                    \
            -o -name "makefile" -print`               \
