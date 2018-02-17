SRC_DIR=src
DIST_DIR=dist

.PHONY: install LOC clean
.SILENT: install verify LOC build clean

ifndef SRC_DIR
$(error SRC_DIR not set)
endif

ifndef DIST_DIR
$(error DIST_DIR not set)
endif

build: src/*
	echo '## Build LibreSignage...'
	mkdir -p $(DIST_DIR)
	cp -Rpv $(SRC_DIR)/* $(DIST_DIR)/

verify: src/*
	# Run the source verification scripts.
	echo '## Verify LibreSignage sources'
	./verify.sh

install: verify build
	# Install LibreSignage to INSTALL_DIR.
	echo '## Install'
	./install.sh

clean:
	echo '## Clean LibreSignage build files'
	rm -rfv dist

LOC:
	# Count the lines of code in LibreSignage.
	wc -l `find . -name "*.php"		\
			-o -name "*.js"		\
			-o -name "*.html"	\
			-o -name "*.css"	\
			-o -name "*.sh"		\
			-o -name "*.json"	\
			-o -name "makefile"`
