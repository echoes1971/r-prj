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
Server Widget

Includes a single ExplorerWidget with a stacked form widgets

For a single server connection only.

"""

import os,sys,traceback
from PyQt4 import QtCore, QtGui, QtWebKit

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

class WebWidget(QtGui.QWidget):
	def __init__(self,parent,objectName, url_homepage):
		QtGui.QWidget.__init__(self,parent)
		self.url_homepage = url_homepage
		w = self
		w.setAutoFillBackground(False)
		w.setObjectName("w_%s"%objectName)
		self.gridLayout = QtGui.QGridLayout(w)
		self.gridLayout.setObjectName("gridLayout_%s"%objectName)
		self.gridLayout.setMargin(0)
		self.webAddress = QtGui.QLineEdit(w)
		self.webAddress.setObjectName("webAddress_%s"%objectName)
		self.gridLayout.addWidget(self.webAddress, 0, 2, 1, 1)
		self.frame = QtGui.QFrame(w)
		self.frame.setFrameShape(QtGui.QFrame.StyledPanel)
		self.frame.setFrameShadow(QtGui.QFrame.Sunken)
		self.frame.setObjectName("frame_%s"%objectName)
		self.verticalLayout = QtGui.QVBoxLayout(self.frame)
		self.verticalLayout.setMargin(0)
		self.verticalLayout.setObjectName("verticalLayout")
		self.webView = QtWebKit.QWebView(self.frame)
		self.webView.setAutoFillBackground(False)
		self.webView.setUrl(QtCore.QUrl(self.url_homepage))
		self.webView.setObjectName("webView_%s"%objectName)
		self.verticalLayout.addWidget(self.webView)
		self.gridLayout.addWidget(self.frame, 1, 0, 1, 3)
		self.label = QtGui.QLabel(w)
		self.label.setObjectName("label_%s"%objectName)
		self.label.setText(QtGui.QApplication.translate("WebComponent", "Url:", None, QtGui.QApplication.UnicodeUTF8))
		self.gridLayout.addWidget(self.label, 0, 1, 1, 1)
		# Slots
		self.bind()
		# Logic
		self.server = None
		self.formFactory = None
		# Container
		self.container = None
	def bind(self):
		QtCore.QObject.connect(self.webAddress,QtCore.SIGNAL("returnPressed()"),self.webAddress_returnPressed)
		QtCore.QObject.connect(self.webView,QtCore.SIGNAL("loadProgress(int)"),self.loadProgress)
		QtCore.QObject.connect(self.webView,QtCore.SIGNAL("loadFinished(bool)"),self.loadFinished)
		QtCore.QObject.connect(self.webView,QtCore.SIGNAL("urlChanged (const QUrl&)"),self.urlChanged)
		QtCore.QObject.connect(self.webView,QtCore.SIGNAL("statusBarMessage (const QString&)"),self.SetStatusText)
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
		print "WebWidget.setFormFactory: TODO"
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
	def urlChanged(self,s):
		self.webAddress.setText(s.toString())
	def loadProgress(self,x):
		self.SetStatusText("Loaded %d%%"%x)
		self.SetProgress(x)
	def loadFinished(self,b):
		if b:
			self.SetStatusText("")
		else:
			self.SetStatusText("Error while loading.")
	def hideNavigation(self):
		self.webAddress.hide()
		self.label.hide()
	def showNavigation(self):
		self.webAddress.show()
		self.label.show()
	def webAddress_returnPressed(self):
		if ("%s"%self.webAddress.text()).startswith("http://"):
			self.webView.setUrl(QtCore.QUrl(self.webAddress.text()))
		else:
			self.webView.setUrl(QtCore.QUrl("http://%s" % self.webAddress.text()))
