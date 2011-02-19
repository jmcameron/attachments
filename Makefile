VERSION = '2.2'
# DATE = 'December  3, 2010'

all: parts attachments-$(VERSION).zip

INSTALLS = attachments_plugin \
	   add_attachment_btn_plugin \
	   insert_attachments_token_btn_plugin \
	   attachments_plugin_framework \
	   attachments_for_content \
	   attachments_search \
	   show_attachments_in_editor_plugin

EXTRAS = attachments_for_quickfaq attachments_for_jevents

NAMES = $(INSTALLS) $(EXTRAS)

ZIPS = $(NAMES:=.zip)

ZIPIGNORES = -x "*.svn/*" -x ".svnignore" -x ".directory" -x "*.xcf"


parts: $(ZIPS)

%.zip:
	@echo "-------------------------------------------------------"
	@echo $*
	@rm -f $@
	@(cd $*; zip -r ../$@ * $(ZIPIGNORES))


attachments-$(VERSION).zip: $(ZIPS)
	@echo "-------------------------------------------------------"
	@echo "Creating extension zip file: attachments-$(VERSION).zip"
	@mv $(INSTALLS:=.zip) attachments_component/admin/install/
	@(cd attachments_component; zip -r ../$@ * $(ZIPIGNORES))


clean:
	@find . -name '*~' -exec rm {} \;
	@rm -f _tests.pdf

veryclean: clean
	@rm -f $(ZIPS) attachments_component/admin/install/*.zip
	@rm -f attachments-$(VERSION).zip

fixversions:
	@echo "Updating all install xml files to version $(VERSION)"
	@export ATVERS=$(VERSION); export ATDATE=$(DATE); find . \( -name 'helper.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec ./fixvd {} \;

revertversions:
	@echo "Reverting all install xml files"
	@find . \( -name 'helper.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec git checkout {} \;

check: 
	find . -exec grep -n '???' {} /dev/null \; | egrep -v -e '(\.git|\.zip|\.gif|\.png|\.org|plugin_manual)'

check2: 
	find . -name '*.php' -exec egrep -n -e "^[[:space:]]+echo.*<br.*" {} /dev/null \;

purge:
	@find . -name '*.bak' -exec rm {} \;

manual: extensions_manual/manual.rst
	@echo "Creating Attachments Extension Manual"
	rst2pdf extensions_manual/manual.rst -o extensions_manual.pdf --stylesheets extensions_manual/.rst2style

_tests.pdf: _tests.rst
	/usr/local/bin/rst2pdf _tests.rst -s narrowmargins -o _tests.pdf


docs:
	@echo "Generating documentation using PhpDocumentor..."
	@echo " (Note: see docerrs.log for error log)"
	@phpdoc -c /home/jmcameron/src/attachments/work/docs.ini | egrep -v -e '(Ignored|File not parsed)' > docerrs.log

fixperms:
	@find . -name '*.gif' -exec chmod -x {} \;
	@find . -name '*.html' -exec chmod -x {} \;
	@find . -name '*.ini' -exec chmod -x {} \;
	@find . -name '*.php' -exec chmod -x {} \;
	@find . -name '*.png' -exec chmod -x {} \;
	@find . -name '*.txt' -exec chmod -x {} \;
	@find . -name '*.txt' -exec chmod g-s {} \;
	@find . -name '*.xml' -exec chmod -x {} \;
	@chmod g-s *.ppr

watchfiles:
	@xterm -geometry 45x200+0+0 -T Attachments -e /home/jmcameron/src/attachments/work/watch &

# windmill:
# 	$(eval APASS := `kdialog --password "Admin password:"`)
# 	@echo "START: `date`"
# 	@../testing/make_nonsef /var/www/test/joomla/configuration.php
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
