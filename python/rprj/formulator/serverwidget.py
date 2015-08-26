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

import os
# import sys,traceback
from PyQt4 import QtCore, QtGui

try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s

import explorerwidget
from rprj.formulator import FAssociation


class ServerWidget(QtGui.QWidget):
    def __init__(self, parent, objectName):
        QtGui.QWidget.__init__(self, parent)
        # UI
        self.setObjectName(_fromUtf8("serverwidget"))
        self.layout = QtGui.QVBoxLayout(self)
        self.layout.setMargin(0)
        self.layout.setObjectName(_fromUtf8("layout"))
        self.splitter = QtGui.QSplitter(self)
        self.splitter.setOrientation(QtCore.Qt.Horizontal)
        self.splitter.setObjectName(_fromUtf8("splitter"))
        self.layout.addWidget(self.splitter)
        self.explorer = explorerwidget.ExplorerWidget(self.splitter, "TestWidget")
        self.explorer.container = self
        self.stackedWidget = QtGui.QStackedWidget(self.splitter)
        self.stackedWidget.setObjectName(_fromUtf8("stackedWidget"))
        self.pageMain = QtGui.QWidget()
        self.pageMain.setObjectName(_fromUtf8("pageMain"))
        self.stackedWidget.addWidget(self.pageMain)
        # Slots
        self.bind()
        # Logic
        self.server = None
        self.formFactory = None
        self.currentForm = None
        self.currentItem = None
        # Container
        self.container = None

    def bind(self):
        QtCore.QObject.connect(self, QtCore.SIGNAL("actionDownload(PyQt_PyObject)"), self.slotDownload)
        # Explorer
        QtCore.QObject.connect(self.explorer, QtCore.SIGNAL("updatedTree()"), self.slotUpdatedTree)
        QtCore.QObject.connect(self.explorer, QtCore.SIGNAL("selected(QStandardItem,PyQt_PyObject)"), self.slotSelected)
        QtCore.QObject.connect(self.explorer, QtCore.SIGNAL("actionDownload(PyQt_PyObject)"), self.slotDownload)
# ############################## PROPERTIES ###################################

    def setContainer(self, c):
        self.container = c

    def setServer(self, server):
        self.server = server
        self.explorer.setServer(server)

    def getServer(self):
        return self.server

    def setFormFactory(self, formFactory):
        self.formFactory = formFactory
        self.explorer.setFormFactory(formFactory)
        # Update UI
        # Cleaning stackedWidget
        for i in range(self.stackedWidget.count()):
            if i == 0:
                continue
            self.stackedWidget.removeWidget(self.stackedWidget.widget(i))
        # Form Cache
        self.forms = {}
        self.formWidgets = {}
        formList = self.formFactory.getAllClassnames()
        for _f in formList:
            if _f in ['default', 'FBanned', 'FLog', 'FObject',
                      'FProjectPeopleRole', 'FProjectCompanyRole', 'FTodoTipo', 'FUserGroupAssociation']:
                continue
            if _f.find("Filter") >= 0:
                continue
            myform = self.formFactory.getInstance(_f, dbmgr=self.server)
            if isinstance(myform, FAssociation):
                continue
            w = myform.render(self.stackedWidget)
            self.stackedWidget.addWidget(w)
            QtCore.QObject.connect(myform, QtCore.SIGNAL("clickedButton(PyQt_PyObject)"), self.slotFormClickedButton)
            self.forms[_f] = myform
            self.formWidgets[_f] = w

    def getName(self):
        d = self.server.getConnectionProvider().getDBType()
        u = "nobody"
        if not self.server.getDBEUser() is None:
            u = self.server.getDBEUser().getValue('login')
        h = "%s" % self.server.getConnectionProvider().getHost()
        if d == "SQLite":
            h = ("%s" % self.server.getConnectionProvider().getDB()).split(os.path.sep)[-1]
        if h.find("://") > 0:
            _url = h.split("://")
            if len(_url) > 1:
                h = "%s:%s" % (_url[0], _url[1].split("/")[0])
        return "%s@%s" % (u, h)
# ############################## PREFERENCES ###################################

    def getPref(self, section, option, default=None):
        if self.container is None:
            return default
        return self.container.getPref(section, option, default)

    def setPref(self, section, option, valore):
        if self.container is None:
            return
        self.container.setPref(section, option, valore)
# ############################## ... ###################################

    def Alert(self, msg, eccezione=None):
        if self.container is None:
            return
        self.container.Alert(msg, eccezione)

    def Confirm(self, msg):
        if self.container is None:
            return True
        return self.container.Confirm(msg)

    def SetStatusText(self, msg):
        if self.container is None:
            return
        self.container.SetStatusText(msg)

    def SetProgress(self, perc):
        if self.container is None:
            return
        self.container.SetProgress(perc)
