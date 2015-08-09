#!/usr/bin/python
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
"""

from ConfigParser import ConfigParser
import os,sys,traceback
from PyQt4 import QtCore, QtGui, Qt
try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s


from rprj.dblayer import DBMgr,createConnection
from rprj.formulator import FormFactory, apps_rc
from rprj.apps import mainwindow,  logindialog, dbschema, formschema

RPRJ_CONFIG = "%s%s.config%srprj%srprj.cfg" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
RPRJ_WINDOW_TITLE = ":: R-Prj ::"
RPRJ_LOCAL_DB = "%s%s.config%srprj%srprj.db" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
RPRJ_PLUGINS = "%s%s.config%srprj%splugins" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )

class RApp(QtCore.QObject):
    def __init__(self):
        QtCore.QObject.__init__(self)
        self.LoadConfig(RPRJ_CONFIG)
        self.SetupPlugins(RPRJ_PLUGINS)
        self.localpath = os.path.realpath(os.path.dirname(__file__))
        # Extra Components (web browser, email client, rss reader, ecc.)
        self.components = {}
        # UI
        self.app = QtGui.QApplication([])
        self.app.setApplicationName(RPRJ_WINDOW_TITLE)
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/rprj.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.app.setWindowIcon(icon)
        self.loadStyle()
        self.myMainWindow = None
        if self.getPref("UI","Frameless",False):
            self.myMainWindow = QtGui.QMainWindow(None,QtCore.Qt.FramelessWindowHint)
            self.myMainWindow.setAttribute(QtCore.Qt.WA_TranslucentBackground,True)
        else:
            self.myMainWindow = QtGui.QMainWindow()
        self.ui=mainwindow.Ui_MainWindow()
        self.ui.setupUi(self.myMainWindow)
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/rprj.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.myMainWindow.setWindowIcon(icon)
        # Main Window Setup
        self.progressBar = QtGui.QProgressBar(self.myMainWindow.statusBar())
        self.progressBar.setMaximumWidth(100)
        self.progressBar.setMaximumHeight(20)
        self.myMainWindow.statusBar().addPermanentWidget(self.progressBar,1)
        tmpMainWindowSize = self.getPref("UI","mainWindowSize",[])
        if len( tmpMainWindowSize )==2:
            self.myMainWindow.resize(tmpMainWindowSize[0],tmpMainWindowSize[1])
        else:
            self.myMainWindow.resize(800,800)
        # Login Dialog
        self.loginDialog = QtGui.QDialog(self.myMainWindow)
        self.uiLogin=logindialog.Ui_LoginDialog()
        self.uiLogin.setupUi(self.loginDialog)
        self._loginShowOptions(False)
        # Logic
        self.server = None
        self.dbeFactory = dbschema.dbeFactory
        # Components
        componentmodules = self.getPref("Main","Components",["rprjcomponent"])
        for module in componentmodules:
            try:
                exec("from rprj.apps import %s" % module)
            except:
                exec("import %s" % module)
            clazzname = None
            exec("""
for x in dir(%s):
    if x.find('Component')>0:
        clazzname=x
        break
