#!/bin/bash

# Get the release version number
ZIPFILE=$1
UPDATEFILE=$(find . -name $2 | tr -d '\n')

echo "fixsha : $UPDATEFILE with sha256 of $1";

SHA256=$(sha256sum $1 | cut -f 1 -d ' ' | tr -d '\n' | tr '[a-z]' '[A-Z]')
echo "SHA256:$SHA256"

SHA384=$(sha384sum $1 | cut -f 1 -d ' ' | tr -d '\n' | tr '[a-z]' '[A-Z]')
echo "SHA384:$SHA384"

SHA512=$(sha512sum $1 | cut -f 1 -d ' ' | tr -d '\n' | tr '[a-z]' '[A-Z]')
echo "SHA512:$SHA512"


sed -i 's%<sha256>.*</sha256>%<sha256>'$SHA256'</sha256>%g' $UPDATEFILE
sed -i 's%<sha384>.*</sha384>%<sha384>'$SHA384'</sha384>%g' $UPDATEFILE
sed -i 's%<sha512>.*</sha512>%<sha512>'$SHA512'</sha512>%g' $UPDATEFILE