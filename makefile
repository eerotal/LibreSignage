SRC_DIR=src
DIST_DIR=dist

.PHONY: install LOC clean
.SILENT: install verify LOC dist clean

ifndef SRC_DIR
$(error SRC_DIR not set)
endif

ifndef DIST_DIR
$(error DIST_DIR not set)
endif

dist: src/*
	echo '## Create LibreSignage dist...'
	./build/mkdist.sh

verify: src/*
	# Run the source verification scripts.
	echo '## Verify LibreSignage sources'
	./build/verify.sh

install: verify dist
	# Install LibreSignage to INSTALL_DIR.
	echo '## Install'
	./build/install.sh $(INST)

clean:
	echo '## Clean LibreSignage build files'
	rm -rfv dist
	rm -f *.instconf

LOC:
	# Count the lines of code in LibreSignage.
	wc -l `find .	-name "dist" -prune -o		\
			-name "*.php" -print		\
			-o -name "*.js"	-print		\
			-o -name "*.html" -print	\
			-o -name "*.css" -print		\
			-o -name "*.sh"	-print		\
			-o -name "*.json" -print	\
			-o -name "makefile" -print`
