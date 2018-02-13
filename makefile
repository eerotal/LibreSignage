INSTALL_DIR=/var/www/html

.PHONY: install verify LOC
.SILENT: install verify LOC

verify:
	# Run the source verification scripts.
	echo '## Verify LibreSignage sources'
	./verify.sh

install: verify
	# Install LibreSignage to INSTALL_DIR.
	echo '## Install'
	./install.sh $(INSTALL_DIR)

LOC:
	# Count the lines of code in LibreSignage.
	wc -l `find . -name "*.php"		\
			-o -name "*.js"		\
			-o -name "*.html"	\
			-o -name "*.css"	\
			-o -name "*.sh"		\
			-o -name "*.json"	\
			-o -name "makefile"`
