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
R-Project main component


"""

import os,sys,traceback
from PyQt4 import QtCore, QtGui, Qt
try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s

from rprj.formulator import serverwidget
from qtcomponent import qtComponent

class RPrjComponent(qtComponent):
    def __init__(self,config,parent=None):
        qtComponent.__init__(self,config,parent)
        self.icon = None
        self.widget = None
        self.menu = None
        self._objectNamePrefix="rprj"
        # Logic
        self.connections = []
    def renderWidget(self,parentWidget):
        self.widget = QtGui.QTabWidget(parentWidget)
        self.widget.setTabsClosable(True)
        self.widget.setMovable(True)
        self.widget.setObjectName("%s_widget"%self._objectNamePrefix)
        # Binds
        # Tabs
        QtCore.QObject.connect(self.widget,QtCore.SIGNAL("tabCloseRequested(int )"),self.slotTabCloseRequested)
    def renderMenu(self,menubar):
        # Main Menu
        self.menu = QtGui.QMenu(menubar)
        self.menu.setTitle(QtGui.QApplication.translate("Rprj", "Rprj", None, QtGui.QApplication.UnicodeUTF8))
        self.menu.setObjectName("%s_menu"%self._objectNamePrefix)
        # Menu Admin
        if self.getPref("Main","Admin",False):
            self.menuAdmin = QtGui.QMenu(self.menu)
            self.menuAdmin.setTitle(QtGui.QApplication.translate("Rprj", "Admin", None, QtGui.QApplication.UnicodeUTF8))
            self.menuAdmin.setObjectName("%s_menuAdmin"%self._objectNamePrefix)
            self.actionCheckDB = QtGui.QAction(self.menuAdmin)
            self.actionCheckDB.setText(QtGui.QApplication.translate("Rprj", "Check DB", None, QtGui.QApplication.UnicodeUTF8))
            self.actionCheckDB.setObjectName(_fromUtf8("actionCheckDB"))
            self.actionUpdate_Forms = QtGui.QAction(self.menuAdmin)
            self.actionUpdate_Forms.setText(QtGui.QApplication.translate("Rprj", "Update Form Schema", None, QtGui.QApplication.UnicodeUTF8))
            self.actionUpdate_Forms.setObjectName(_fromUtf8("actionUpdate_Forms"))
            self.actionUpdate_DB_Schema = QtGui.QAction(self.menuAdmin)
            self.actionUpdate_DB_Schema.setText(QtGui.QApplication.translate("Rprj", "Update DB Schema", None, QtGui.QApplication.UnicodeUTF8))
            self.actionUpdate_DB_Schema.setObjectName(_fromUtf8("actionUpdate_DB_Schema"))
            self.actionDBSchema_to_SQL = QtGui.QAction(self.menuAdmin)
            self.actionDBSchema_to_SQL.setText(QtGui.QApplication.translate("Rprj", "DBSchema to SQL", None, QtGui.QApplication.UnicodeUTF8))
            self.actionDBSchema_to_SQL.setObjectName(_fromUtf8("actionDBSchema_to_SQL"))
            self.menuAdmin.addAction(self.actionCheckDB)
            self.menuAdmin.addAction(self.actionUpdate_Forms)
            self.menuAdmin.addAction(self.actionUpdate_DB_Schema)
            self.menuAdmin.addAction(self.actionDBSchema_to_SQL)
        # Only Admins should see this
        if self.getPref("Main","Admin",False):
            self.menu.addSeparator()
            self.menu.addMenu(self.menuAdmin)
        menubar.addAction(self.menu.menuAction())
        # Binds
        # Menu
        pass
        # Admin Menu
        if self.getPref("Main","Admin",False):
            QtCore.QObject.connect(self.actionCheckDB,QtCore.SIGNAL("triggered()"),self.slotCheckDB)
            QtCore.QObject.connect(self.actionUpdate_Forms,QtCore.SIGNAL("triggered()"),self.slotUpdateForms)
            QtCore.QObject.connect(self.actionUpdate_DB_Schema,QtCore.SIGNAL("triggered()"),self.slotUpdateDBSchema)
            QtCore.QObject.connect(self.actionDBSchema_to_SQL,QtCore.SIGNAL("triggered()"),self.slotDBSchemaToSQL)
# ############################## DESCRIPTION ###################################
    def getName(self):
        return "R-Project"
    def getIcon(self):
        if self.icon is None:
            self.icon = QtGui.QIcon()
            self.icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/rprj.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        return self.icon
    def getPanel(self,parentWidget=None):
        if self.widget is None:
            self.renderWidget(parentWidget)
        return self.widget
    def getMenu(self,menubar):
        if self.menu is None:
            self.renderMenu(menubar)
        return self.menu
# ############################## PREFERENCES ###################################
    def SaveUI2Config(self):
        pass
    def RestoreUIFromConfig(self):
        # Target system
        pass

# ############################## LOGIC ###################################
    def downloadDBSchema(self):
        mydbschema = self.server.getDBSchema('python')
        f=file('mydbschema.py','w')
        mysrc = """# -*- coding: utf-8 -*-