"""%module)
            exec( "self.addComponent( %s.%s(self.config,self) )" % (module, clazzname) )
        if self.getPref("UI","Frameless","False"):
            self.mybtn_minimize = QtGui.QPushButton(self.ui.menubar)
            self.mybtn_minimize.setText("_")
            self.mybtn_minimize.setObjectName("btn_minimize")
            self.mybtn_maximize = QtGui.QPushButton(self.ui.menubar)
            self.mybtn_maximize.setText("O")
            self.mybtn_maximize.setObjectName("btn_maximize")
            self.mybtn_close = QtGui.QPushButton(self.ui.menubar)
            self.mybtn_close.setText("X")
            self.mybtn_close.setObjectName("btn_close")
            # FIXME this is horrible!!!
            self.mybtn_minimize.move(50,0)
            self.mybtn_maximize.move(30,0)
            self.mybtn_close.move(10,0)
        # Binds
        self.bind()
    def bind(self):
        # Menu
        # Main Menu
        QtCore.QObject.connect(self.ui.actionLogin,QtCore.SIGNAL("triggered()"),self.slotLogin) # 2012.06.16
        QtCore.QObject.connect(self.ui.actionReload_Style,QtCore.SIGNAL("triggered()"),self.slotReloadStylesheet)
        QtCore.QObject.connect(self.ui.actionQuit,QtCore.SIGNAL("triggered()"),self.slotQuit)
        # Login Dialog
        QtCore.QObject.connect(self.uiLogin.buttonBox,QtCore.SIGNAL(_fromUtf8("accepted()")),self.slotLoginOk)
        QtCore.QObject.connect(self.uiLogin.buttonBox, QtCore.SIGNAL(_fromUtf8("rejected()")), self.slotLoginCancel)
        QtCore.QObject.connect(self.uiLogin.pushButton, QtCore.SIGNAL(_fromUtf8("clicked()")), self.slotLoginMoreOptions)
        # Frameless
        if self.getPref("UI","Frameless","False"):
            QtCore.QObject.connect(self.mybtn_minimize,QtCore.SIGNAL("clicked()"),self.slotMinimizeWindow)
            QtCore.QObject.connect(self.mybtn_maximize,QtCore.SIGNAL("clicked()"),self.slotMaximizeWindow)
            QtCore.QObject.connect(self.mybtn_close,QtCore.SIGNAL("clicked()"),self.slotQuit)
    def _loginShowOptions(self,b):
        self.uiLogin.label_3.setVisible(b)
        self.uiLogin.label_4.setVisible(b)
        self.uiLogin.lineEdit_schema.setVisible(b)
        self.uiLogin.comboBox.setVisible(b)
    def loadStyle(self):
        try:
            style = self.getPref("UI","Style","")
            if len(style)==0:
                return
            ss = file(style).read()
            frameless = self.getPref("UI","Frameless",False)
            if frameless:
                ss = "%s%s" % (ss,"""
QMenuBar { padding-left: 65px; }
""")
            else:
                ss = "%s%s" % (ss,"""
