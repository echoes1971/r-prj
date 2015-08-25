# -*- coding: utf-8 -*-
#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: qtgui.py $
# @package formulator
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

"""QT Implementation of the formulator."""

import datetime, os, os.path, sys, traceback
import collections  # for OrderedDict
from PyQt4 import QtCore, QtGui
try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s

import rprj.formulator
from rprj.qtwidgets.editwidgetcontroller import EditWidgetController


class FField(rprj.formulator.FField, QtCore.QObject):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='', aClasseCss=None, myform=None,
                 mytipo='s'):
        QtCore.QObject.__init__(self)
        rprj.formulator.FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='',
                                        aClasseCss=None, myform=None, mytipo='s')
        self.widget = None
        self.label = None
        self.field = None

    def render(self, parent):
        if self.widget is None:
            self.InitWidget(parent)
        if self.widget is not None:
            self.widget.setVisible(True)
        if self.field is not None:
            self.field.setEnabled(True)
            self.field.setVisible(True)
        return self.widget

    def render_view(self, parent):
        return self.render_readonly(parent)

    def render_hidden(self, parent):
        self.render(parent)
        if self.widget is not None:
            self.widget.setVisible(False)
        elif self.field is not None:
            self.field.setVisible(False)
        return self.widget

    def render_readonly(self, parent):
        if self.widget is None:
            self.InitWidget(parent)
        self.field.setEnabled(False)
        return self.widget

    def InitLabel(self, parent):
        self.label = QtGui.QLabel(parent)
        self.label.setText(
            QtGui.QApplication.translate("Formulator", self._title, None, QtGui.QApplication.UnicodeUTF8))
        self.label.setObjectName("label_%s" % self.aNomeCampo)
        return self.label

    def InitField(self, parent):
        self.field = QtGui.QLineEdit(parent)
        # Size Policy: fix expanding issue on mac
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Expanding)
        sizePolicy.setHorizontalStretch(1)
        sizePolicy.setVerticalStretch(0)
        self.field.setSizePolicy(sizePolicy)
        self.field.setObjectName("field_%s" % self.aNomeCampo)
        return self.field

    def InitWidget(self, parent):
        if parent is None:
            return
        self.widget = QtGui.QWidget(parent)
        self.widget.setObjectName("widget_%s" % self.aNomeCampo)
        self.layout = QtGui.QVBoxLayout(self.widget)
        self.layout.setMargin(0)
        self.layout.setObjectName("layout_%s" % self.aNomeCampo)
        self.InitLabel(self.widget)
        self.layout.addWidget(self.label)
        self.InitField(self.widget)
        self.layout.addWidget(self.field)

    def getValue(self):
        if self.field is not None:
            self.aValore = "%s" % self.field.text()
        return rprj.formulator.FField.getValue(self)

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)
        if self.field is not None:
            self.field.setText("%s" % v)