from rprj.dblayer import *

%s

dbeFactory = DBEFactory(True)
for k in dbschema.keys():
    v = dbschema[k]
    dbeFactory.register( k, v )
""" % (mydbschema)
        f.write(mysrc)
        f.flush()
        f.close()
        # Definitins import
        import mydbschema
        self.server.setDBEFactory(mydbschema.dbeFactory)
        self.SetStatusText("Download DB Schema: finished.")
    def downloadForms(self):
        myformschema = self.server.getFormSchema()
        f=file('myformschema.py','w')
        mysrc = """# -*- coding: utf-8 -*-
from rprj.formulator.qtgui import *

%s

""" % (myformschema)
        f.write(mysrc)
        f.flush()
        f.close()
        # Definitins import
        from myformschema import formschema_type_list
        for f in formschema_type_list.keys():
            self.formFactory.register(f,formschema_type_list[f],self.server)
        self.SetStatusText("Download Form Schema: finished.")
    def connectToServer(self, server,  formFactory):
        # Exists?
        serverIDstring = server.getServerIDString()
        for c in self.connections:
            if c['name']!=serverIDstring:
                continue
            widget = c['widget']
            self.widget.setCurrentWidget( widget )
            return ""
        try:
            # Widget
            widget = serverwidget.ServerWidget(self.widget,"ServerWidget")
            widget.setServer(server)
            widget.setFormFactory(formFactory)
            widget.setContainer(self)
            # Slots
            QtCore.QObject.connect(widget,QtCore.SIGNAL("updatedTree()"),self.slotUpdatedTree)
            QtCore.QObject.connect(widget,QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"),self.slotSelected)
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
    def slotCheckDB(self):
        currentWidget = self.widget.currentWidget()
        if not currentWidget is None:
            self.server = currentWidget.getServer()
            self.server.checkDB()
    def slotUpdateForms(self):
        currentWidget = self.widget.currentWidget()
        if not currentWidget is None:
            self.server = currentWidget.getServer()
            self.downloadForms()
    def slotUpdateDBSchema(self):
        currentWidget = self.widget.currentWidget()
        if not currentWidget is None:
            self.server = currentWidget.getServer()
            self.downloadDBSchema()
    def slotDBSchemaToSQL(self):
        currentWidget = self.widget.currentWidget()
        if not currentWidget is None:
            self.server = currentWidget.getServer()
            self.server.initDB()

    def slotUpdatedTree(self):
        pass # TODO self.SetStatusText("Updated tree")
    def slotSelected(self,item,dbe):
        pass # TODO self.SetStatusText("Selected item=%s dbe=%s" % (item,dbe) )
