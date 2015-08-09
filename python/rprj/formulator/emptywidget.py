# -*- coding: utf-8 -*-

#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: editwidgetcontroller.py $
# @package qtwidgets
# 
# Permission to use, copy, modify, and distribute this software for any
# purpose with or without fee is hereby granted, provided that the above
# copyright notice and this permission notice appear in all copies.
#
# THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
# WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
# ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
# WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
# ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
# OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
#

"""
Empty Widget

USED AS A STUB FOR ALL COMPONENT'S WIDGETS

"""

import os,sys,traceback
from PyQt4 import QtCore, QtGui

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

class EmptyWidget(QtGui.QWidget):
	def __init__(self,parent,objectName, url_homepage):
		QtGui.QWidget.__init__(self,parent)
		self.url_homepage = url_homepage
		w = self
		w.setAutoFillBackground(False)
		w.setObjectName("w_%s"%objectName)
		self.gridLayout = QtGui.QGridLayout(w)
		self.gridLayout.setObjectName("gridLayout_%s"%objectName)
		self.gridLayout.setMargin(0)
		# Add widgets here
		#self.webAddress = QtGui.QLineEdit(w)
		#self.webAddress.setObjectName("webAddress_%s"%objectName)
		#self.gridLayout.addWidget(self.webAddress, 0, 2, 1, 1)
		# ...
		# Slots
		self.bind()
		# Logic
		self.server = None
		self.formFactory = None
		# Container
		self.container = None
	def bind(self):
		# Bind signals and slots here
		pass
		#QtCore.QObject.connect(self.webAddress,QtCore.SIGNAL("returnPressed()"),self.webAddress_returnPressed)
# ############################## PROPERTIES ###################################
	def setContainer(self,c):
		self.container = c
	def setServer(self,server):
		self.server = server
	def getServer(self):
		return self.server
	def setFormFactory(self,formFactory):
		self.formFactory = formFactory
		# Update UI
		print "EmptyWidget.setFormFactory: TODO"
	def getName(self):
		return self.server.getServerIDString()
# ############################## PREFERENCES ###################################
	def getPref(self,section,option,default=None):
		if self.container is None:
			return default
		return self.container.getPref(section,option,default)
	def setPref(self,section,option,valore):
		if self.container is None:
			return
		self.container.setPref(section,option,valore)
# ############################## ... ###################################
	def Alert(self,msg, eccezione=None):
		if self.container is None:
			return
		self.container.Alert(msg,eccezione)
	def Confirm(self,msg):
		if self.container is None:
			return True
		return self.container.Confirm(msg)
	def SetStatusText(self,msg):
		if self.container is None:
			return
		self.container.SetStatusText(msg)
	def SetProgress(self,perc):
		if self.container is None:
			return
		self.container.SetProgress(perc)
# ############################## UI ###################################
# ############################## LOGIC ###################################
# ############################## SLOTS ###################################
