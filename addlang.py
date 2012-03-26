#!/usr/bin/python

"""

Update Attachments translation files

"""

import sys, os

root_path = os.path.dirname(os.getcwd()) + '/'

# Parse the arguments
from optparse import OptionParser
parser = OptionParser("usage: %prog [options]")

parser.add_option("--code", dest="code", default='',
                  help="Five letter designator code for the desired language (eg, fr-FR)")

parser.add_option("--lang", dest="lang", default='',
                  help="The language name (eg, French)")


(opt, args) = parser.parse_args()

def print_usage(msg = None):
    print ""
    if msg != None:
        print "ERROR: ", msg
    print ""
    parser.print_usage()
    print
    sys.exit()


# Give the user help if they seem clueless
if opt.code == "":
    print_usage('Forgot language code (eg, --code fr-FR)')
    
if opt.lang == "":
    print_usage('Forgot language name (eg, --lang French)')

if len(args) > 0:
    print_usage()


# Read the lines from the transifex config file
filename = '.tx/config'

f=open(filename, 'r')
lines=f.readlines()
f.close()

# Create a temporary file to receive the modified lines
fout = open(filename + '.temp', 'w')

for line in lines:
    line = line.strip()

    if len(line) > 2 and line[0] == '#' and 'Language' in line:
        newline = line[2:]
        newline = newline.replace('qq_ZZ', opt.code.replace('-', '_'))
        newline = newline.replace('qq-ZZ', opt.code)
        newline = newline.replace('Language', opt.lang)
        print >> fout, newline
        print >> fout, line
    else:
        print >> fout, line

fout.close()

os.rename(filename, filename + '.bak')
os.rename(filename + '.temp', filename)
print
print " *** UPDATED: ", filename
print

