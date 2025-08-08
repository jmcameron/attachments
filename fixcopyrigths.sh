#!/bin/bash

FILE=$1
BIN=$(basename $0)
JOOMLAEXTENSION="https:\/\/github\.com\/jmcameron\/attachments"

printf "$BIN : $FILE with with Copyright 2025\nand link $JOOMLAEXTENSION\n";


sed -i 's%Copyright \(C\) ([0-9]+)\-[0-9]+%Copyright (C) \1-2025%g' $FILE
sed -i 's%<copyright>.*</copyright>%<copyright>\(C\) 2007\-2025 Jonathan M\. Cameron\. All rights reserved\.</copyright>%g' $FILE
sed -i 's%@link .*%\@link '$JOOMLAEXTENSION'%g' $FILE
sed -i 's%<authorUrl>.*</authorUrl>%<authorUrl>'$JOOMLAEXTENSION'</authorUrl>%g' $FILE
sed -i 's%<packagerurl>.*</packagerurl>%<packagerurl>'$JOOMLAEXTENSION'</packagerurl>%g' $FILE

