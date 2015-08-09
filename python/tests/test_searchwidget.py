#!/usr/bin/python

"""

python -m cProfile ./test_searchwidget.py FProject FTodoFilter

"""
import os,sys
sys.path.insert(0,'..')

from PyQt4 import QtCore,QtGui,QtNetwork

# rPrj
from rprj.dblayer import *
from rprj.formulator import apps_rc
from rprj.formulator import FormFactory,searchwidget
from rprj.apps import dbschema,formschema

class TestWidget(QtCore.QObject):
	def __init__(self,aFormList=[],aServerDefinition={}):
		QtCore.QObject.__init__(self)
		self.app = QtGui.QApplication([])
		self.myMainWindow = self.mainWindow()
		
		self.prefs={}
		
		self.dbeFactory = dbschema.dbeFactory
		
		if len(aServerDefinition)>0:
			#print aServerDefinition
			s = aServerDefinition
			# Connection
			myconn = createConnection(s['url'],False)
			# Server
			server = dbschema.ObjectMgr(myconn, False, s['schema'])
			server.setDBEFactory(self.dbeFactory)
			server.connect()
			server.login(s['user'],s['pwd'])
		# Form Factory
		self.formFactory = FormFactory()
		for f in formschema.formschema_type_list.keys():
			self.formFactory.register(f,formschema.formschema_type_list[f],server)
		formList = self.formFactory.getAllClassnames()
		formList.sort()
		if len(aFormList)>0:
			formList = aFormList
		for _f in formList:
			print "TestWidget.__init__: _f=%s" % ( _f )
			if _f in ['default','FProjectCompany','FProjectPeople','FProjectProject','FUserGroupAssociation',]:
				continue
			myform = self.formFactory.getInstance(_f,'','',"POST",server)
			try:
				widget = searchwidget.SearchWidget(self.myMainWindow,"testFilter_%s"%_f,myform,True) #,False)
				widget.setServer(server)
				widget.setFormFactory(self.formFactory)
				widget.container=self
				QtCore.QObject.connect(widget,QtCore.SIGNAL("updatedList()"),self.slotUpdatedList)
				QtCore.QObject.connect(widget,QtCore.SIGNAL("selected(QListWidgetItem,PyQt_PyObject)"),self.slotSelected)
				self.centralwidget.addTab(widget,myform.getDetailTitle())
				#self.addForm(myform)
			except Exception,e:
				print "ECCEZIONE:",e
				print "".join(traceback.format_tb(sys.exc_info()[2]))
			# Slots
			#QtCore.QObject.connect(myform,QtCore.SIGNAL("clickedButton(PyQt_PyObject)"),self.slotClickedButton)
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
		# Main Window Setup
		self.progressBar = QtGui.QProgressBar(ret.statusBar())
		self.progressBar.setMaximumWidth(100)
		self.progressBar.setMaximumHeight(20)
		#self.progressBar.setHidden(True)
		ret.statusBar().addPermanentWidget(self.progressBar,1)
		return ret

# ############################## ... ###################################
	def Alert(self,msg, eccezione=None):
		#if self.app.thread()==QtCore.QThread.currentThread():
		if not eccezione is None:
			QtGui.QMessageBox.warning(\
				self.myMainWindow, "Attenzione",\
				"%s\n\nEccezione: %s\n\n%s" % (msg, eccezione,"".join(traceback.format_tb(sys.exc_info()[2])))\
			)
		else:
			QtGui.QMessageBox.information(self.myMainWindow, "Informazione", msg)
		#else:
			#self.emit(QtCore.SIGNAL('alert(PyQt_PyObject,PyQt_PyObject)'),msg,eccezione)
	def Confirm(self,msg):
		ret = QtGui.QMessageBox.question(self.myMainWindow, "Domanda", msg, QtGui.QMessageBox.Cancel|QtGui.QMessageBox.Ok)
		return ret==QtGui.QMessageBox.Ok
	def SetStatusText(self,msg):
		self.myMainWindow.statusBar().showMessage(msg)
	def SetProgress(self,perc):
		if perc <= 0:
			if self.progressBar.maximum()!=0:
				self.progressBar.setMaximum(0)
		else:
			if self.progressBar.maximum()!=100:
				self.progressBar.setMaximum(100)
			#QtCore.QObject.emit(self.progressBar,QtCore.SIGNAL("setValue(int)"),perc)
			self.progressBar.setValue(perc)
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
# ############################## SLOTS ###################################
	def slotUpdatedList(self):
		self.SetStatusText("List updated.")
	def slotSelected(self,item,dbe):
		self.SetStatusText("Item selected: %s - %s" % (dbe.getValue('name'),item) )

def main(argv=[]):
	listaforms = []
	if len(argv)>0:
		listaforms = argv
	test = TestWidget( listaforms, \
		#{'url':'http://www.kware.it/','user':'roberto','pwd':'roberto'}
		#{'url':'https://supervisor.webmailpec.it/pecmon/','user':'roberto','pwd':'roberto'}
		#{'url':'http://www.roccoangeloni.it/rproject/','user':'roberto','pwd':'roberto'}
		{'url':"sqlite:%s/.config/rprj/rprj.db"%os.path.expanduser("~"),'user':'roberto','pwd':'roberto','schema':'test'}
		#{'url':'sqlite:/home/roberto/.config/rprj/rprj.db','user':'roberto','pwd':'roberto','schema':'test'}
		#{'url':'sqlite:./test.db','user':'roberto','pwd':'roberto','schema':'test'}
		#{'url':'http://localhost/~roberto/rproject/','user':'roberto','pwd':'roberto'}
		#{'url':'mysql:localhost:rproject:root:','user':'roberto','pwd':'roberto'}
		#{'url':'postgresql:localhost:rproject:roberto:','user':'roberto','pwd':'roberto'}
		)
	test.exec_()

if __name__=='__main__':
	main(sys.argv[1:])
