VERSION = "4.1.4.2"
VERSION2 = $(shell echo $(VERSION)|sed 's/ /-/g')
ZIPFILE = attachments-$(VERSION2).zip


# Only set DATE if you need to force the date.  
# (Otherwise it uses the current date.)
# DATE = "February 19, 2011"

all: parts $(ZIPFILE) fixsha

INSTALLS = attachments_plugin \
	   add_attachment_btn_plugin \
	   insert_attachments_id_token_btn_plugin \
	   insert_attachments_token_btn_plugin \
	   attachments_plugin_framework \
	   attachments_for_content \
	   attachments_search \
	   show_attachments_in_editor_plugin \
	   attachments_quickicon_plugin \
	   attachments_finder_plugin \
	   attachments_component

EXTRAS = 

NAMES = $(INSTALLS) $(EXTRAS)

ZIPS = $(NAMES:=.zip)

ZIPIGNORES = -x "*.git*" -x "*.svn*" -x ".directory" -x "*.xcf" -x "*admin/help*"

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
	@scp $(ZIPFILE) jmcameron:/home/jmcameron/webapps/jmcameron/attachments/downloads/
	@echo

updateweb:
	@echo "Updating updates on jmcameron.net..."
	@ssh jmcameron.net "cd webapps/jmcameron/attachments/updates; git pull"

count:
	@ssh jmcameron "cd logs/apache > /dev/null ; grep $(ZIPFILE) access_jmcameron*.log* | wc -l"

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
	@export ATVERS=$(VERSION); export ATDATE=$(DATE); find . \( -name 'AttachmentsDefines.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec  ./fixvd {} \;

revertversions:
	@echo "Reverting all install xml files"
	@find . \( -name 'AttachmentsDefines.php' -o -name 'help.rst' -o -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec git checkout {} \;

fixsha:
	@echo "Updating update xml files with checksums"
	./fixsha.sh $(ZIPFILE) 'update_pkg.xml'

fixcopyrights:
	@find . \( -name '*.php' -o -name '*.ini' -o -name '*.xml' \) -exec ./fixcopyright.sh {} \;

check: 
	find . -type f -exec grep -n '???' {} /dev/null \; | egrep -v -e '(\.git|\.zip|\.gif|\.png|\.org|plugin_manual|coverage_|/temp/)'

check2: 
	find . -name '*.php' -exec egrep -n -e "^[[:space:]]+echo.*<br.*" {} /dev/null \;

classdefs: 
	find . -name '*.php' -exec egrep -n -e "^class " {} /dev/null \;

diff25:
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_plugin /var/www/test/joomla25/plugins/content/attachments/
	@diff -qwcr --exclude-from=diffs/exclude_component_site.txt attachments_component/site /var/www/test/joomla25/components/com_attachments
	@diff -qwcr --exclude-from=diffs/exclude_component_admin.txt attachments_component/admin /var/www/test/joomla25/administrator/components/com_attachments
	@diff -qwcr --exclude-from=diffs/exclude_all.txt add_attachment_btn_plugin /var/www/test/joomla25/plugins/editors-xtd/add_attachment
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_for_content /var/www/test/joomla25/plugins/attachments/attachments_for_content
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_plugin_framework /var/www/test/joomla25/plugins/attachments/attachments_plugin_framework
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_search /var/www/test/joomla25/plugins/search/attachments/
	@diff -qwcr --exclude-from=diffs/exclude_all.txt insert_attachments_token_btn_plugin /var/www/test/joomla25/plugins/editors-xtd/insert_attachments_token
	@diff -qwcr --exclude-from=diffs/exclude_all.txt show_attachments_in_editor_plugin /var/www/test/joomla25/plugins/system/show_attachments

diff3test:
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_plugin /var/www/test/joomla3test/plugins/content/attachments/
	@diff -qwcr --exclude-from=diffs/exclude_component_site.txt attachments_component/site /var/www/test/joomla3test/components/com_attachments
	@diff -qwcr --exclude-from=diffs/exclude_component_admin.txt attachments_component/admin /var/www/test/joomla3test/administrator/components/com_attachments
	@diff -qwcr --exclude-from=diffs/exclude_all.txt add_attachment_btn_plugin /var/www/test/joomla3test/plugins/editors-xtd/add_attachment
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_for_content /var/www/test/joomla3test/plugins/attachments/attachments_for_content
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_plugin_framework /var/www/test/joomla3test/plugins/attachments/attachments_plugin_framework
	@diff -qwcr --exclude-from=diffs/exclude_all.txt attachments_search /var/www/test/joomla3test/plugins/search/attachments/
	@diff -qwcr --exclude-from=diffs/exclude_all.txt insert_attachments_token_btn_plugin /var/www/test/joomla3test/plugins/editors-xtd/insert_attachments_token
	@diff -qwcr --exclude-from=diffs/exclude_all.txt show_attachments_in_editor_plugin /var/www/test/joomla3test/plugins/system/show_attachments

purge:
	@find . -name '*.bak' -exec rm {} \;
	@rm -f .tx/*.bak

unittests:
	@echo
	@echo "Running unit tests..."
	@cd test; phing -Droot=/var/www/test/joomla25/ unit_tests
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
	@phpdoc -c /home/jmcameron/src/attachments/attachments3/docs.ini | egrep -v -e '(Ignored|File not parsed)' > docerrs.log

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
	@xterm -geometry 45x200+0+0 -T Attachments -e /home/jmcameron/src/attachments/attachments3/watch &

watchfiles25:
	@xterm -geometry 45x200+0+0 -T Attachments -e /home/jmcameron/src/attachments/attachments3/watch25 &

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
