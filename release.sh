#!/bin/bash

syntax() {
  echo "Syntax: `basename $0` <version number>"
  echo " Where: version number like 0.5.0-beta"
  echo
}

if [ -z $1 ]; then
  syntax
  exit 1
fi

VERSION_NUMBER=$1

# Save new version
echo $VERSION_NUMBER > VERSION

# Web package
#rm -rf build
mkdir -p build/rproject-$VERSION_NUMBER
rm -rf build/r-prj
bzr checkout bzr://r-prj.bzr.sourceforge.net/bzrroot/r-prj build/r-prj/
cp -R build/r-prj/php/* build/rproject-$VERSION_NUMBER/
rm build/rproject-$VERSION_NUMBER/config_local.php
cd build
tar cvjf rproject-$VERSION_NUMBER.tbz rproject-$VERSION_NUMBER
mv rproject-$VERSION_NUMBER.tbz ../
cd ..

# Python package
cd python
SED_STRING="s/version=\"\\(.*\\)\"/version=\"$VERSION_NUMBER\"/g"
sed -i $SED_STRING setup.py
python setup.py sdist upload
mv dist/R-Prj-$VERSION_NUMBER.tar.gz ../
cd ..

echo
echo "Please upload rproject-$VERSION_NUMBER.tbz and R-Prj-$VERSION_NUMBER.tar.gz to Sourceforge!"
echo