class FForm(rprj.formulator.FForm, QtCore.QObject):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        QtCore.QObject.__init__(self)
        rprj.formulator.FForm.__init__(self, nome, azione, metodo, enctype, dbmgr)
        self.widget = None
        self.filterWidget = None
        self.buttons = {}
        # I'll keep the 'is_modified' logic on the QT form by now
        self._is_modified = False

    def render(self, parent):
        if self.widget is None:
            self.InitWidget(parent)
        if self.widget is not None:
            self.widget.setVisible(True)
        return self.widget

    def render_filter(self, parent):
        if self.filterWidget is None:
            self.InitFilterWidget(parent)
        return self.filterWidget
    # Search Filter: start.

    def InitFilterWidget(self, parent):
        if parent is None:
            return
        self.filterWidget = QtGui.QWidget(parent)
        self.filterWidget.setObjectName("widget_%s" % self.nome)
        self.filterLayout = QtGui.QVBoxLayout(self.filterWidget)
        self.filterLayout.setMargin(0)
        self.filterLayout.setObjectName("layout_%s" % self.nome)
        # Actions
        self.filterActions = QtGui.QWidget(self.widget)
        self.filterActions.setObjectName("widget_%s" % self.nome)
        self.filterLayoutActions = QtGui.QHBoxLayout(self.filterActions)
        self.filterLayoutActions.setMargin(0)
        self.filterLayoutActions.setObjectName("layout_%s" % self.nome)
        self.filterLayout.addWidget(self.filterActions)
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(":/rPrj/icons/glass.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        buttonShowHide = QtGui.QPushButton(icon, "", self.filterActions)
        buttonShowHide.setToolTip("Show / hide search filters")
        QtCore.QObject.connect(buttonShowHide, QtCore.SIGNAL("clicked()"), self.slotShowHideFilter)
        self.filterLayoutActions.addWidget(buttonShowHide)
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(":/rPrj/icons/reload.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        button = QtGui.QPushButton(icon, "", self.filterActions)
        button.setToolTip("Search")
        QtCore.QObject.connect(button, QtCore.SIGNAL("clicked()"), self.slotSearch)
        self.filterLayoutActions.addWidget(button)
        # Search Fields
        self.filterFields = QtGui.QWidget(self.filterWidget)
        formLayout = QtGui.QFormLayout(self.filterFields)
        formLayout.setFieldGrowthPolicy(QtGui.QFormLayout.ExpandingFieldsGrow)
        filters = self.getFilterFields()
        for myfieldname in filters:
            myfield = self.getField(myfieldname)
            label = myfield.InitLabel(self.filterFields)
            field = None
            if isinstance(myfield, FKField):
                field = myfield.InitField(self.filterFields, self.dbmgr, self.formFactory)
            else:
                field = myfield.InitField(self.filterFields)
            formLayout.addRow(label, field)
        self.filterFields.setVisible(False)
        self.filterLayout.addWidget(self.filterFields)
        if len(filters) == 0:
            buttonShowHide.setVisible(False)

    def slotShowHideFilter(self):
        self.filterFields.setVisible(not self.filterFields.isVisible())

    def slotSearch(self):
        self.filterWidget.emit(QtCore.SIGNAL("doSearch()"))
    # Search Filter: end.

    def InitWidget(self, parent):
        if parent is None:
            return
        self.widget = QtGui.QWidget(parent)
        self.widget.setObjectName("widget_%s" % self.nome)
        self.layout = QtGui.QVBoxLayout(self.widget)
        self.layout.setMargin(0)
        self.layout.setObjectName("layout_%s" % self.nome)
        # Actions
        self.widgetActions = QtGui.QWidget(self.widget)
        self.widgetActions.setObjectName("widget_%s" % self.nome)
        self.layoutActions = QtGui.QHBoxLayout(self.widgetActions)
        self.layoutActions.setMargin(0)
        self.layoutActions.setObjectName("layout_%s" % self.nome)
        myactions = collections.OrderedDict((
            ('close', ['Close', '_do.php', 'icons/fileclose.png', 'Close']),
            ('reload', ['Reload', '_do.php', 'icons/reload.png', 'Reload']),
            ('save', ['Save', '_do.php', 'icons/filesave.png', 'Save']),
            ('delete', ['Delete', '_do.php', 'icons/editdelete.png', 'Delete']),
            ))
        for k, v in self.getActions().items():
            myactions[k] = v
        for k in myactions.keys():
            if len(myactions[k]) > 2:
                icon = QtGui.QIcon()
                icon.addPixmap(
                    QtGui.QPixmap(_fromUtf8(":/rPrj/%s" % myactions[k][2])), QtGui.QIcon.Normal, QtGui.QIcon.Off)
                button = QtGui.QPushButton(icon, "", self.widgetActions)
                button.setToolTip(myactions[k][0])
            else:
                button = QtGui.QPushButton(myactions[k][0], self.widgetActions)
            self.buttons[k] = button
            button.setObjectName("button_%s_%s" % (self.nome, k))
            # tmpmsg = "button_%s_%s" % (self.nome, k)
            myaction = lambda: self.emit(
                QtCore.SIGNAL("clickedButton(PyQt_PyObject)"), "%s" % button.sender().objectName())
            QtCore.QObject.connect(button, QtCore.SIGNAL("clicked()"), myaction)
            self.layoutActions.addWidget(button, 0)
        self.layoutActions.addItem(QtGui.QSpacerItem(20, 20, QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Minimum))
        self.layout.addWidget(self.widgetActions, 0)
        # Tab
        self.widgetTab = QtGui.QTabWidget(self.widget)
        self.widgetTab.setObjectName("widget_%s_tab" % self.nome)
        self.layoutTab = QtGui.QVBoxLayout(self.widgetTab)
        self.layoutTab.setMargin(0)
        self.layoutTab.setObjectName("layout_%s_tab" % self.nome)
        for groupName in self.getGroupNames():
            groupBox = QtGui.QWidget(self.widgetTab)
            decodedGroupName = self.decodeGroupName(groupName)
            if decodedGroupName == groupName:
                decodedGroupName = self.getDetailTitle()
            groupBox.setObjectName("groupBox_%s" % groupName)
            formLayout = QtGui.QFormLayout(groupBox)
            # Field Growth Policy: fix expanding issue on mac
            formLayout.setFieldGrowthPolicy(QtGui.QFormLayout.ExpandingFieldsGrow)
            formLayout.setObjectName("gridLayout_%s" % groupName)
            mygroup = self.getGroup(groupName)
            for myfieldname in mygroup:
                myfield = self.getField(myfieldname)
                label = myfield.InitLabel(groupBox)
                field = None
                if isinstance(myfield, FKField):
                    field = myfield.InitField(groupBox, self.dbmgr, self.formFactory)
                else:
                    field = myfield.InitField(groupBox)
                if myfieldname in self.getDetailReadOnlyColumnNames():
                    field.setEnabled(False)
                if myfieldname not in self.getDetailColumnNames():
                    label.setVisible(False)
                    field.setVisible(False)
                else:
                    formLayout.addRow(label, field)
            self.widgetTab.addTab(groupBox, decodedGroupName)
        self.layout.addWidget(self.widgetTab, 1)

    def setWidget(self, w):
        if w == self.widget:
            return
        self.widget = w
        for fieldName in self.getFieldNames():
            childs = w.findChildren(QtGui.QWidget, "field_%s" % fieldName)
            if len(childs) == 1:
                myField = rprj.formulator.FMasterDetail.getField(self, fieldName)
                myField.field = childs[0]
                myField.render(None)

    def setValues(self, valori):
        # if I set all the values, means I'm setting the initial state
        self._is_modified = False
        rprj.formulator.FForm.setValues(self, valori)

    def setValue(self, fieldName, aValue):
        # When I set a value, either is the same value or is a different one
        # I'll not distinguish between the 2 cases now 'cause a possible overhead
        # I'll set the form 'changed' in any case
        self._is_modified = True
        rprj.formulator.FForm.setValue(self, fieldName, aValue)

    def is_modified(self):
        return self._is_modified


class FMasterDetail(FForm, rprj.formulator.FMasterDetail):
    def __init__(self, nome='', azione='', metodo="POST", enctype='', dbmgr=None):
        rprj.formulator.FMasterDetail.__init__(self, nome, azione, metodo, enctype, dbmgr)
        FForm.__init__(self, nome, azione, metodo, enctype, dbmgr)

    def getValues(self):
        tmp = rprj.formulator.FMasterDetail.getValues(self)
        # Because of setWidget, I have non-existent widgets and the getValue returns None :-(
        # this creates problems in xmlrpc marshalling :-P
        ret = {}
        for k in tmp.keys():
            v = tmp[k]
            if v is None:
                continue
            ret[k] = v
        return ret

    def setValues(self, valori, doFetchChilds=False):
        rprj.formulator.FMasterDetail.setValues(self, valori, doFetchChilds)

FAssociation = rprj.formulator.FAssociation

FormFactory = rprj.formulator.FormFactory

# ######################### Campi Standard


class FFileField(FField, rprj.formulator.FFileField):
    def __init__(self, aNomeCampo, aTitle, aDescription, dest_directory='', aSize=1, aLength=20, aValore='',
                 aClasseCss=None):
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)
        rprj.formulator.FFileField.__init__(self, aNomeCampo, aTitle, aDescription, dest_directory, aSize, aLength,
                                            aValore, aClasseCss)

    def InitField(self, parent):
        self.fieldWidget = QtGui.QWidget(parent)
        self.fileDialog = QtGui.QFileDialog(parent, QtGui.QApplication.translate("Formulator", "Select file...", None,
                                            QtGui.QApplication.UnicodeUTF8), os.path.expanduser("~"))
        self.fileDialog.setFileMode(QtGui.QFileDialog.ExistingFile)
        self.layout = QtGui.QHBoxLayout(self.fieldWidget)
        self.layout.setMargin(0)
        self.layout.setObjectName("layout_%s" % self.aNomeCampo)
        # TODO widget for the thumbnail
        myfield = FField.InitField(self, self.fieldWidget)
        myfield.setEnabled(False)
        self.layout.addWidget(myfield, 1)
        self.icon = QtGui.QIcon()
        self.icon.addPixmap(
            QtGui.QPixmap(_fromUtf8(":/rPrj/icons/folder_16x16.gif")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.mybutton = QtGui.QPushButton(self.icon, "", self.fieldWidget)
        self.layout.addWidget(self.mybutton, 0)
        QtCore.QObject.connect(self.mybutton, QtCore.SIGNAL("clicked()"), self.slotFileDialog)
        return self.fieldWidget

    def slotFileDialog(self):
        fileNames = []
        if self.fileDialog.exec_():
            fileNames = self.fileDialog.selectedFiles()
        if len(fileNames) != 1:
            return
        self.setValue("%s" % fileNames[0])

    def setValue(self, v):
        FField.setValue(self, v)
        if self.myform.isImage():
            # TODO isImage=True
            print "FFileField.setValue: TODO - isImage=True"


class FNumber(FField):  # ,rprj.formulator.FNumber):
    def getValue(self):
        ret = FField.getValue(self)
        if ret == '':
            return None
        return ret


class FPercent(FField):  # ,rprj.formulator.FPercent):
    def getValue(self):
        ret = FField.getValue(self)
        if ret == '': return None
        return ret


class FString(FField, rprj.formulator.FString):
    def getValue(self):
        if self.field is not None:
            self.aValore = "%s" % self.field.text()
        return rprj.formulator.FField.getValue(self)


class FPassword(FString):  # ,rprj.formulator.FPassword):
    def InitField(self, parent):
        FString.InitField(self, parent)
        # TEST this
        self.field.setEchoMode(QtGui.QLineEdit.Password)
        return self.field


class FServerFile(FField):  # ,rprj.formulator.FServerFile):
    pass


class FServerImage(FField):  # ,rprj.formulator.FServerImage):
    pass


class FList(FField, rprj.formulator.FList):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='', aClasseCss=None, listaValori={},
                 altezza=1, multiselezione=False):
        """listaValori = {  k=>v  }
        altezza = numero di elementi visualizzati della select
        multiselezione"""
        rprj.formulator.FList.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss,
                                       listaValori, altezza, multiselezione)
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength, aValore, aClasseCss)

    def InitField(self, parent):
        # TODO multiselection and altezza (height)
        self.field = QtGui.QComboBox(parent)
        self.field.setObjectName("field_%s" % self.aNomeCampo)
        for k in self.listaValori.keys():
            self.field.addItem(self.listaValori[k], k)
        self.field.setEditable(False)
        return self.field

    def getValue(self):
        if self.field is not None:
            self.aValore = self.field.itemData(self.field.currentIndex()).toPyObject()
        return rprj.formulator.FField.getValue(self)

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)
        if self.field is not None:
            self.field.setCurrentIndex(self.field.findData(v))


