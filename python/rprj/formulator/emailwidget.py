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
Email Widget


Signals:
selectedFolder(QStandardItem,PyQt_PyObject)

"""

import codecs, os, sys, traceback
from PyQt4 import QtCore, QtGui

import explorerwidget, searchwidget

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

try:
	sys.stdout = codecs.lookup('utf8')[-1](sys.stdout)
	sys.stderr = codecs.lookup('utf8')[-1](sys.stderr)
except Exception,e:
	pass

EMAIL_FOLDERS_NAME = "EmailFolders"
EMAIL_FOLDERS_DEFAULTS = ["Inbox", "Archive", "Outbox", "Sent", "Trash", "Drafts"]

class EmailWidget(QtGui.QWidget):
	def __init__(self,parent,objectName, formFactory, server):
		QtGui.QWidget.__init__(self,parent)
		w = self
		w.setAutoFillBackground(False)
		w.setObjectName("w_%s"%objectName)
		self.layout = QtGui.QVBoxLayout(self)
		self.layout.setMargin(0)
		self.layout.setObjectName(_fromUtf8("layout"))
		# Add widgets here
		self.splitter = QtGui.QSplitter(self)
		self.splitter.setOrientation(QtCore.Qt.Horizontal)
		self.splitter.setObjectName(_fromUtf8("splitter"))
		self.layout.addWidget(self.splitter)
		# Left pane
		self.explorer = explorerwidget.ExplorerWidget(self.splitter,"explorer_%s"%objectName)
		self.explorer.container = self
		self.explorer.setSearchVisible(False)
		# Right pane
		self.splitter2 = QtGui.QSplitter(self.splitter)
		self.splitter2.setOrientation(QtCore.Qt.Vertical)
		self.splitter2.setObjectName(_fromUtf8("splitter2"))
		filterForm = formFactory.getInstance( "FMail", dbmgr=server )
		self.search = searchwidget.SearchWidget(self.splitter2,"search_%s"%objectName,  filterForm)
		self.search.container = self
		mailForm = formFactory.getInstance( "FMail", dbmgr=server )
		self.mailForm = mailForm.render(self.splitter2)
		self.mailForm.container = self
		self.mailForm.setEnabled(False)
		# Splitter size
		self.splitter.setSizes([200, 600])
		# File dialog
		self.fileDialog = QtGui.QFileDialog(parent,QtGui.QApplication.translate(\
			"Rprj", "Select folder...", None, \
			QtGui.QApplication.UnicodeUTF8),os.path.realpath("."))
		# Mbox dialog
		self.mboxDialog = QtGui.QFileDialog(parent,QtGui.QApplication.translate(\
			"Rprj", "Select mbox...", None, \
			QtGui.QApplication.UnicodeUTF8),os.path.realpath(".") )
		# Slots
		self.bind()
		# Container
		self.container = None
		# Logic
		self.server = None
		self.formFactory = None
		self.setServer(server)
		self.setFormFactory(formFactory)
		self.explorer.setSearchTypes( ['DBEFolder', ] )
	def bind(self):
		# Bind signals and slots here
		QtCore.QObject.connect(self.explorer,QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),self.slotExplorerSelectedItem)
# ############################## PROPERTIES ###################################
	def setContainer(self,c):
		self.container = c
		self.mboxDialog.setDirectory( self.getPref( self.container.__class__.__name__, "mboxDirectory", os.path.realpath(".") )  )
		self.fileDialog.setDirectory( self.getPref(self.container.__class__.__name__,  "mboxDirectory", os.path.realpath(".") )  )
	def setServer(self,server):
		self.server = server
		self.explorer.setServer(server)
		self.search.setServer(server)
	def getServer(self):
		return self.server
	def setFormFactory(self,formFactory):
		self.formFactory = formFactory
		self.explorer.setFormFactory(formFactory)
		self.search.setFormFactory(formFactory)
		# Update UI
		self.checkEmailFolders()
		self.explorer.runSearch(EMAIL_FOLDERS_NAME, "DBEFolder")
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
	def checkEmailFolders(self):
		"""Checks wheter EmailFolders exists, if not creates the basic structure"""
		cerca = self.server.getClazzByTypeName("DBEFolder")(attrs={'name':"%s" % EMAIL_FOLDERS_NAME,  'owner':self.server.getDBEUser().getValue('id')})
		lista = self.server.search(cerca, uselike=False, full_object=False)
		if len(lista)>0:
			return
		mainfolder = self.server.getClazzByTypeName("DBEFolder")(attrs={'name':EMAIL_FOLDERS_NAME})
		mainfolder = self.server.insert(mainfolder)
		for childname in EMAIL_FOLDERS_DEFAULTS:
			childfolder = self.server.getClazzByTypeName("DBEFolder")(attrs={'name':childname, 'father_id':mainfolder.getValue('id')})
			self.server.insert( childfolder )
	def _importFolder(self, parentdbe, path,  recursive=True):
		mypath=path
		if not mypath.endswith(os.path.sep): mypath="%s%s" % (mypath,os.path.sep)
		tmp = os.listdir( mypath )
		not_inserted = 0
		for f in tmp:
			try:
				if f==".DS_Store": continue
			except Exception,e:
				pass
			tmpfilename = "%s%s" % (mypath, f)
			if os.path.isdir( tmpfilename ) and recursive:
				nuova = self.server.getClazzByTypeName("DBEFolder")()
				nuova.setValue('father_id', parentdbe.getValue('id'))
				nuova.setValue('name', f)
				tmp = self.server.search(nuova, uselike=False)
				if len(tmp)==1:
					nuova = tmp[0]
				else:
					nuova = self.server.insert(nuova)
				self._importFolder(nuova, tmpfilename)
			else:
				nuova = self.server.getClazzByTypeName("DBEMail")()
				nuova.setValue('father_id', parentdbe.getValue('id'))
				nuova.setValue('filename', tmpfilename)
				try:
					nuova = self.server.insert(nuova)
				except Exception, e:
					not_inserted += 1
		if not_inserted>0:
			self.Alert("Not imported %d emails!" % not_inserted)
	def _importMbox(self, parentdbe, path):
		import mailbox
		mypath=path
		no_msg_id = 0
		processed = 0
		not_inserted = 0
		mymailbox = mailbox.mbox(mypath)
		for message in mymailbox:
			mymsgid = None
			if message.has_key('Message-ID'):
				mymsgid = message['Message-ID']
			else:
				mymsgid = "%s_%s" % (no_msg_id, message['Thread-Index'])
				no_msg_id += 1
			tmpfilename = "%s.eml"%mymsgid
			nuova = None
			try:
				tmpfile = file(tmpfilename, 'wb').write( message.as_string() )
				nuova = self.server.getClazzByTypeName("DBEMail")()
				nuova.setValue('father_id', parentdbe.getValue('id'))
				nuova.setValue('filename', tmpfilename)
				nuova = self.server.insert(nuova)
				os.remove(tmpfilename)
			except Exception, e:
				not_inserted += 1
			processed += 1
			self.SetProgress( (processed*100)/ len(mymailbox) )
		self.SetProgress( 100 )
		if not_inserted>0:
			self.Alert("Not imported %d emails!" % not_inserted)
	def importFolder(self):
		# Target folder
		myfolder = self.explorer.getCurrentDBE()
		if myfolder is None:
			self.Alert("Select a destination folder on the tree view!")
			return
		# Select import folder
		fileNames = []
		if self.fileDialog.exec_():
			fileNames = ["%s"%x for x in self.fileDialog.selectedFiles()]
		if len(fileNames)!=1:
			return
		self.setPref(self.container.__class__.__name__, "fileDirectory", self.mboxDialog.directory().absolutePath())
		for mypath in fileNames:
			self._importFolder(myfolder, mypath)
		self.explorer.slotReload()
	def importMbox(self):
		# Target folder
		myfolder = self.explorer.getCurrentDBE()
		if myfolder is None:
			self.Alert("Select a destination folder on the tree view!")
			return
		# Select import mbox file
		fileNames = []
		if self.mboxDialog.exec_():
			fileNames = ["%s"%x for x in self.mboxDialog.selectedFiles()]
		if len(fileNames)<1:
			return
		self.setPref(self.container.__class__.__name__, "mboxDirectory", self.mboxDialog.directory().absolutePath())
		for mypath in fileNames:
			self._importMbox(myfolder, mypath)
		self.explorer.slotReload()
# ############################## SLOTS ###################################
	def slotExplorerSelectedItem(self, item, dbe):
		self.search.cleanValues()
		self.search.setValues( { 'father_id': dbe.getValue('id') } )
		self.server.verbose=False
		self.search.slotDoSearch()
		self.emit(QtCore.SIGNAL("selectedFolder(QStandardItem,PyQt_PyObject)"),item,dbe)
