# -*- coding: utf-8 -*-

# Form implementation generated from reading ui file 'explorerwidgetui.ui'
#
# Created: Mon Sep 24 12:30:21 2012
#      by: PyQt4 UI code generator 4.9.1
#
# WARNING! All changes made in this file will be lost!

from PyQt4 import QtCore, QtGui

try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s

class Ui_RprjExplorerWidget(object):
    def setupUi(self, RprjExplorerWidget):
        RprjExplorerWidget.setObjectName(_fromUtf8("RprjExplorerWidget"))
        RprjExplorerWidget.resize(359, 483)
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/rprj.png")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        RprjExplorerWidget.setWindowIcon(icon)
        self.verticalLayout = QtGui.QVBoxLayout(RprjExplorerWidget)
        self.verticalLayout.setMargin(0)
        self.verticalLayout.setObjectName(_fromUtf8("verticalLayout"))
        self.widget = QtGui.QWidget(RprjExplorerWidget)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Preferred)
        sizePolicy.setHorizontalStretch(1)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(self.widget.sizePolicy().hasHeightForWidth())
        self.widget.setSizePolicy(sizePolicy)
        self.widget.setObjectName(_fromUtf8("widget"))
        self.horizontalLayout = QtGui.QHBoxLayout(self.widget)
        self.horizontalLayout.setMargin(0)
        self.horizontalLayout.setMargin(0)
        self.horizontalLayout.setObjectName(_fromUtf8("horizontalLayout"))
        self.searchFolder = QtGui.QLineEdit(self.widget)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Fixed)
        sizePolicy.setHorizontalStretch(1)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(self.searchFolder.sizePolicy().hasHeightForWidth())
        self.searchFolder.setSizePolicy(sizePolicy)
        self.searchFolder.setObjectName(_fromUtf8("searchFolder"))
        self.horizontalLayout.addWidget(self.searchFolder)
        self.comboForms = QtGui.QComboBox(self.widget)
        self.comboForms.setSizeAdjustPolicy(QtGui.QComboBox.AdjustToContents)
        self.comboForms.setObjectName(_fromUtf8("comboForms"))
        self.horizontalLayout.addWidget(self.comboForms)
        self.searchButton = QtGui.QPushButton(self.widget)
        self.searchButton.setText(_fromUtf8(""))
        icon1 = QtGui.QIcon()
        icon1.addPixmap(QtGui.QPixmap(_fromUtf8(":/rPrj/icons/Zoom16.gif")), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.searchButton.setIcon(icon1)
        self.searchButton.setObjectName(_fromUtf8("searchButton"))
        self.horizontalLayout.addWidget(self.searchButton)
        self.horizontalLayout.setStretch(0, 1)
        self.verticalLayout.addWidget(self.widget)
        self.treeView = QtGui.QTreeView(RprjExplorerWidget)
        self.treeView.setEditTriggers(QtGui.QAbstractItemView.NoEditTriggers)
        self.treeView.setDragEnabled(True)
        self.treeView.setDragDropMode(QtGui.QAbstractItemView.DragDrop)
        self.treeView.setDefaultDropAction(QtCore.Qt.MoveAction)
        self.treeView.setSortingEnabled(True)
        self.treeView.setAnimated(True)
        self.treeView.setObjectName(_fromUtf8("treeView"))
        self.verticalLayout.addWidget(self.treeView)

        self.retranslateUi(RprjExplorerWidget)
        QtCore.QMetaObject.connectSlotsByName(RprjExplorerWidget)

    def retranslateUi(self, RprjExplorerWidget):
        RprjExplorerWidget.setWindowTitle(QtGui.QApplication.translate("RprjExplorerWidget", "Explorer", None, QtGui.QApplication.UnicodeUTF8))
        self.searchFolder.setToolTip(QtGui.QApplication.translate("RprjExplorerWidget", "Search items", None, QtGui.QApplication.UnicodeUTF8))
        self.searchButton.setToolTip(QtGui.QApplication.translate("RprjExplorerWidget", "Search", None, QtGui.QApplication.UnicodeUTF8))
        self.searchButton.setShortcut(QtGui.QApplication.translate("RprjExplorerWidget", "Ctrl+F", None, QtGui.QApplication.UnicodeUTF8))

import apps_rc