class FCheckBox(FField):  # ,rprj.formulator.FCheckBox):
    pass


class FTextArea(FField, rprj.formulator.FTextArea):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aValore='', aClasseCss=None, width=None, height=None,
                 basicFormatting=True):
        rprj.formulator.FTextArea.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aValore, aClasseCss, width,
                                           height, basicFormatting)
        FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, -1, aValore)

    def InitField(self, parent):
        self.field = QtGui.QTextEdit(parent)
        self.field.setObjectName("field_%s" % self.aNomeCampo)
        hp = QtGui.QSizePolicy.Expanding
        vp = QtGui.QSizePolicy.Expanding
        if self.width > 0:
            hp = QtGui.QSizePolicy.Fixed
            self.field.setMaximumWidth(self.width*14)
        if self.height > 0:
            vp = QtGui.QSizePolicy.Fixed
            self.field.setMaximumHeight(self.height*22)
        sizePolicy = QtGui.QSizePolicy(hp, vp)
        sizePolicy.setHorizontalStretch(self.width <= 0)
        sizePolicy.setVerticalStretch(self.height <= 0)
        self.field.setSizePolicy(sizePolicy)
        return self.field

    def getValue(self):
        if self.field is not None:
            self.aValore = "%s" % self.field.toPlainText()
        return rprj.formulator.FField.getValue(self)


