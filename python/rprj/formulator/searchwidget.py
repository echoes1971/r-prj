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

Signals:
updatedList()
selected(QListWidgetItem,PyQt_PyObject)

"""
import datetime,os,sys,traceback
from PyQt4 import QtCore,QtGui

try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

DEBUG_WIDGET=True

class SearchWidgetItemDelegate(QtGui.QItemDelegate):
	# http://doc.qt.digia.com/qt/model-view-programming.html
	# http://doc.qt.digia.com/qt/qabstractitemview.html#setItemDelegate
	# QItemDelegate(QObject parent=None)
	def __init__(self,myfield, parent=None):
		QtGui.QItemDelegate.__init__(self,parent)
		#self.myeditorclazz = myeditorclazz
		self.myfield = myfield
	
	# QWidget *createEditor(QWidget *parent, const QStyleOptionViewItem &option, const QModelIndex &index) const;
	def createEditor(self, parent, option, index):
		#self.myinstance = self.myeditorclazz()
		editor = self.myfield.InitField(parent)
		#editor.setMinimum(0)
		#editor.setMaximum(100)
		return editor
	
	# void setEditorData(QWidget *editor, const QModelIndex &index) const;
	def setEditorData(self, editor, index):
		value = index.model().data(index, QtCore.EditRole).toInt()
		#QSpinBox *spinBox = static_cast<QSpinBox*>(editor)
		spinBox = editor
		spinBox.setValue(value)

	# void setModelData(QWidget *editor, QAbstractItemModel *model, const QModelIndex &index) const;
	def setModelData(self, editor, model, index):
		value = editor.getValue()
		model.setData(index, value, QtCore.EditRole) # Qt.EditRole
	
	# void updateEditorGeometry(QWidget *editor, const QStyleOptionViewItem &option, const QModelIndex &index) const;
	def updateEditorGeometry(self, editor, option, modelindex):
		editor.field.setGeometry(option.rect)

class SearchWidget(QtGui.QWidget):
	def __init__(self,parent,objectName,filterForm,modifyFilter=True):
		QtGui.QWidget.__init__(self,parent)
		# UI
		self.setObjectName(objectName)
		self.resize(360, 360)
		self.setWindowTitle(QtGui.QApplication.translate("RprjSearchWidget", "Search", None, QtGui.QApplication.UnicodeUTF8))
		self.verticalLayout = QtGui.QVBoxLayout(self)
		self.verticalLayout.setMargin(0)
		self.verticalLayout.setObjectName("%s_verticalLayout"%objectName)
		# Filter
		self.widgetFilter = filterForm.render_filter(self)
		self.verticalLayout.addWidget(self.widgetFilter,0)
		# Button Search
		icon = QtGui.QIcon()
		icon.addPixmap(QtGui.QPixmap(":/rPrj/icons/reload.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
		self.buttonSearch = QtGui.QPushButton(icon,"",self)
		self.buttonSearch.setToolTip("Search")
		self.buttonSearch.setShortcut("Ctrl+R")
		QtCore.QObject.connect(self.buttonSearch,QtCore.SIGNAL("clicked()"),self.slotDoSearch)
		self.verticalLayout.addWidget(self.buttonSearch,0)
		self.filterForm = filterForm
		# List
		self.widgetList = QtGui.QTableView(self)
		self.widgetList.setSortingEnabled(True)
		self.columnNames = self.filterForm.getListColumnNames()
		self.model = QtGui.QStandardItemModel(0, len(self.columnNames)+1, self.widgetList)
		self.model.setHeaderData(0, QtCore.Qt.Horizontal, QtCore.QVariant( "Type" ))
		for i in range(len(self.columnNames)):
			self.model.setHeaderData(i+1, QtCore.Qt.Horizontal, QtCore.QVariant( self.filterForm.getField( self.columnNames[i] ).GetTitle() ))
			if self.columnNames[i]=='stato':
				myfieldinstance = self.filterForm.getField( self.columnNames[i] )
				print "myfieldinstance:",myfieldinstance
				myitemdelegate = SearchWidgetItemDelegate(myfieldinstance)
				self.widgetList.setItemDelegateForColumn( i+1, myitemdelegate )
			# TEST
			#if self.columnNames[i]=='stato':
				#myitemdelegate = SearchWidgetItemDelegate(myfield)
				#self.widgetList.setItemDelegateForColumn( i+1, myitemdelegate )
		self.widgetList.setModel( self.model )
		self.verticalLayout.addWidget(self.widgetList,1)
		# Slots
		self.bind()
		# Logic
		self.server = None
		self.formFactory = None
		self.setModifyFilter(modifyFilter)
		self._selectedDBE = None
		# Container
		self.container = None
		# DragDrop
		self.setAcceptDrops(True)
	def bind(self):
		# Search
		QtCore.QObject.connect(self.widgetFilter,QtCore.SIGNAL("doSearch()"),self.slotDoSearch)
		# List Widget
		QtCore.QObject.connect(self.widgetList,QtCore.SIGNAL("clicked(QModelIndex*)"),self.slotItemClicked)
		QtCore.QObject.connect(self.widgetList,QtCore.SIGNAL("doubleClicked(QModelIndex*)"),self.slotItemDoubleClicked)

# ############################## PROPERTIES ###################################
	def setServer(self,server):
		self.server = server
	def getServer(self):
		return self.server
	def setFormFactory(self,formFactory):
		self.formFactory = formFactory
		# Update UI
		pass
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
	def setModifyFilter(self,b):
		self._modifyFilter = b
		self.widgetFilter.setVisible( b )
		self.buttonSearch.setVisible( not b )
	def modifyFilter(self):
		return self._modifyFilter
	def setSelectedDBE(self,dbe):
		self._selectedDBE = dbe
	def selectedDBE(self):
		return self._selectedDBE
	def setValues(self, values):
		self.filterForm.setValues(values)
	def getValues(self, values):
		return self.filterForm.getValues()
	def cleanValues(self):
		return self.filterForm.clean()
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
	def updateList(self,lista):
		self.model.removeRows(0, self.model.rowCount())
		for l in lista:
			_mytypename = l.getTypeName()
			if _mytypename=='DBEObject':
				_mytypename=l.getValue('classname')
			myform = self.formFactory.getInstanceByDBEName( _mytypename, dbmgr=self.server)
			myform.setValues( l.getValuesDictionary() )
			row = self.model.rowCount()
			self.model.insertRow(row)
			self._formToRow(row,myform)
		self.widgetList.resizeColumnsToContents()
		self.widgetList.resizeRowsToContents()
		self.emit(QtCore.SIGNAL("updatedList()"))
	def _formToRow(self,row,myform):
		readOnlyColumnNames = myform.getDetailReadOnlyColumnNames()
		icon = QtGui.QIcon()
		icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/%s"%myform.getDetailIcon())), QtGui.QIcon.Normal, QtGui.QIcon.Off)
		self.model.setData(self.model.index(row, 0), u"%s"%myform.getDetailTitle() )
		self.model.item(row,0).setEditable(False)
		self.model.item(row,0).setIcon( icon )
		for i in range(len(self.columnNames)):
			myfield = myform.getField(self.columnNames[i])
			myvalue = "%s"%myfield.getValue()
			self.model.setData(self.model.index(row, i+1), myvalue )
			self.model.setData(self.model.index(row, i+1), QtCore.QVariant(row), 32 )
			if self.columnNames[i] in readOnlyColumnNames:
				self.model.item(row,i+1).setEditable(False)
			else:
				self.model.item(row,i+1).setEditable(True)
# ############################## LOGIC ###################################
	def runSearch(self,searchdbe):
		"""A better name..."""
		self.SetProgress(33)
		lista = self.server.search(searchdbe)
		self.SetProgress(66)
		self.updateList(lista)
		self.SetProgress(100)
# ############################## SLOTS ###################################
	def slotDoSearch(self):
		cerca = self.filterForm.getDBE(self.server)
		tmp_values = self.filterForm.getValues()
		searchValues = {}
		for k,v in tmp_values.items():
			myv=v
			if isinstance(myv,datetime.datetime):
				myv = self.server.datetime2string(myv)
			elif isinstance(myv,QtCore.QString):
				myv = "%s" % v
			else:
				myv = "%s" % v
			if myv.find('0000-00-00 00:00')>=0: continue
			searchValues[k]=myv
		cerca.setValuesDictionary( searchValues )
		self.runSearch(cerca)
	def slotItemClicked(self,item):
		pass
	def slotItemDoubleClicked(self,item):
		mydata = item.data(QtCore.Qt.UserRole).toPyObject()
		self.setSelectedDBE(mydata)
		self.emit(QtCore.SIGNAL("selected(QListWidgetItem,PyQt_PyObject)"),item,mydata)
