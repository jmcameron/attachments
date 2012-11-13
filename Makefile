VERSION = "3.1-Alpha6"
VERSION2 = $(shell echo $(VERSION)|sed 's/ /-/g')
ZIPFILE = attachments-$(VERSION2).zip

# Only set DATE if you need to force the date.  
# (Otherwise it uses the current date.)
# DATE = "February 19, 2011"

all: parts $(ZIPFILE)

INSTALLS = attachments_plugin \
	   add_attachment_btn_plugin \
	   insert_attachments_token_btn_plugin \
	   attachments_plugin_framework \
	   attachments_for_content \
	   attachments_search \
	   show_attachments_in_editor_plugin \
	   attachments_component

EXTRAS = 

NAMES = $(INSTALLS) $(EXTRAS)

ZIPS = $(NAMES:=.zip)

ZIPIGNORES = -x "*.svn/*" -x ".svnignore" -x ".directory" -x "*.xcf"

parts: $(ZIPS)

%.zip:
	@echo "-------------------------------------------------------"
	@echo "Creating zip file for: $*"
	@rm -f $@
	@(cd $*; zip -r ../$@ * $(ZIPIGNORES))

$(ZIPFILE): $(ZIPS)
	@echo "-------------------------------------------------------"
	@echo "Creating extension zip file: $(ZIPFILE)"
	@mv $(INSTALLS:=.zip) pkg_attachments/packages/
	@(cd pkg_attachments; zip -r ../$@ * $(ZIPIGNORES))
	@echo "-------------------------------------------------------"
	@echo "Finished creating package $(ZIPFILE)."


upload:
	@echo "-------------------------------------------------------"
	@echo "Copying new package $(ZIPFILE) to jmcameron.net"
	@scp $(ZIPFILE) jmcameron:/home/jmcameron/webapps/jmcameron/attachments/
	@echo

updateweb:
	@echo "Updating updates on jmcameron.net..."
	@ssh jmcameron.net "cd webapps/jmcameron/attachments/updates; svn update"

clean:
	@find . -name '*~' -exec rm {} \;
	@rm -f _tests.pdf

veryclean: clean
	@rm -f $(ZIPS) pkg_attachments/packages/*.zip
	@rm -f $(ZIPFILE).zip
	@rm -rf test/coverage_db
	@rm -rf test/coverage_result

fixversions:
	@echo "Updating all install xml files to version $(VERSION)"
	@export ATVERS=$(VERSION); export ATDATE=$(DATE); find . \( -name 'defines.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' ! -name 'updates.xml' \) -exec ./fixvd {} \;

revertversions:
	@echo "Reverting all install xml files"
	@find . \( -name 'defines.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec git checkout {} \;

check: 
	find . -exec grep -n '???' {} /dev/null \; | egrep -v -e '(\.git|\.zip|\.gif|\.png|\.org|plugin_manual|coverage_)'

check2: 
	find . -name '*.php' -exec egrep -n -e "^[[:space:]]+echo.*<br.*" {} /dev/null \;

classdefs: 
	find . -name '*.php' -exec egrep -n -e "^class " {} /dev/null \;

purge:
	@find . -name '*.bak' -exec rm {} \;
	@rm -f .tx/*.bak

unittests:
	@echo
	@echo "Running unit tests..."
	@cd test; phing -Droot=/var/www/test/joomla16/ unit_tests
	@echo

unittests_show: unittests
	@firefox test/coverage_result/index.html

manual: extensions_manual/manual.rst
	@echo "Creating Attachments Extension Manual"
	rst2pdf extensions_manual/manual.rst -o extensions_manual.pdf --stylesheets extensions_manual/.rst2style

_tests.pdf: _tests.rst
	rst2pdf _tests.rst -s narrowmargins -o _tests.pdf


docs:
	@echo "Generating documentation using PhpDocumentor..."
	@echo " (Note: see docerrs.log for error log)"
	@phpdoc -c /home/jmcameron/src/attachments/work-j1.6/docs.ini | egrep -v -e '(Ignored|File not parsed)' > docerrs.log

fixperms:
	@find . -name '*.gif' -exec chmod -x {} \;
	@find . -name '*.html' -exec chmod -x {} \;
	@find . -name '*.ini' -exec chmod -x {} \;
	@find . -name '*.php' -exec chmod -x {} \;
	@find . -name '*.png' -exec chmod -x {} \;
	@find . -name '*.txt' -exec chmod -x {} \;
	@find . -name '*.txt' -exec chmod g-s {} \;
	@find . -name '*.xml' -exec chmod -x {} \;

watchfiles:
	@xterm -geometry 45x200+0+0 -T Attachments -e /home/jmcameron/src/attachments/work-j1.6/watch &

watchfiles25:
	@xterm -geometry 45x200+0+0 -T Attachments -e /home/jmcameron/src/attachments/work-j1.6/watch25 &

# windmill:
# 	$(eval APASS := `kdialog --password "Admin password:"`)
# 	@echo "START: `date`"
# 	@../testing/make_nonsef /var/www/test/joomla16/configuration.php
# 	windmill firefox exit http://localhost/test/joomla test=tests password=$(APASS)
# 	@echo "FINISH: `date`"
# 
# # Test that doesn't exit to develop new tests
# windmill2:
# 	$(eval APASS := `kdialog --password "Admin password:"`)
# 	windmill firefox http://localhost/test/joomla test=tests/test_01_login.py password=$(APASS)


# findtabs:
# 	@echo "Looking for files with embedded tabs (should list only Makefile):"
# 	@find . -nowarn -exec grep -l -e '	' {} \; | egrep -v -e '(\.svn|\.png|\.gif|\.ppr|\.project)'

tabify:
	@echo "Converting all PHP files to tabs..."
	find . \( -name '*.php' ! -name 'Changelog.php' \) -execdir tabify {} \;
