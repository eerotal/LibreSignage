INSTALL_DIR=/var/www/html

.PHONY: install verify LOC
.SILENT: install verify LOC

verify:
	echo '## Verify LibreSignage sources'
	./verify.sh

install: verify
	echo '## Install'
	./install.sh $(INSTALL_DIR)

LOC:
	wc -l `tree -fi --noreport`