# ############################## UI ###################################

    def updateObject(self, myitem, mydbe):
        myobj = mydbe
        # Load full object definition
        if mydbe.getTypeName() == "DBEObject":
            myobj = self.server.fullObjectById(mydbe.getValue('id'))
        # dbe 2 form
        try:
            myformname = self.formFactory.dbename2classname[myobj.getTypeName()]
            _form = self.forms[myformname]
            self.currentForm = _form
            self.currentItem = myitem
            self.stackedWidget.setCurrentWidget(self.formWidgets[myformname])
            _form.clean()
            _form.setValues(myobj.getValuesDictionary())
        except Exception, e:
            self.Alert("Error while executing updateObject: %s" % e, e)
# ############################## LOGIC ###################################

    def runSearchFolder(self, name, mydbetype='DBEObject'):
        self.explorer.runSearchFolder(name, mydbetype)

    def _form2item(self, item):
        if item is None:
            return
        values = self.currentForm.getValues()
        mydata = item.data().toPyObject()
        mydata.setValuesDictionary(values)
        item.setData(mydata)
        return item

    def actionClose(self, dbe):
        self.stackedWidget.setCurrentWidget(self.pageMain)

    def actionSave(self, dbe):
        mydbe = dbe
        if mydbe.isNew():
            mydbe = self.server.insert(mydbe)
        else:
            mydbe = self.server.update(mydbe)
        if mydbe is None:
            self.SetStatusText("Save: errors.")
            return None
        # Item update
        if self.currentItem is None:
            return
        myitem = self.currentItem
        myitem.setText(u"%s" % mydbe.getValue('name'))
        myitem.setData(mydbe)
        desc = mydbe.getValue('description')
        if desc > '':
            myitem.setToolTip(desc)
        self.updateObject(myitem, mydbe)
        self.SetStatusText("Save: OK")

    def actionDelete(self, dbe):
        msg = QtGui.QApplication.translate("ServerWidget", "Confirm Delete?", None, QtGui.QApplication.UnicodeUTF8)
        if not self.Confirm(msg):
            return
        myitem = self.currentItem
        if myitem is None:
            return
        myitem = self._form2item(myitem)
        mydbe = dbe
        if mydbe.isNew():
            mydbe = None
        else:
            mydbe = self.server.delete(mydbe)
        if mydbe is not None and not mydbe.isDeleted():
            self.SetStatusText("Delete: errors.")
            return
        myparent = myitem.parent()
        self.explorer.removeItem(myitem)
        mydbe = myparent.data().toPyObject()
        self.updateObject(myparent, mydbe)
        self.SetStatusText("Delete: OK")

    def actionDownload(self, dbe):
        currentDir = self.getPref("Main", "download_path", "%s%sDownloads" % (os.path.expanduser("~"), os.path.sep))
        fd = QtGui.QFileDialog(
            self, "Select destination...", "%s%s%s" % (currentDir, os.path.sep, dbe.getValue('filename')))
        fd.setAcceptMode(QtGui.QFileDialog.AcceptSave)
        if not fd.exec_():
            return
        currentDir = "%s" % (fd.directory().path())
        self.setPref("Main", "download_path", currentDir)
        try:
            local_filename = self.server.downloadFile(dbe.getValue('id'), currentDir)
            self.Alert("Download complete!")
        except Exception, e:
            self.Alert("Error while downloading!", e)
# ############################## SLOTS ###################################

    def slotUpdatedTree(self):
        self.stackedWidget.setCurrentWidget(self.pageMain)

    def slotSelected(self, item, dbe):
        self.updateObject(item, dbe)

    def slotFormClickedButton(self, s):
        sender = self.sender()
        mydbe = sender.getDBE(self.server)
        mydbe.setValuesDictionary(sender.getValues())
        tmp = s.split("_")
        nomeform = ''
        if len(tmp) > 1:
            nomeform = tmp[1]
        if len(tmp) > 2:
            nomeazione = "_".join(tmp[2:])
        customAction = "action%s%s" % (nomeazione[0].upper(), nomeazione[1:])
        if customAction == 'actionDownload':
            self.emit(QtCore.SIGNAL("actionDownload(PyQt_PyObject)"), mydbe)
            return
        try:
            getattr(self, customAction)(mydbe)
        except Exception, e:
            self.Alert("Error while executing '%s'." % nomeazione, e)

    def slotDownload(self, dbe):
        self.actionDownload(dbe)
