#!/usr/bin/python

import sys
sys.path.insert(0,'..')

from PyQt4 import QtCore,QtGui,QtNetwork

# rPrj
from rprj.dblayer import *
from rprj.formulator import *
from rprj.formulator import apps_rc
from rprj.apps import dbschema,formschema


class TestFormulator(QtCore.QObject):
	def __init__(self,aFormList=[],aServerDefinition={}):
		QtCore.QObject.__init__(self)
		self.app = QtGui.QApplication([])
		self.myMainWindow = self.mainWindow()
		self.dbeFactory = dbschema.dbeFactory
		server = None
		if len(aServerDefinition)>0:
			s = aServerDefinition
			# Connection
			myconn = createConnection(s['url'],True)
			# Server
			server = dbschema.ObjectMgr(myconn, True, 'rra')
			server.setDBEFactory(self.dbeFactory)
			server.connect()
			server.login(s['user'],s['pwd'])
		# Init form factory
		self.formFactory = FormFactory()
		for f in formschema.formschema_type_list.keys():
			self.formFactory.register(f,formschema.formschema_type_list[f],server)
		formList = self.formFactory.getAllClassnames()
		formList.sort()
		if len(aFormList)>0:
			formList = aFormList
		for _f in formList:
			print _f
			if _f in ['default','FProjectCompany','FProjectPeople','FProjectProject','FUserGroupAssociation']:
				continue
			if _f.find("Filter")>=0:
				continue
			myform = self.formFactory.getInstance(_f,'','',"POST",server)
			self.addForm(myform)
			QtCore.QObject.connect(myform,QtCore.SIGNAL("clickedButton(PyQt_PyObject)"),self.slotClickedButton)
			# Carico un dato se presente
			cerca = myform.getDBE(server)
			if isinstance(cerca,dbschema.DBEObject):
				cerca.setDefaultValues(server)
			tmp = server.search(cerca)
			if len(tmp)>0:
				myform.setValues( tmp[0].getValuesDictionary() )
			else:
				myform.setValues( cerca.getValuesDictionary() )
			print "%s.buttons=%s" % (_f,myform.buttons)
	def exec_(self):
		self.myMainWindow.show()
		
		self.app.exec_()
	
	def mainWindow(self):
		ret = QtGui.QMainWindow()
		ret.setObjectName("MainWindow")
		ret.resize(800, 800)
		ret.setWindowTitle(":: R-Prj :: TEST ::")
		self.centralwidget = QtGui.QTabWidget(ret)
		#self.centralwidget = QtGui.QWidget(ret)
		self.centralwidget.setObjectName("centralwidget")
		#self.verticalLayout = QtGui.QVBoxLayout(self.centralwidget)
		#self.verticalLayout.setObjectName("verticalLayout")
		ret.setCentralWidget(self.centralwidget)
		return ret

	def addForm(self,myform):
		self.myform = myform
		# Widget definition
		w = myform.render(self.centralwidget)
		# add widget
		self.centralwidget.addTab(w, myform.getDetailTitle())
		#self.verticalLayout.addWidget(w,1)

	def slotClickedButton(self,s):
		sender = self.sender()
		print "slotClickedButton: sender=%s"%sender
		print "slotClickedButton: s=%s"%s
		tmp = s.split("_")
		nomeform=''
		if len(tmp)>1:
			nomeform=tmp[1]
		if len(tmp)>2:
			nomeazione="_".join(tmp[2:])
		print "slotClickedButton: nomeform=%s nomeazione=%s" % (nomeform,nomeazione)
		values = sender.getValues()
		for k in values.keys():
			print "slotClickedButton: sender[%s]=%s"%(k,values[k])

if __name__=='__main__':
	listaforms = []
	if len(sys.argv)>1:
		listaforms = sys.argv[1:]
	test = TestFormulator(listaforms,\
		{'url':'sqlite:./test.db','user':'roberto','pwd':'robertopwd'}\
		)
	test.exec_()

