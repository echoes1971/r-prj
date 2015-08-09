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

pyuic4 explorerwidgetui.ui -o explorerwidgetui.py

Signals:
updatedTree()
selected(QStandardItem,PyQt_PyObject)

"""
import os,sys,traceback
from PyQt4 import QtCore,QtGui

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

import explorerwidgetui
from rprj.formulator import FAssociation

DEBUG_WIDGET=True

class ExplorerWidget(QtGui.QWidget):
	def __init__(self,parent,objectName):
		QtGui.QWidget.__init__(self,parent)
		# UI
		self.ui = explorerwidgetui.Ui_RprjExplorerWidget()
		self.ui.setupUi(self)
		self.setObjectName(objectName)
		# Tree Model
		self.modelTree = QtGui.QStandardItemModel( 0, 1 )
		self.modelTree.setHorizontalHeaderItem( 0, QtGui.QStandardItem("Name") )
		self.ui.treeView.setModel( self.modelTree )
		self.ui.treeView.setContextMenuPolicy(QtCore.Qt.CustomContextMenu)
		# Slots
		self.bind()
		# Logic
		self.server = None
		self.formFactory = None
		self._searchTypes = [] #'DBEObject']
		# Container
		self.container = None
		# DragDrop
		self.setAcceptDrops(True)
	def bind(self):
		# Search
		QtCore.QObject.connect(self.ui.searchFolder,QtCore.SIGNAL("returnPressed()"),self.slotSearchFolder)
		QtCore.QObject.connect(self.ui.searchButton,QtCore.SIGNAL("clicked()"),self.slotSearchFolder)
		# Tree View
		QtCore.QObject.connect(self.ui.treeView,QtCore.SIGNAL("clicked(QModelIndex)"),self.slotTreeViewClick)
		QtCore.QObject.connect(self.ui.treeView,QtCore.SIGNAL("doubleClicked(QModelIndex)"),self.slotTreeViewDoubleClick)
		QtCore.QObject.connect(self.ui.treeView,QtCore.SIGNAL("customContextMenuRequested(QPoint)"),self.slotTreeViewContextMenu)
		QtCore.QObject.connect(self.ui.treeView,QtCore.SIGNAL("activated(QModelIndex)"),self.slotTreeViewClick)
		# Model Tree
		QtCore.QObject.connect(self.modelTree,QtCore.SIGNAL("itemChanged(QStandardItem*)"),self.slotItemChanged)

# ############################## DRAG DROP ###################################
	def dragEnterEvent(self,event):
		"""called when a drag is in progress and the mouse enters the DropArea object"""
		childOver = self.childAt(event.pos())
		if self.ui.treeView.viewport() == self.childAt(event.pos()):
			event.acceptProposedAction()
	def dragMoveEvent(self,event):
		childOver = self.childAt(event.pos())
		if self.ui.treeView.viewport() == childOver:
			item = self._treeItemAtEventPosition(event.pos())
			if item is None:
				return
			self.runSearchChilds( item )
			mydata = item.data().toPyObject()
			event.acceptProposedAction()
	def _treeItemAtEventPosition(self,eventPosition):
		item = None
		childOver = self.childAt(eventPosition)
		if self.ui.treeView.viewport() == childOver:
			headersize=QtCore.QPoint(0,self.ui.treeView.header().size().height())
			deltapos = eventPosition - self.ui.treeView.pos() - headersize
			index = self.ui.treeView.indexAt(deltapos)
			item = self.modelTree.itemFromIndex(index)
			if not item is None and item.hasChildren() and not self.ui.treeView.isExpanded(item.index()):
				self.ui.treeView.setExpanded(item.index(),True)
			else:
				if DEBUG_WIDGET: print "ExplorerWidget._treeItemAtEventPosition: eventPosition=%s"%(eventPosition)
		return item
	def dragLeaveEvent(self,event):
		"""called when a drag is in progress and the mouse leaves the widget"""
		event.accept()
	def dropEvent(self,event):
		myformat = event.format()
		item = self._treeItemAtEventPosition(event.pos())
		if item is None:
			return
		mydata = item.data().toPyObject()
		_mytypename = mydata.getTypeName()
		if _mytypename=='DBEObject':
			_mytypename=mydata.getValue('classname')
		myform = self.formFactory.getInstanceByDBEName( _mytypename, dbmgr=self.server)
		if myform.getDetailFormsCount()==0:
			return
		myformlist = [myform.getDetail(x) for x in range(myform.getDetailFormsCount())]
		mimeData = event.mimeData()
		if myformat=='text/uri-list' or myformat=='x-special/gnome-icon-list':
			for u in mimeData.urls():
				myurl = "%s"%u.toString()
				if not myurl.startswith("file://"):
					continue
				myfilename = myurl.replace("file://","")
				if not os.path.exists(myfilename):
					continue
				import mimetypes
				mime,altro = mimetypes.guess_type( myurl )
				newdbe = self.server.getClazzByTypeName("DBEFile")(attrs={'filename':myfilename})
				if mime=='message/rfc822':
					newdbe = self.server.getClazzByTypeName("DBEMail")(attrs={'filename':myfilename})
				myform = self.formFactory.getInstanceByDBEName(newdbe.getTypeName(),dbmgr=self.server)
				newdbe.readFKFrom( mydata )
				newdbe.setDefaultValues(self.server)
				newdbe.setValue('father_id',mydata.getValue('id'))
				newdbe.setValue('name',"New %s"%newdbe.getTypeName())
				newdbe = self.server.insert(newdbe)
				icon = QtGui.QIcon()
				icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
				child = QtGui.QStandardItem( icon, "%s" % (newdbe.getValue('name') ) )
				child.setData( newdbe )
				item.appendRow( child )
		else:
			self.SetStatusText("DND: format '%s' not allowed." % myformat)
# ############################## PROPERTIES ###################################
	def setServer(self,server):
		self.server = server
		self.modelTree.server = server
	def setFormFactory(self,formFactory):
		self.formFactory = formFactory
		# Update UI
		pass
		# Cleaning comboForms
		for i in range(self.ui.comboForms.count()):
			self.ui.comboForms.removeItem(i)
		self.ui.comboForms.addItem("","DBEObject")
		formList = self.formFactory.getAllClassnames()
		for _f in formList:
			if _f in ['default','FBanned','FLog','FObject'
						,'FProjectPeopleRole','FProjectCompanyRole','FTodoTipo','FUserGroupAssociation']:
				continue
			if _f.find("Filter")>=0:
				continue
			myform = self.formFactory.getInstance(_f, dbmgr=self.server)
			if isinstance(myform,FAssociation):
				continue
			# comboForms
			icon = QtGui.QIcon()
			icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
			dbename = myform.getDBE(self.server).getTypeName()
			self.ui.comboForms.addItem(icon,myform.getListTitle(),dbename)
	def getName(self):
		d = self.server.getConnectionProvider().getDBType()
		u = "nobody"
		if not self.server.getDBEUser() is None:
			u = self.server.getDBEUser().getValue('login')
		h = "%s" % self.server.getConnectionProvider().getHost()
		if d=="SQLite":
			h = ( "%s" % self.server.getConnectionProvider().getDB() ).split( os.path.sep )[-1]
		if h.find("://")>0:
			_url = h.split("://")
			if len(_url)>1:
				h = "%s:%s" % ( _url[0],_url[1].split("/")[0] )
		return "%s@%s" % (u,h)
	def setSearchVisible(self, b):
		self.ui.widget.setVisible(b)
	def setSearchTypes(self,types):
		"""['DBEObject', 'DBEFile']"""
		self._searchTypes = types
	def getCurrentDBE(self):
		"""Returns the current selected DBE in the tree"""
		index = self.ui.treeView.currentIndex()
		#print "ExplorerWidget.getCurrentDBE: index=%s"%index
		myitem = self.modelTree.itemFromIndex(index)
		if myitem is None:
			return None
		mydbe = myitem.data().toPyObject()
		return mydbe
		#if mydbe.isNew():
		#	return
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
	def removeItem(self,myitem):
		myparent = myitem.parent()
		myrow = myitem.index().row()
		myparent.removeRow(myrow)
		self.ui.treeView.setCurrentIndex( self.modelTree.indexFromItem(myparent) )
	def updateTree(self,lista):
		self.modelTree.clear()
		# 2012.11.11: start.
		self.ids_items={}
		self.fatherids_items={}
		rootItem = self.modelTree.invisibleRootItem()
		self.updateItem(rootItem, lista)
		#for l in lista:
			#_mytypename = l.getTypeName()
			#if _mytypename=='DBEObject':
				#_mytypename=l.getValue('classname')
			#myform = self.formFactory.getInstanceByDBEName( _mytypename, dbmgr=self.server)
			#icon = QtGui.QIcon()
			#icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
			#item = QtGui.QStandardItem( icon, "%s" % (l.getValue('name')) )
			#item.setData(l)
			#desc = l.getValue('description')
			#if desc>'':
				#item.setToolTip( desc )
			#self.modelTree.appendRow( item )
		# 2012.11.11: end.
		self.modelTree.setHorizontalHeaderItem( 0, QtGui.QStandardItem("Name") )
		# TODO connect this signal to the first 8 lines of code in rPrj.updateTree
		self.emit(QtCore.SIGNAL("updatedTree()"))
	def updateItem(self,item,lista):
		rootItem = self.modelTree.invisibleRootItem()
		for l in lista:
			_mytypename = l.getTypeName()
			if _mytypename=='DBEObject':
				_mytypename=l.getValue('classname')
			myform = self.formFactory.getInstanceByDBEName( _mytypename, dbmgr=self.server)
			icon = QtGui.QIcon()
			icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
			child = QtGui.QStandardItem( icon, "%s" % (l.getValue('name') ) )
			child.setData(l)
			desc = l.getValue('description')
			if desc>'':
				child.setToolTip( desc )
			# 2012.11.11: start.
			self.ids_items[l.getValue('id')]=child
			if l.getValue('father_id')>'':
				if not self.fatherids_items.has_key(l.getValue('father_id')):
					self.fatherids_items[l.getValue('father_id')]=[]
				self.fatherids_items[l.getValue('father_id')].append(child)
			# 1. Check whether I'm son of somebody
			father_id = l.getValue('father_id')
			parentItem = None
			if father_id>'' and self.ids_items.has_key(father_id):
				parentItem = self.ids_items[father_id]
			#if father_id>'':
				#parentItem = self._findItemById(father_id)
			if not parentItem is None and not parentItem==rootItem and not parentItem==item:
				parentItem.appendRow( child )
				self.ui.treeView.setExpanded(parentItem.index(),True)
			else:
				item.appendRow( child )
			# 2. Check whether my childs are already here
			if self.fatherids_items.has_key(l.getValue('father_id')):
				mychilds = self.fatherids_items[l.getValue('father_id')]
				if len(mychilds)>0: print "TODO: che fare con i %s childs?"%len(mychilds)
			#item.appendRow( child )
			# 2012.11.11: end.
		self.ui.treeView.setExpanded(item.index(),True)
	def _findItemById(self,uuid,rootItem=None):
		if rootItem is None:
			rootItem = self.modelTree.invisibleRootItem()
		ret = self.modelTree.invisibleRootItem()
		for i in range(rootItem.rowCount()):
			_item = rootItem.child( i )
			try:
				_dbe = _item.data().toPyObject()
				if _dbe.getValue('id')==uuid:
					ret = _item
					break
				else:
					ret = self._findItemById(uuid,_item)
			except Exception,e:
				if DEBUG_WIDGET: print "_findItemById: ECCEZIONE=%s" % e
		return ret
# ############################## LOGIC ###################################
	def runSearchFolder(self,name,mydbetype='DBEObject'):
		if name != "%s" % self.ui.searchFolder.text():
			self.ui.searchFolder.setText(name)
		if mydbetype != "%s" % self.ui.comboForms.itemData( self.ui.comboForms.currentIndex() ).toPyObject():
			self.ui.comboForms.setCurrentIndex( self.ui.comboForms.findData(mydbetype) )
		attrname = 'name'
		if mydbetype=='DBEUser':
			attrname='login'
		cerca = self.server.getClazzByTypeName(mydbetype)(attrs={attrname:"%s" % name})
		lista = self.server.search(cerca, full_object=False)
		if len(lista)>0:
			if len(name)==0:
				tmp = []
				for dbe in lista:
					if mydbetype!="DBEObject" or dbe.getValue('father_id') is None or dbe.getValue('father_id')==0:
						tmp.append(dbe)
				lista = tmp
		self.updateTree(lista)
	def runSearch(self,name,mydbetype='DBEObject'):
		"""A better name..."""
		self.runSearchFolder(name,mydbetype)
	def runSearchChilds(self, item, refresh=False):
		mydata = item.data().toPyObject()
		if mydata.getValue('id') is None:
			return
		if refresh:
			item.removeRows( 0, item.rowCount() )
		if item.hasChildren():
			return
		# 2012.07.15: inizio.
		lista = []
		if len(self._searchTypes)>0:
			for dbename in self._searchTypes:
				cerca = self.server.getClazzByTypeName(dbename)(attrs={'father_id':mydata.id})
				lista.extend( self.server.search(cerca, full_object=False) )
		else:
			cerca = self.server.getClazzByTypeName('DBEObject')(attrs={'father_id':mydata.id})
			lista = self.server.search(cerca, full_object=False)
		#cerca = self.server.getClazzByTypeName('DBEObject')(attrs={'father_id':mydata.id})
		#lista = self.server.search(cerca, full_object=False)
		# 2012.07.15: fine.
		if len(lista)==0:
			return
		self.updateItem(item,lista)
	def runDelete(self,mydbe):
		"""returns: None=>OK, mydbe=>errors"""
		if mydbe.isNew():
			mydbe = None
		else:
			mydbe = self.server.delete(mydbe)
		return mydbe
# ############################## SLOTS ###################################
	def slotSearchFolder(self):
		name = "%s" % self.ui.searchFolder.text()
		mydbetype = "%s" % self.ui.comboForms.itemData( self.ui.comboForms.currentIndex() ).toPyObject()
		self.runSearchFolder(name,mydbetype)
	def slotTreeViewClick(self, index):
		item = self.modelTree.itemFromIndex(index)
		mydata = item.data().toPyObject()
		self.runSearchChilds( item )
		self.emit(QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),item,mydata)
	def slotTreeViewDoubleClick(self, index):
		pass
	def slotTreeViewContextMenu(self,pos):
		index = self.ui.treeView.currentIndex()
		myitem = self.modelTree.itemFromIndex(index)
		# Defaults
		mydbe = self.server.getClazzByTypeName("DBEFolder")()
		myform = self.formFactory.getInstance("FFolder",dbmgr=self.server)
		if not myitem is None:
			mydbe = myitem.data().toPyObject()
			if mydbe.getTypeName()=='DBEObject':
				myform = self.formFactory.getInstanceByDBEName(mydbe.getValue('classname'),dbmgr=self.server)
			else:
				myform = self.formFactory.getInstanceByDBEName(mydbe.getTypeName(),dbmgr=self.server)
		else:
			myitem = self.modelTree.invisibleRootItem()
		menu = QtGui.QMenu(self.ui.treeView)
		icon = QtGui.QIcon()
		icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/reload.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
		myaction = menu.addAction(icon,QtGui.QApplication.translate("TreeView", "Reload", None, QtGui.QApplication.UnicodeUTF8),self.slotReload)
		myaction.setData(None)
		# Build myformlist
		myformlist = []
		if myitem == self.modelTree.invisibleRootItem():
			formList = self.formFactory.getAllClassnames()
			for _f in formList:
				if _f in ['default','FBanned','FEventFilter','FLog','FObject'
							,'FProjectPeopleRole','FProjectCompanyRole','FTodoTipo','FUserGroupAssociation']:
					continue
				if _f.find("Filter")>=0:
					continue
				_myform = self.formFactory.getInstance(_f, dbmgr=self.server)
				if isinstance(_myform,FAssociation):
					continue
				myformlist.append(_myform)
		else:
			myformlist = [myform.getDetail(x) for x in range(myform.getDetailFormsCount())]
		myformlist = [x for x in myformlist if not isinstance(x,FAssociation)]
		if len(self._searchTypes)>0:
			tmpformlist = filter( lambda x: x.getDBE(self.server).getTypeName() in self._searchTypes, myformlist )
			myformlist = tmpformlist
		# Current form actions
		myactions = myform.getActions()
		if myactions.has_key('reload'): myactions.pop('reload')
		# Adding 'add child' actions
		if len(myformlist)>0 or len(myactions)>0:
			menu.addSeparator()
		if len(myformlist)>0:
			menuAdd = menu.addMenu(QtGui.QApplication.translate("TreeView", "Add"))
			for mydetail in myformlist:
				icon = QtGui.QIcon()
				icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%mydetail.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
				myaction = menuAdd.addAction(icon,QtGui.QApplication.translate("TreeView", "%s"%mydetail.getDetailTitle(), None, QtGui.QApplication.UnicodeUTF8))#,self.slotTest)
				myaction.setData( mydetail.getDBE(self.server) )
		# Adding custom form actions
		if len(myactions.keys())>0:
			menuActions = menu.addMenu(QtGui.QApplication.translate("TreeView", "Actions"))
			for k,v in myactions.items():
				if len(myactions[k])>2:
					icon = QtGui.QIcon()
					# 2012.10.16: start.
					icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/%s"%myactions[k][2])), QtGui.QIcon.Normal, QtGui.QIcon.Off)
					#icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myactions[k][2])), QtGui.QIcon.Normal, QtGui.QIcon.Off)
					# 2012.10.16: end.
					myaction = menuActions.addAction(icon,QtGui.QApplication.translate("TreeView", myactions[k][0], None, QtGui.QApplication.UnicodeUTF8),self.slotCustomAction)
				else:
					myaction = menuActions.addAction(QtGui.QApplication.translate("TreeView", myactions[k][0], None, QtGui.QApplication.UnicodeUTF8),self.slotCustomAction)
				myaction.setData( "%s"%k )
		menu.addSeparator()
		icon = QtGui.QIcon()
		icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/editdelete.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
		myaction = menu.addAction(icon,QtGui.QApplication.translate("TreeView", "Delete", None, QtGui.QApplication.UnicodeUTF8),self.slotDelete)
		myaction.setData(None)
		selectedAction = menu.exec_( QtGui.QCursor.pos() )
		if selectedAction is None:
			return
		newdbe = selectedAction.data().toPyObject()
		if newdbe is None or isinstance(newdbe,QtCore.QString):
			return
		myform = self.formFactory.getInstanceByDBEName(newdbe.getTypeName(),dbmgr=self.server)
		newdbe.readFKFrom( mydbe )
		newdbe.setDefaultValues(self.server)
		newdbe.setValue('father_id',mydbe.getValue('id'))
		newdbe.setValue('name',"New %s"%newdbe.getTypeName())
		icon = QtGui.QIcon()
		icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
		child = QtGui.QStandardItem( icon, "%s" % (newdbe.getValue('name') ) )
		child.setData( newdbe )
		myitem.appendRow( child )
		self.ui.treeView.setCurrentIndex( self.modelTree.indexFromItem(child) )
		self.emit(QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),child,newdbe)
	def slotItemChanged(self,item):
		if DEBUG_WIDGET: print "slotItemChanged: item=%s" % (item)
		mydbe = item.data().toPyObject()
		myparent = item.parent()
		if myparent is None:
			return
		mydbeparent = myparent.data().toPyObject()
		if DEBUG_WIDGET: print "slotItemChanged: mydbe.father_id=%s" % (mydbe.getValue('father_id'))
		if DEBUG_WIDGET: print "slotItemChanged: mydbeparent.id=%s" % (mydbeparent.getValue('id'))
		if mydbe.getValue('father_id')!=mydbeparent.getValue('id'):
			if DEBUG_WIDGET: print "slotItemChanged: PARENT CHANGED"
			try:
				if self.server.canWrite(mydbe) and self.server.canExecute(mydbeparent):
					# Yes, we can... change father
					mydbe.readFKFrom( mydbeparent )
					mydbe.setValue('father_id',mydbeparent.getValue('id'))
					mydbe = self.server.update(mydbe)
				else:
					# Change not allowed
					myoldparent = self._findItemById( mydbe.getValue('father_id') )
					if not myoldparent is None:
						__old = myparent.takeRow( item.row() )
						myoldparent.appendRow(__old)
			except Exception,e:
				# Change not possible
				myoldparent = self._findItemById( mydbe.getValue('father_id') )
				if not myoldparent is None:
					__old = myparent.takeRow( item.row() )
					myoldparent.appendRow(__old)
				self.Alert("Unable to move item!",e)
				raise e
	def slotReload(self):
		msg = QtGui.QApplication.translate("TreeView", "Confirm Reload?", None, QtGui.QApplication.UnicodeUTF8)
		if not self.Confirm( msg ):
			return
		index = self.ui.treeView.currentIndex()
		myitem = self.modelTree.itemFromIndex(index)
		mydbe = myitem.data().toPyObject()
		if mydbe.isNew():
			return
		cerca = self.server.getClazzByTypeName('DBEObject')(attrs={'id':"%s" % mydbe.getValue('id')})
		lista = self.server.search(cerca)
		if len(lista)!=1:
			self.Alert("Error: fetched %d results."%(len(lista)))
			return
		mydbe = lista[0]
		self.runSearchChilds( myitem, True )
		self.emit(QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),myitem,mydbe)
	def slotCustomAction(self):
		sender = self.sender()
		customActionString = "%s" % sender.data().toPyObject()
		customAction = "action%s%s" % (customActionString[0].upper(),customActionString[1:])
		index = self.ui.treeView.currentIndex()
		myitem = self.modelTree.itemFromIndex(index)
		mydbe = myitem.data().toPyObject()
		if customAction=='actionDownload':
			self.emit(QtCore.SIGNAL("actionDownload(PyQt_PyObject)"),mydbe)
			return
		try:
			getattr(self,customAction)(mydbe)
		except Exception,e:
			self.Alert("Error while executing '%s'." % sender.text(),e)
	def slotDelete(self):
		msg = QtGui.QApplication.translate("TreeView", "Confirm Delete?", None, QtGui.QApplication.UnicodeUTF8)
		if not self.Confirm( msg ):
			return
		index = self.ui.treeView.currentIndex()
		myitem = self.modelTree.itemFromIndex(index)
		myrow = myitem.index().row()
		myparent = myitem.parent()
		if myitem is None:
			return
		mydbe = myitem.data().toPyObject()
		mydbe = self.runDelete(mydbe)
		if not mydbe is None and not mydbe.isDeleted():
			self.SetStatusText("Delete: errors.")
			return
		myparent.removeRow(myrow)
		self.ui.treeView.setCurrentIndex( self.modelTree.indexFromItem(myparent) )
		self.previousItem = myparent
		mydbe = myparent.data().toPyObject()
		self.SetStatusText("Delete: OK")
		self.emit(QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),myparent,mydbe)