QMenuBar { border-radius: 0px; }
QStatusBar { border-radius: 0px; }
QStatusBar QSizeGrip { border-bottom-right-radius: 0px; }
""")
            self.app.setStyleSheet(ss)
        except Exception,e:
            pass
    def exec_(self):
        self.initialize()
        self.ui.dockWidget.setWindowTitle("R-Project")
        self.myMainWindow.show()
        self.SetStatusText("OK")
        self.app.exec_()
    def initialize(self):
        self.RestoreUIFromConfig()
    def finalize(self):
        # TODO
        # Save GUI
        self.SaveUI2Config()
        # Save Config
        self.SaveConfig()
    def addComponent(self,c):
        tmp_page = c.getPanel()
        self.components[c.getName()]=[c,tmp_page]
        
        QtGui.QListWidgetItem( c.getIcon(), c.getName(), self.ui.listWidget )
        tmp_page = self.components[c.getName()][1]
        self.ui.stackedWidget.addWidget(tmp_page)
        if self.ui.dockWidget.isHidden() and len(self.components)>1:
            self.ui.dockWidget.show()
        elif not self.ui.dockWidget.isHidden() and len(self.components)==1:
            self.ui.dockWidget.hide()
        menu = c.getMenu(self.ui.menubar)
    def SetupPlugins(self, plugins_path):
        self._checkPath(plugins_path)
        if not plugins_path in sys.path:
            sys.path.append(plugins_path)
# ############################## ... ###################################
    def Alert(self,msg, eccezione=None):
        if self.app.thread()==QtCore.QThread.currentThread():
            if not eccezione is None:
                QtGui.QMessageBox.warning(\
                    self.myMainWindow, "Attenzione",\
                    "%s\n\nEccezione: %s\n\n%s" % (msg, eccezione,"".join(traceback.format_tb(sys.exc_info()[2])))\
                )
            else:
                QtGui.QMessageBox.information(self.myMainWindow, "Informazione", msg)
        else:
            self.emit(QtCore.SIGNAL('alert(PyQt_PyObject,PyQt_PyObject)'),msg,eccezione)
    def Confirm(self,msg):
        ret = QtGui.QMessageBox.question(self.myMainWindow, "Domanda", msg, QtGui.QMessageBox.Cancel|QtGui.QMessageBox.Ok)
        return ret==QtGui.QMessageBox.Ok
    def SetStatusText(self,msg):
        self.ui.statusbar.showMessage(msg)
    def SetProgress(self,perc):
        if perc <= 0:
            if self.progressBar.maximum()!=0:
                self.progressBar.setMaximum(0)
        else:
            if self.progressBar.maximum()!=100:
                self.progressBar.setMaximum(100)
            self.progressBar.setValue(perc)
# ############################## PREFERENCES ###################################
    def SaveUI2Config(self):
        for l in self.components.values():
            l[0].SaveUI2Config()
        # Main Window
        # TODO self.setPref("UI","searchFolder",self.ui.searchFolder.text())
        # TODO self.setPref("UI","comboForms.currentIndex",self.ui.comboForms.currentIndex())
        self.setPref("UI","maximized",self.myMainWindow.isMaximized())
        if not self.myMainWindow.isMaximized():
            self.setPref("UI","mainWindowSize",[self.myMainWindow.width(),self.myMainWindow.height()])
        # Login Dialog
        self.setPref("UILogin","login",self.uiLogin.lineEdit.text())
        self.setPref("UILogin","pwd",self.uiLogin.lineEdit_2.text())
        self.setPref("DB","schema",self.uiLogin.lineEdit_schema.text())
        self.setPref("UILogin","comboBox.currentIndex",self.uiLogin.comboBox.currentIndex())
        self.setPref("UILogin","comboBox.currentText",self.uiLogin.comboBox.currentText())
    def RestoreUIFromConfig(self):
        for l in self.components.values():
            l[0].RestoreUIFromConfig()
        # Main Window
        # TODO self.ui.searchFolder.setText(self.getPref("UI","searchFolder",""))
        # TODO self.ui.comboForms.setCurrentIndex( self.getPref("UI","comboForms.currentIndex","0"))
        if self.getPref("UI","maximized",False):
            self.myMainWindow.showMaximized()
        # Login Dialog
        self.uiLogin.lineEdit.setText(self.getPref("UILogin","login",""))
        self.uiLogin.lineEdit_2.setText(self.getPref("UILogin","pwd",""))
        self.uiLogin.lineEdit_schema.setText( self.getPref('DB','schema','rprj') )
        serverlist = self.getPref("UILogin","servers",["sqlite:%s"%RPRJ_LOCAL_DB,"http://localhost/rprj/","mysql:localhost:rproject:root:","postgresql:localhost:rproject:roberto:"])
        for s in serverlist:
            self.uiLogin.comboBox.addItem(s)
        self.uiLogin.comboBox.setCurrentIndex( self.getPref("UILogin","comboBox.currentIndex","0"))
        self.uiLogin.comboBox.setEditText(self.getPref("UILogin","comboBox.currentText",serverlist[0]))
    def getPref(self,section,option,default=None):
        if isinstance(default,bool): default="%s" % (default)
        if not self.config.has_section(section):
            self.config.add_section(section)
        if not self.config.has_option(section,option):
            self.config.set(section,option,default)
        tmp = self.config.get(section,option,default)
        ret = tmp
        try:
            pycode = "ret = %s" % (tmp)
            exec(compile(pycode, "myeval.py","exec"))
        except Exception,e:
            pass
        return ret
    def setPref(self,section,option,valore):
        if not self.config.has_section(section):
            self.config.add_section(section)
        if isinstance( valore, bool ): valore ="%s"%(valore)
        self.config.set(section,option,valore)
    def _checkPath(self,mycfgfile):
        dirs = mycfgfile.split(os.path.sep)[:-1]
        tmpdir = []
        for d in dirs:
            if d == "":
                continue
            tmpdir.append(d)
            tmpstr = "%s%s" % (os.path.sep, os.path.sep.join(tmpdir))
            if sys.platform=='win32':
                tmpstr = os.path.sep.join(tmpdir)
            if not os.path.exists(tmpstr):
                os.mkdir(tmpstr)
        return os.path.exists(tmpstr)
    def LoadConfig(self,config_file):
        self.config_filename = config_file
        self._checkPath(self.config_filename)
        self.config = ConfigParser()
        try:
            self.config.readfp(file(self.config_filename,'rt'))
        except Exception,e:
            print e
    def SaveConfig(self):
        self._checkPath(self.config_filename)
        self.config.write( file(self.config_filename,'w') )
# ############################## LOGIC ###################################
    def connectToServer(self,url,user,pwd):
        myurl = url
        if myurl.startswith("http"):
            if myurl.endswith("/xmlrpc_server.php"):
                pass # OK
            elif myurl.endswith("/"):
                myurl = "%sxmlrpc_server.php" % ( myurl )
            else:
                myurl = "%s/xmlrpc_server.php" % ( myurl )
        # Connection
        myconn = createConnection(myurl,self.getPref("Debug","connection",False)) #True)
        # Server
        self.server = dbschema.ObjectMgr(myconn, self.getPref("Debug","server",False), self.getPref('DB','schema','rprj'))
        self.server.setDBEFactory(self.dbeFactory)
        self.server.connect()
        myerr = ""
        #print self.server.ping()
        try:
            self.server.login(user,pwd)
        except Exception,e:
            myerr = "%s" % e
            print myerr
            print "".join(traceback.format_tb(sys.exc_info()[2]))
        if not self.server.user is None:
            # New server?
            serverlist = self.getPref("UILogin","servers",["http://localhost/rprj/","sqlite:%s"%RPRJ_LOCAL_DB,"mysql:localhost:rproject:root:","postgresql:localhost:rproject:roberto:"])
            if not url in serverlist:
                serverlist.insert(0,url)
                self.setPref("UILogin","servers",serverlist)
            # Form Factory
            formFactory = FormFactory()
            for f in formschema.formschema_type_list.keys():
                formFactory.register(f,formschema.formschema_type_list[f], self.server)
            for k in self.components.keys():
                mycomponent,  mypanel = self.components[k]
                mycomponent.connectToServer(self.server, formFactory)
            return "Logged in: %s (%s)" % (self.server.user.getValue('login'), self.server.user.getValue('fullname'))
        elif len(myerr)>0:
            return "Login error: %s."%myerr
        else:
            return "Login error!"

# ############################## SLOTS ###################################
    def slotLoginOk(self):
        url = "%s" % self.uiLogin.comboBox.currentText()
        user = "%s" % self.uiLogin.lineEdit.text()
        pwd = "%s" % self.uiLogin.lineEdit_2.text()
        schema = "%s" % self.uiLogin.lineEdit_schema.text()
        self.setPref("DB","schema",schema)
        msg = self.connectToServer(url,user,pwd)
        self.SetStatusText(msg)
    def slotLoginCancel(self):
        pass
    def slotLogin(self):
        if not self.loginDialog.show():
            self.loginDialog.show()
        else:
            self.loginDialog.hide()
    def slotLoginMoreOptions(self):
        self._loginShowOptions( self.uiLogin.pushButton.isChecked() )

    def slotQuit(self):
        if not self.Confirm("Confirm Quit?"):
            return
        self.myMainWindow.close()
        self.finalize()
    def slotMinimizeWindow(self):
        self.myMainWindow.showMinimized()
    def slotMaximizeWindow(self):
        if self.myMainWindow.isMaximized():
            self.myMainWindow.showNormal()
        else:
            self.myMainWindow.showMaximized()

    def slotReloadStylesheet(self):
        self.loadStyle()




if __name__=='__main__':
    if len(sys.argv)>1:
        RPRJ_CONFIG = sys.argv[1]
    myapp = RApp()
    myapp.exec_()
