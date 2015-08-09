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
R-Project Email component

"""

import os,sys,traceback
from PyQt4 import QtCore, QtGui

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

from qtcomponent import *
from rprj.formulator import apps_rc,  emailwidget

class EmailComponent(qtComponent):
	"""Empty Component"""
	def __init__(self, config, parent=None):
		qtComponent.__init__(self, config, parent)
		self._objectNamePrefix="rprj_web"
		self.widget = None
		self.menu = None
		self.server = None
		self.formFactory = None
	def init(self):
		self.w=None
		self.connections = []
	def renderWidget(self,parentWidget):
		self.widget = QtGui.QTabWidget(parentWidget)
		self.widget.setTabsClosable(True)
		self.widget.setMovable(True)
		self.widget.setObjectName("%s_widget"%self._objectNamePrefix)
		# Binds
		#QtCore.QObject.connect(self.widget,QtCore.SIGNAL('selectedFolder(QStandardItem,PyQt_PyObject)'),self.slotSelectedFolder)
		# Tabs
		QtCore.QObject.connect(self.widget,QtCore.SIGNAL("tabCloseRequested(int )"),self.slotTabCloseRequested)
	def renderMenu(self,menubar):
		# Main Menu
		self.menu = QtGui.QMenu(menubar)
		self.menu.setTitle(QtGui.QApplication.translate("Rprj", "Email", None, QtGui.QApplication.UnicodeUTF8))
		self.menu.setObjectName("%s_menu"%self._objectNamePrefix)
		self.actionImportFolder = QtGui.QAction(self.menu)
		self.actionImportFolder.setText(QtGui.QApplication.translate("Rprj", "Import Folder", None, QtGui.QApplication.UnicodeUTF8))
		self.actionImportFolder.setObjectName(_fromUtf8("actionImportFolder"))
		self.menu.addAction(self.actionImportFolder)
		self.actionImportMbox = QtGui.QAction(self.menu)
		self.actionImportMbox.setText(QtGui.QApplication.translate("Rprj", "Import Mbox", None, QtGui.QApplication.UnicodeUTF8))
		self.actionImportMbox.setObjectName(_fromUtf8("actionImportMbox"))
		self.menu.addAction(self.actionImportMbox)
		self.actionPreferences = QtGui.QAction(self.menu)
		self.actionPreferences.setText(QtGui.QApplication.translate("Rprj", "Preferences", None, QtGui.QApplication.UnicodeUTF8))
		self.actionPreferences.setObjectName(_fromUtf8("actionPreferences"))
		self.menu.addAction(self.actionPreferences)
		# Menu Admin
		if self.getPref("Main","Admin",False):
			self.menuAdmin = QtGui.QMenu(self.menu)
			self.menuAdmin.setTitle(QtGui.QApplication.translate("Rprj", "Admin", None, QtGui.QApplication.UnicodeUTF8))
			self.menuAdmin.setObjectName("%s_menuAdmin"%self._objectNamePrefix)
			#self.actionCheckDB = QtGui.QAction(self.menuAdmin)
			#self.actionCheckDB.setText(QtGui.QApplication.translate("Rprj", "Check DB", None, QtGui.QApplication.UnicodeUTF8))
			#self.actionCheckDB.setObjectName(_fromUtf8("actionCheckDB"))
			#self.menuAdmin.addAction(self.actionCheckDB)
			pass
		# Only Admins should see this
		if self.getPref("Main","Admin",False):
			self.menu.addSeparator()
			self.menu.addMenu(self.menuAdmin)
		menubar.addAction(self.menu.menuAction())
		# Binds
		# Menu
		QtCore.QObject.connect(self.actionImportFolder,QtCore.SIGNAL("triggered()"),self.slotImportFolder)
		QtCore.QObject.connect(self.actionImportMbox,QtCore.SIGNAL("triggered()"),self.slotImportMbox)
		QtCore.QObject.connect(self.actionPreferences,QtCore.SIGNAL("triggered()"),self.slotPreferences)
		# Admin Menu
		if self.getPref("Main","Admin",False):
			#QtCore.QObject.connect(self.actionCheckDB,QtCore.SIGNAL("triggered()"),self.slotCheckDB)
			pass
# ############################## DESCRIPTION ###################################
	def getIcon(self):
		return QtGui.QIcon(":/rPrj/icons/mail.png")
	def getName(self):
		return "Email"
	def getPanel(self,parent=None):
		"""Returns a QtGui.QWidget"""
		if self.widget is None:
			self.renderWidget(parent)
		return self.widget
	def getMenu(self,menubar):
		if self.menu is None:
			self.renderMenu(menubar)
		return self.menu
# ############################## PREFERENCES ###################################
	def SaveUI2Config(self):
		pass
	def RestoreUIFromConfig(self):
		pass
# ############################## LOGIC ###################################
	def connectToServer(self, server,  formFactory):
		self.server = server
		self.formFactory = formFactory
		# Exists?
		serverIDstring = server.getServerIDString()
		for c in self.connections:
			if c['name']!=serverIDstring:
				continue
			widget = c['widget']
			self.widget.setCurrentWidget( widget )
			return ""
		try:
			# Widget: create here your custom widget
			widget = emailwidget.EmailWidget(self.widget, "EmailWidget", formFactory,  server)
			print "EmailComponent.connectToServer: TODO"
			#widget.setServer(server)
			#widget.setFormFactory(formFactory)
			widget.setContainer(self)
			# Slots
			#QtCore.QObject.connect(widget,QtCore.SIGNAL("updatedTree()"),self.slotUpdatedTree)
			#QtCore.QObject.connect(widget,QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),self.slotSelected)
			QtCore.QObject.connect(widget,QtCore.SIGNAL('selectedFolder(QStandardItem,PyQt_PyObject)'),self.slotSelectedFolder)
			# tabBar
			if self.widget.count()==0:
				self.widget.tabBar().hide()
			else:
				self.widget.tabBar().show()
			# Add
			self.widget.addTab(widget, widget.getName() )
			self.widget.setCurrentWidget( widget )
			self.connections.append( { 'name':serverIDstring, 'widget':widget } )
		except Exception,e:
			print "ECCEZIONE:",e
			print "".join(traceback.format_tb(sys.exc_info()[2]))
		print "WebComponent.connectToServer: TODO"
# ############################## SLOTS ###################################
	def slotTabCloseRequested(self,index):
		if not self.Confirm("Close this connection?"):
			return
		serverwidget = self.widget.widget( index )
		self.widget.removeTab( index )
		# Exists?
		for i in range(len(self.connections)):
			if self.connections[i]['widget']!=serverwidget:
				continue
			self.connections.pop(i)
			break
		del serverwidget
	# Menu
	def slotPreferences(self):
		self.Alert("Preferences: TODO")
	def slotImportFolder(self):
		if len(self.connections)>0:
			self.widget.currentWidget().importFolder()
		else:
			self.Alert("Import Folder: required at least one connection to a DB!")
	def slotImportMbox(self):
		if len(self.connections)>0:
			self.widget.currentWidget().importMbox()
		else:
			self.Alert("Import Mbox: required at least one connection to a DB!")
	# Widget
	def slotSelectedFolder(self, item, dbe):
		self.SetStatusText("dbe=%s"%dbe)
		print "slotSelectedFolder: item=%s" % (item)
		print "slotSelectedFolder: dbe=%s" % (dbe)
