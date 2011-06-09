all: help.html


help.html: help.rst help.css help.config
	rst2html --no-generator --language=en --config=help.config help.rst > help.html
	cp help.html /var/www/test/joomla16/administrator/components/com_attachments/help/en-GB/

updatecss:
	cp help.css /var/www/test/joomla16/administrator/components/com_attachments/help/en-GB/
