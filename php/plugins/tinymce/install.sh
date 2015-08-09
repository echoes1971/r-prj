#!/bin/bash

wget --no-check-certificate http://github.com/downloads/tinymce/tinymce/tinymce_3.4.7.zip
unzip tinymce_3.4.7.zip
mv tinymce/jscripts skins/default/

rm -rf tinymce
rm tinymce_3.4.7.zip