class FDateTime(FField, rprj.formulator.FDateTime):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss=None, aVisualizzaData=True,
                 aVisualizzaOra=True):
        rprj.formulator.FDateTime.__init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss, aVisualizzaData,
                                           aVisualizzaOra)
        FField.__init__(self, aNomeCampo, aTitle, aDescription, -1, -1, aValore)

    def InitField(self, parent):
        if self.aVisualizzaData:
            if self.aVisualizzaOra:
                self.field = QtGui.QDateTimeEdit(parent)
            else:
                self.field = QtGui.QDateEdit(parent)
        else:
            self.field = QtGui.QTimeEdit(parent)
        self.field.setObjectName("field_%s" % self.aNomeCampo)
        # Forced rendering with default value
        self.setValue(self.aValore)
        return self.field

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)
        if self.field is not None:
            if v is None:
                self.field.setDateTime(QtCore.QDateTime.fromString('2000-01-01 00:00:00', "yyyy-MM-dd hh:mm:ss"))
            elif type(v) == str or type(v) == unicode:
                self.field.setDateTime(QtCore.QDateTime.fromString(v, "yyyy-MM-dd hh:mm:ss"))
            elif isinstance(v, datetime.time):
                # TODO show only time for timetracks
                self.field.setTime(QtCore.QTime(v.hour, v.minute, v.second))
            elif isinstance(v, datetime.datetime):
                self.field.setDateTime(QtCore.QDateTime(v.year, v.month, v.day, v.hour, v.minute, v.second))
            else:
                self.field.setDateTime(QtCore.QDateTime(v))

    def getValue(self):
        if self.field is not None:
            self.aValore = self.field.dateTime().toPyDateTime()
            if '2000-01-01 00:00:00' == "%s" % self.aValore:
                self.aValore = '0000-00-00 00:00:00'
        return rprj.formulator.FField.getValue(self)

    def clean(self):
        """Resets the field value."""
        self.setValue(None)


