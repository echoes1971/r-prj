#!/bin/bash

COST_PER_YEAR=97200

# External Libraries
sloccount --personcost $COST_PER_YEAR php/formulator/jscalendar-1.0 php/js/3rdparties php/xmlrpc php/plugins/tinymce/tinymce php/plugins/tinymce/tinymce_3.4.7.zip php/plugins/tinymce/skins/default/jscripts


# My code
sloccount --personcost $COST_PER_YEAR php python

