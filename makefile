INSTALL_DIR=/var/www/html

.PHONY: install LOC
.SILENT: install LOC

install:
	./install.sh $(INSTALL_DIR)

LOC:
	wc -l `tree -fi --noreport`