class FDateTimeReadOnly(FDateTime):  # ,rprj.formulator.FDateTimeReadOnly):
    def InitField(self, parent):
        FDateTime.InitField(self, parent)
        self.field.setEnabled(False)
        return self.field

    def render(self, parent):
        return self.render_readonly(parent)


class FKField(FField, rprj.formulator.FKField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore=None, aClasseCss=None, mydbe=None, myFK=None,
                 description_columns=[], destform=None, viewmode='select', aDescription_glue=" - ", aAltezza=1,
                 aMultiselezione=False):
        rprj.formulator.FKField.__init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss, mydbe, myFK,
                                         description_columns, destform, viewmode, aDescription_glue, aAltezza,
                                         aMultiselezione)
        FField.__init__(self, aNomeCampo, aTitle, aDescription, 0, 0, aValore, aClasseCss)
        self.dbmgr = None
        self.formFactory = None
        self._mythread = QtCore.QThread()
        self._mythread.run = self.runDecodeValue

    def InitField(self, parent, dbmgr, formFactory):
        self.fieldWidget = QtGui.QWidget(parent)
        self.layout = QtGui.QHBoxLayout(self.fieldWidget)
        self.layout.setMargin(0)
        self.layout.setObjectName("layout_%s" % self.aNomeCampo)
        # TODO widget for the thumbnail
        myfield = FField.InitField(self, self.fieldWidget)
        myfield.setEnabled(False)
        self.layout.addWidget(myfield, 1)
        self.icon = QtGui.QIcon()
        self.icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/glass.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.mybutton = QtGui.QPushButton(self.icon, "", self.fieldWidget)
        self.layout.addWidget(self.mybutton, 0)
        QtCore.QObject.connect(self.mybutton, QtCore.SIGNAL("clicked()"), self.slotSearch)
        self.dbmgr = dbmgr
        self.formFactory = formFactory
        return self.fieldWidget

    def getMyFilterForm(self):
        myclazz = self.dbmgr.getClazz(self.myFK.tabella_riferita)
        return self.formFactory.getInstanceByDBEName(myclazz().getTypeName(), dbmgr=self.dbmgr)

    def slotSearch(self):
        myfilterform = self.getMyFilterForm()
        searchDialog = QtGui.QDialog(self.fieldWidget)
        searchDialog.setModal(True)
        searchDialog.setWindowTitle(
            QtGui.QApplication.translate("FKFiels", "Select", None, QtGui.QApplication.UnicodeUTF8))
        verticalLayout = QtGui.QVBoxLayout(searchDialog)
        verticalLayout.setMargin(0)
        verticalLayout.setObjectName("%s_verticalLayout" % self.aNomeCampo)
        import searchwidget
        searchWidget = searchwidget.SearchWidget(searchDialog, "%s_explorer" % self.aNomeCampo, myfilterform, True)
        searchWidget.setServer(self.dbmgr)
        searchWidget.setFormFactory(self.formFactory)
        if self.getValue() is not None and not myfilterform.getDetailTitle() == 'Object':
            selectedDBE = myfilterform.getDBE(self.dbmgr)
            selectedDBE.setValue(self.myFK.colonna_riferita, self.getValue())
            searchWidget.setSelectedDBE(selectedDBE)
            myfilterform.setValue(self.myFK.colonna_riferita, self.getValue())
            searchWidget.slotDoSearch()
            myfilterform.setValue(self.myFK.colonna_riferita, None)
        QtCore.QObject.connect(
            searchWidget, QtCore.SIGNAL("selected(QListWidgetItem,PyQt_PyObject)"), searchDialog.accept)
        verticalLayout.addWidget(searchWidget, 1)
        res = searchDialog.exec_()
        if res == 0: return
        selectedDBE = searchWidget.selectedDBE()
        self.setValue(selectedDBE.getValue(self.myFK.colonna_riferita))

    def clean(self):
        """Resets the field value."""
        self.setValue(None)

    def getValue(self):
        return rprj.formulator.FField.getValue(self)

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)
        if self.field is not None:
            if v is None:
                self.field.setText('')
                return
            # BUG FIXED sqlite and mysql do not supports multithread
            if self.dbmgr.getConnectionProvider().getDBType() in ['MYSQL', 'SQLite']:
                # If the connection provider does NOT support multithread...
                self.runDecodeValue()
            else:
                # ...otherwise start decode thread
                self.startDecodeValue()

    def startDecodeValue(self):
        if not self._mythread.isRunning():
            self._mythread.start()

    def runDecodeValue(self):
        try:
            v = rprj.formulator.FField.getValue(self)
            if v is None:
                return
            cerca = self.dbmgr.getDBEFactory().getClazz(self.myFK.tabella_riferita)()
            cerca.setValue(self.myFK.colonna_riferita, v)
            lista = self.dbmgr.search(cerca, uselike=False)
            if len(lista) == 1:
                mydbe = lista[0]
                description_array = []
                for chiave in self.description_columns:
                    description_array.append(mydbe.getValue(chiave))
                link_desc = self.description_glue.join(description_array)
                self.field.setText(link_desc)
            else:
                pass
        except Exception, e:
            print "FKField.runDecodeValue: ERROR '%s'" % e
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            pass


