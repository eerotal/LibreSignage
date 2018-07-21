SRC_DIR=src
DIST_DIR=dist
DIST_DOCS_DIR=$(DIST_DIR)/doc
BUILD_CONF=build/scripts/conf.sh
NPMBIN=$(shell build/scripts/npmbin.sh)

JS_MAINS=$(shell find $(SRC_DIR) -name 'main.js' -exec sh -c 'echo -n "$$0 "|sed "s/src/dist/g"' '{}' ';')

.PHONY: LOC clean realclean verify configure
.SILENT: install verify LOC dist clean docs
.ONESHELL:

ifndef SRC_DIR
$(error SRC_DIR not set)
endif

ifndef DIST_DIR
$(error DIST_DIR not set)
endif

all: $(DIST_DIR) install

configure:
	@. $(BUILD_CONF)
	echo '[INFO] Configure LibreSignage...'
	sudo -u $$OWNER ./build/scripts/configure.sh

install:
	# Uses root, no need to source BUILD_CONF.
	@echo '[INFO] Install LibreSignage...'
	./build/scripts/install.sh $(INST)

# Setup dist/.
$(DIST_DIR): $(JS_MAINS)
	@. $(BUILD_CONF)
	echo '[INFO] Create LibreSignage distribution...'
	sudo -u $$OWNER ./build/scripts/dist.sh $(INST)

	if [ "$$NODOCS" != "y" ]; then
		echo '[INFO] Compile LibreSignage documentation...'
		sudo -u $$OWNER ./build/scripts/docs.sh
	else
		echo "[INFO] Won't compile docs."
	fi

# Compile JavaScript files.
$(JS_MAINS): $(shell $(NPMBIN)/browserify --list $@)
	@. $(BUILD_CONF)
	sudo -u $$OWNER ./build/scripts/compilejs.sh $(shell echo "$@"|sed 's/dist/src/g');

verify: $(DIST_DIR)
	@. $(BUILD_CONF)
	echo '[INFO] Verify LibreSignage sources...'
	sudo -u $$OWNER ./build/scripts/verify.sh

utest: install
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
