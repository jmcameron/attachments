#!/usr/bin/perl -i
#
# Fix copyright dates

print "fixvd $ARGV[0] \n with Copyright 2025\n and link \n";

while (<>) {
    s/Copyright \(C\) ([0-9]+)\-[0-9]+/Copyright (C) \1-2025/;
    s/<copyright>\(C\) ([0-9]+)\-[0-9]+/<copyright>(C) \1-2025/;
    s/\@link .*/\@link https:\/\/extensions\.joomla\.org\/extension\/attachments\//;
    print;
}