class FKObjectField(FKField):  # ,rprj.formulator.FKObjectField):
    def __init__(self, aNomeCampo, aTitle, aDescription, aValore=None, aClasseCss=None, mydbe=None, myFK=None,
                 description_columns=[], destform=None, viewmode='select', aDescription_glue=" - ", aAltezza=1,
                 aMultiselezione=False):
        FKField.__init__(self, aNomeCampo, aTitle, aDescription, aValore, aClasseCss, mydbe, myFK, description_columns,
                         destform, viewmode, aDescription_glue, aAltezza, aMultiselezione)

    def getMyFilterForm(self):
        return self.formFactory.getInstanceByDBEName("DBEObject", dbmgr=self.dbmgr)

    def setValue(self, v, update_widget=True):
        # TODO use multithreading like FKField
        rprj.formulator.FField.setValue(self, v)
        if self.field is not None:
            myobj = self.dbmgr.objectById("%s" % v)
            if myobj is not None:
                self.field.setText(myobj.getValue('name'))


class FHtml(FField):  # ,rprj.formulator.FHtml):
    def getValue(self):
        if self.field is not None:
            self.aValore = "%s" % self.field.text()
        return rprj.formulator.FField.getValue(self)

    def InitField(self, parent):
        self.field = EditWidgetController(parent, "field_%s" % self.aNomeCampo)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Expanding)
        sizePolicy.setHorizontalStretch(0)
        sizePolicy.setVerticalStretch(1)
        sizePolicy.setHeightForWidth(self.field.sizePolicy().hasHeightForWidth())
        self.field.setSizePolicy(sizePolicy)
        return self.field


