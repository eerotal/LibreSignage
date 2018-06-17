SRC_DIR=src
DIST_DIR=dist
DIST_DOCS_DIR=$(DIST_DIR)/doc

.PHONY: LOC clean realclean verify
.SILENT: install verify LOC dist clean docs

ifndef SRC_DIR
$(error SRC_DIR not set)
endif

ifndef DIST_DIR
$(error DIST_DIR not set)
endif

install: $(DIST_DIR) $(DIST_DOCS_DIR)/html
	echo '## Install LibreSignage...'
	./build/scripts/install.sh $(INST)

$(DIST_DIR): verify
	echo '## Create LibreSignage distribution...'
	rm -rfv $(DIST_DIR)
	./build/scripts/mkdist.sh

$(DIST_DOCS_DIR)/html: README.rst $(DIST_DOCS_DIR)
	echo '## Compile LibreSignage documentation...'
	./build/scripts/mkdocs.sh

verify: $(shell find $(SRC_DIR))
	echo '## Verify LibreSignage sources...'
	./build/scripts/verify.sh

clean:
	echo '## Clean LibreSignage build files...'
	rm -rfv $(DIST_DIR)

realclean: clean
	echo '## Clean all LibreSignage build files...'
	rm -fv build/*.instconf

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
