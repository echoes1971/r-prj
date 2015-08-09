# -*- coding: utf-8 -*-

# Form implementation generated from reading ui file 'mainwindow.ui'
#
# Created: Sat Jun 16 12:32:24 2012
#      by: PyQt4 UI code generator 4.9.1
#
# WARNING! All changes made in this file will be lost!

from PyQt4 import QtCore, QtGui

try:
    _fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
    _fromUtf8 = lambda s: s

class Ui_MainWindow(object):
    def setupUi(self, MainWindow):
        MainWindow.setObjectName(_fromUtf8("MainWindow"))
        MainWindow.resize(800, 600)
        MainWindow.setDockNestingEnabled(True)
        self.centralwidget = QtGui.QWidget(MainWindow)
        self.centralwidget.setObjectName(_fromUtf8("centralwidget"))
        self.verticalLayout = QtGui.QVBoxLayout(self.centralwidget)
        self.verticalLayout.setMargin(0)
        self.verticalLayout.setObjectName(_fromUtf8("verticalLayout"))
        self.stackedWidget = QtGui.QStackedWidget(self.centralwidget)
        self.stackedWidget.setObjectName(_fromUtf8("stackedWidget"))
        self.verticalLayout.addWidget(self.stackedWidget)
        MainWindow.setCentralWidget(self.centralwidget)
        self.menubar = QtGui.QMenuBar(MainWindow)
        self.menubar.setGeometry(QtCore.QRect(0, 0, 800, 22))
        self.menubar.setNativeMenuBar(False)
        self.menubar.setObjectName(_fromUtf8("menubar"))
        self.menuMain = QtGui.QMenu(self.menubar)
        self.menuMain.setObjectName(_fromUtf8("menuMain"))
        MainWindow.setMenuBar(self.menubar)
        self.statusbar = QtGui.QStatusBar(MainWindow)
        self.statusbar.setObjectName(_fromUtf8("statusbar"))
        MainWindow.setStatusBar(self.statusbar)
        self.dockWidget = QtGui.QDockWidget(MainWindow)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Minimum, QtGui.QSizePolicy.Minimum)
        sizePolicy.setHorizontalStretch(0)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(self.dockWidget.sizePolicy().hasHeightForWidth())
        self.dockWidget.setSizePolicy(sizePolicy)
        self.dockWidget.setFloating(False)
        self.dockWidget.setFeatures(QtGui.QDockWidget.DockWidgetMovable)
        self.dockWidget.setObjectName(_fromUtf8("dockWidget"))
        self.dockWidgetContents = QtGui.QWidget()
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Fixed, QtGui.QSizePolicy.Preferred)
        sizePolicy.setHorizontalStretch(0)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(self.dockWidgetContents.sizePolicy().hasHeightForWidth())
        self.dockWidgetContents.setSizePolicy(sizePolicy)
        self.dockWidgetContents.setObjectName(_fromUtf8("dockWidgetContents"))
        self.verticalLayout_2 = QtGui.QVBoxLayout(self.dockWidgetContents)
        self.verticalLayout_2.setMargin(4)
        self.verticalLayout_2.setObjectName(_fromUtf8("verticalLayout_2"))
        self.listWidget = QtGui.QListWidget(self.dockWidgetContents)
        sizePolicy = QtGui.QSizePolicy(QtGui.QSizePolicy.Expanding, QtGui.QSizePolicy.Expanding)
        sizePolicy.setHorizontalStretch(0)
        sizePolicy.setVerticalStretch(0)
        sizePolicy.setHeightForWidth(self.listWidget.sizePolicy().hasHeightForWidth())
        self.listWidget.setSizePolicy(sizePolicy)
        self.listWidget.setMinimumSize(QtCore.QSize(50, 0))
        self.listWidget.setMaximumSize(QtCore.QSize(100, 16777215))
        self.listWidget.setHorizontalScrollBarPolicy(QtCore.Qt.ScrollBarAlwaysOff)
        self.listWidget.setEditTriggers(QtGui.QAbstractItemView.NoEditTriggers)
        self.listWidget.setSelectionBehavior(QtGui.QAbstractItemView.SelectRows)
        self.listWidget.setIconSize(QtCore.QSize(50, 50))
        self.listWidget.setTextElideMode(QtCore.Qt.ElideNone)
        self.listWidget.setMovement(QtGui.QListView.Static)
        self.listWidget.setFlow(QtGui.QListView.TopToBottom)
        self.listWidget.setSpacing(25)
        self.listWidget.setViewMode(QtGui.QListView.IconMode)
        self.listWidget.setUniformItemSizes(True)
        self.listWidget.setObjectName(_fromUtf8("listWidget"))
        self.verticalLayout_2.addWidget(self.listWidget)
        self.dockWidget.setWidget(self.dockWidgetContents)
        MainWindow.addDockWidget(QtCore.Qt.DockWidgetArea(1), self.dockWidget)
        self.actionQuit = QtGui.QAction(MainWindow)
        self.actionQuit.setObjectName(_fromUtf8("actionQuit"))
        self.actionReload_Style = QtGui.QAction(MainWindow)
        self.actionReload_Style.setObjectName(_fromUtf8("actionReload_Style"))
        self.actionLogin = QtGui.QAction(MainWindow)
        self.actionLogin.setObjectName(_fromUtf8("actionLogin"))
        self.menuMain.addAction(self.actionLogin)
        self.menuMain.addSeparator()
        self.menuMain.addAction(self.actionReload_Style)
        self.menuMain.addSeparator()
        self.menuMain.addAction(self.actionQuit)
        self.menubar.addAction(self.menuMain.menuAction())

        self.retranslateUi(MainWindow)
        QtCore.QObject.connect(self.listWidget, QtCore.SIGNAL(_fromUtf8("currentRowChanged(int)")), self.stackedWidget.setCurrentIndex)
        QtCore.QMetaObject.connectSlotsByName(MainWindow)

    def retranslateUi(self, MainWindow):
        MainWindow.setWindowTitle(QtGui.QApplication.translate("MainWindow", ":: R-Prj ::", None, QtGui.QApplication.UnicodeUTF8))
        self.menuMain.setTitle(QtGui.QApplication.translate("MainWindow", "Main", None, QtGui.QApplication.UnicodeUTF8))
        self.actionQuit.setText(QtGui.QApplication.translate("MainWindow", "Quit", None, QtGui.QApplication.UnicodeUTF8))
        self.actionQuit.setShortcut(QtGui.QApplication.translate("MainWindow", "Ctrl+Q", None, QtGui.QApplication.UnicodeUTF8))
        self.actionReload_Style.setText(QtGui.QApplication.translate("MainWindow", "Reload Style", None, QtGui.QApplication.UnicodeUTF8))
        self.actionReload_Style.setShortcut(QtGui.QApplication.translate("MainWindow", "Ctrl+T", None, QtGui.QApplication.UnicodeUTF8))
        self.actionLogin.setText(QtGui.QApplication.translate("MainWindow", "Login", None, QtGui.QApplication.UnicodeUTF8))
        self.actionLogin.setShortcut(QtGui.QApplication.translate("MainWindow", "Ctrl+L", None, QtGui.QApplication.UnicodeUTF8))