class FUuid(FString, rprj.formulator.FUuid):
    def InitField(self, parent):
        FString.InitField(self, parent)
        self.field.setEnabled(False)
        return self.field

    def getValue(self):
        return rprj.formulator.FField.getValue(self)

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)
        _v = ("%s" % v).replace("uuid", "")
        __v = "%s-%s-%s-%s-%s" % (_v[:8], _v[8:12], _v[12:16], _v[16:20], _v[20:])
        if self.field is not None:
            self.field.setText(__v)


class FPermissions(FField):  # ,rprj.formulator.FPermissions):
    def __init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='', aClasseCss=None, myform=None,
                 mytipo='s'):
        QtCore.QObject.__init__(self)
        rprj.formulator.FField.__init__(self, aNomeCampo, aTitle, aDescription, aSize, aLength=20, aValore='',
                                        aClasseCss=None, myform=None, mytipo='s')
        self.widget = None
        self.label = None
        self.field = None
        self.buttonUserRead = None
        self.buttonUserWrite = None
        self.buttonUserExecute = None
        self.buttonGroupRead = None
        self.buttonGroupWrite = None
        self.buttonGroupExecute = None
        self.buttonAllRead = None
        self.buttonAllWrite = None
        self.buttonAllExecute = None

    def getValue(self):
        if self.buttonUserRead is None:
            # Field is not visible, then return what we got
            return self.aValore
        v = ['-', '-', '-', '-', '-', '-', '-', '-', '-']
        if self.buttonUserRead.isChecked(): v[0] = 'r'
        if self.buttonUserWrite.isChecked(): v[1] = 'w'
        if self.buttonUserExecute.isChecked(): v[2] = 'x'
        if self.buttonGroupRead.isChecked(): v[3] = 'r'
        if self.buttonGroupWrite.isChecked(): v[4] = 'w'
        if self.buttonGroupExecute.isChecked(): v[5] = 'x'
        if self.buttonAllRead.isChecked(): v[6] = 'r'
        if self.buttonAllWrite.isChecked(): v[7] = 'w'
        if self.buttonAllExecute.isChecked(): v[8] = 'x'
        self.aValore = "".join(v)
        return self.aValore

    def setValue(self, v, update_widget=True):
        if len(v) < 9:
            v = "%s%s" % (v, '-' * (9-len(v)))
        self.aValore = v
        if self.buttonUserRead is None:
            return
        self.buttonUserRead.setChecked(v[0] == 'r')
        self.buttonUserWrite.setChecked(v[1] == 'w')
        self.buttonUserExecute.setChecked(v[2] == 'x')
        self.buttonGroupRead.setChecked(v[3] == 'r')
        self.buttonGroupWrite.setChecked(v[4] == 'w')
        self.buttonGroupExecute.setChecked(v[5] == 'x')
        self.buttonAllRead.setChecked(v[6] == 'r')
        self.buttonAllWrite.setChecked(v[7] == 'w')
        self.buttonAllExecute.setChecked(v[8] == 'x')

    def InitField(self, parent):
        self.field = QtGui.QWidget(parent)
        self.field.setObjectName("field_%s" % self.aNomeCampo)
        self.fieldLayout = QtGui.QGridLayout(self.field)
        self.fieldLayout.setMargin(0)
        self.fieldLayout.setObjectName("field_layout_%s" % self.aNomeCampo)
        # Buttons
        col = 0
        self.userLabel = QtGui.QLabel("User", self.field)
        self.fieldLayout.addWidget(self.userLabel, 0, col+0,  1, 3, QtCore.Qt.AlignHCenter)
        self.buttonUserRead = QtGui.QToolButton(self.field)
        self.buttonUserRead.setText("R")
        self.buttonUserRead.setToolTip("Read")
        self.buttonUserRead.setCheckable(True)
        self.fieldLayout.addWidget(self.buttonUserRead, 1, col+0)
        self.buttonUserWrite = QtGui.QToolButton(self.field)
        self.buttonUserWrite.setText("W")
        self.buttonUserWrite.setCheckable(True)
        self.buttonUserWrite.setToolTip("Write")
        self.fieldLayout.addWidget(self.buttonUserWrite, 1, col+1)
        self.buttonUserExecute = QtGui.QToolButton(self.field)
        self.buttonUserExecute.setText("X")
        self.buttonUserExecute.setCheckable(True)
        self.buttonUserExecute.setToolTip("Execute")
        self.fieldLayout.addWidget(self.buttonUserExecute, 1, col+2)
        col += 3
        self.groupLabel = QtGui.QLabel("Group", self.field)
        self.fieldLayout.addWidget(self.groupLabel, 0, col+0,  1, 3, QtCore.Qt.AlignHCenter)
        self.buttonGroupRead = QtGui.QToolButton(self.field)
        self.buttonGroupRead.setText("R")
        self.buttonGroupRead.setToolTip("Read")
        self.buttonGroupRead.setCheckable(True)
        self.fieldLayout.addWidget(self.buttonGroupRead, 1, col+0)
        self.buttonGroupWrite = QtGui.QToolButton(self.field)
        self.buttonGroupWrite.setText("W")
        self.buttonGroupWrite.setCheckable(True)
        self.buttonGroupWrite.setToolTip("Write")
        self.fieldLayout.addWidget(self.buttonGroupWrite, 1, col+1)
        self.buttonGroupExecute = QtGui.QToolButton(self.field)
        self.buttonGroupExecute.setText("X")
        self.buttonGroupExecute.setCheckable(True)
        self.buttonGroupExecute.setToolTip("Execute")
        self.fieldLayout.addWidget(self.buttonGroupExecute, 1, col+2)
        col += 3
        self.allLabel = QtGui.QLabel("All", self.field)
        self.fieldLayout.addWidget(self.allLabel, 0, col+0,  1, 3, QtCore.Qt.AlignHCenter)
        self.buttonAllRead = QtGui.QToolButton(self.field)
        self.buttonAllRead.setText("R")
        self.buttonAllRead.setToolTip("Read")
        self.buttonAllRead.setCheckable(True)
        self.fieldLayout.addWidget(self.buttonAllRead, 1, col+0)
        self.buttonAllWrite = QtGui.QToolButton(self.field)
        self.buttonAllWrite.setText("W")
        self.buttonAllWrite.setCheckable(True)
        self.buttonAllWrite.setToolTip("Write")
        self.fieldLayout.addWidget(self.buttonAllWrite, 1, col+1)
        self.buttonAllExecute = QtGui.QToolButton(self.field)
        self.buttonAllExecute.setText("X")
        self.buttonAllExecute.setCheckable(True)
        self.buttonAllExecute.setToolTip("Execute")
        self.fieldLayout.addWidget(self.buttonAllExecute, 1, col+2)
        return self.field


class FLanguage(FField):  # ,rprj.formulator.FLanguage):
    pass


class FChildSort(FList):  # ,rprj.formulator.FChildSort):
    # TODO
    def getValue(self):
        return rprj.formulator.FField.getValue(self)

    def setValue(self, v, update_widget=True):
        rprj.formulator.FField.setValue(self, v)


class FIPAddress(FField):  # ,rprj.formulator.FIPAddress):
    pass
