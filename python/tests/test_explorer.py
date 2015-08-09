#!/usr/bin/python

import sys
sys.path.insert(0,'..')

from PyQt4 import QtCore,QtGui,QtNetwork

# rPrj
from rprj.dblayer import *
from rprj.formulator import FormFactory,explorerwidget
from rprj.apps import dbschema,formschema

class TestWidget(QtCore.QObject):
	def __init__(self,aServerList=[]):
		QtCore.QObject.__init__(self)
		self.app = QtGui.QApplication([])
		self.myMainWindow = self.mainWindow()
		
		self.prefs={}
		
		self.dbeFactory = dbschema.dbeFactory
		
		for s in aServerList:
			# Connection
			myconn = createConnection(s['url'],False)
			# Server
			server = dbschema.ObjectMgr(myconn, False, s['schema'])
			server.setDBEFactory(self.dbeFactory)
			server.connect()
			server.login(s['user'],s['pwd'])
			# Form Factory
			formFactory = FormFactory()
			for f in formschema.formschema_type_list.keys():
				formFactory.register(f,formschema.formschema_type_list[f],server)
			# Widget
			widget = explorerwidget.ExplorerWidget(self.myMainWindow,"TestWidget")
			widget.setServer(server)
			widget.setFormFactory(formFactory)
			widget.container=self
			# Slots
			QtCore.QObject.connect(widget,QtCore.SIGNAL("updatedTree()"),self.slotUpdatedTree)
			QtCore.QObject.connect(widget,QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),self.slotSelected)
			# Initial Search
			widget.runSearch("","DBEFolder") #("test",'DBEProject') #'DBEFolder')
			# Add
			self.centralwidget.addTab(widget, "%s::%s" % ( server.getConnectionProvider().getDBType(), widget.getName() ) )
	def exec_(self):
		self.myMainWindow.show()
		self.app.exec_()
	def mainWindow(self):
		ret = QtGui.QMainWindow()
		ret.setObjectName("MainWindow")
		ret.resize(300, 600)
		ret.move(0,0)
		ret.setWindowTitle(":: R-Prj :: TEST ::")
		self.centralwidget = QtGui.QTabWidget(ret)
		self.centralwidget.setTabPosition(QtGui.QTabWidget.South)
		self.centralwidget.setObjectName("centralwidget")
		ret.setCentralWidget(self.centralwidget)
		self.statusbar = QtGui.QStatusBar(ret)
		self.statusbar.setObjectName("statusbar")
		ret.setStatusBar(self.statusbar)
		return ret
	def slotUpdatedTree(self):
		self.SetStatusText("Updated tree")
	def slotSelected(self,item,dbe):
		self.SetStatusText("Selected item=%s dbe=%s" % (item,dbe) )

# ############################## ... ###################################
	def Alert(self,msg, eccezione=None):
		if not eccezione is None:
			QtGui.QMessageBox.warning(\
				self.myMainWindow, "Attenzione",\
				"%s\n\nEccezione: %s\n\n%s" % (msg, eccezione,"".join(traceback.format_tb(sys.exc_info()[2])))\
			)
		else:
			QtGui.QMessageBox.information(self.myMainWindow, "Informazione", msg)
	def Confirm(self,msg):
		ret = QtGui.QMessageBox.question(self.myMainWindow, "Domanda", msg, QtGui.QMessageBox.Cancel|QtGui.QMessageBox.Ok)
		return ret==QtGui.QMessageBox.Ok
	def SetStatusText(self,msg):
		self.myMainWindow.statusBar().showMessage(msg)
# ############################## PREFERENCES ###################################
	def getPref(self,section,option,default=None):
		if not self.prefs.has_key(section):
			self.prefs[section]={}
		if not self.prefs[section].has_key(option):
			self.prefs[section][option]=default
		return self.prefs[section][option]
	def setPref(self,section,option,valore):
		if not self.prefs.has_key(section):
			self.prefs[section]={}
		self.prefs[section][option]=valore


if __name__=='__main__':
	test = TestWidget( [\
		#{'url':'http://www.kware.it/','user':'roberto','pwd':'robertopwd'},\
		#{'url':'https://supervisor.webmailpec.it/pecmon/','user':'roberto','pwd':'robertopwd'},\
		#{'url':'http://www.roccoangeloni.it/rproject/','user':'roberto','pwd':'robertopwd'},\
		{'url':'sqlite:/home/roberto/Dropbox/rprj/namirial/namirial.db','user':'roberto','pwd':'echoestrade','schema':'rra'},\
		#{'url':'sqlite:/home/roberto/.config/rprj/rprj.db','user':'roberto','pwd':'robertopwd','schema':'test'},\
		#{'url':'http://localhost/~roberto/rproject/','user':'roberto','pwd':'robertopwd','schema':'test'},\
		#{'url':'mysql:localhost:rproject:root:','user':'roberto','pwd':'robertopwd','schema':'test'},\
		#{'url':'postgresql:localhost:rproject:roberto:','user':'roberto','pwd':'robertopwd','schema':'test'},\
		] )
	test.exec_()

