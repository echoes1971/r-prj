# -*- coding: utf-8 -*-

#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: editwidgetcontroller.py $
# @package apps
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

import os
from PyQt4 import QtCore, QtGui, Qt
try:
	_fromUtf8 = QtCore.QString.fromUtf8
except AttributeError:
	_fromUtf8 = lambda s: s

class qtComponentException(Exception):
	pass

class qtComponent(QtCore.QObject):
	def __init__(self, config, parent=None):
		QtCore.QObject.__init__(self)
		self.config = config
		self.parent = parent
		
		self.logfile = file( self.getPref( self.__class__.__name__ ,"logfile", "%s%s.%s.log"%(os.path.expanduser("~"), os.path.sep,self.__class__.__name__) ) ,'a') #'w')
		
		self._statusText=""
		
		self.init()
		self.mythreads={}
		# QT
		QtCore.QObject.connect(self, QtCore.SIGNAL('setProgress(int)'), self.SetProgress )
		QtCore.QObject.connect(self, QtCore.SIGNAL('setStatusText(PyQt_PyObject)'), self.SetStatusText )
	def setParent(self,p):
		self.parent = p
# ############################## DESCRIPTION ###################################
	def getName(self):
		return self.__class__.__name__
	def getDescription(self):
		return self.__class__.__doc__
	def getIcon(self):
		raise qtComponentException("qtComponent.getIcon: NOT YET IMPLEMENTED!")
# ################################## GUI #######################################
	def getMenu(self,parent):
		return None
	def getPanel(self,parentWidget=None):
		raise qtComponentException("getPanel: NOT YET IMPLEMENTED!")
	def getPreferencesPanel(self):
		raise qtComponentException("getPreferencesPanel: NOT YET IMPLEMENTED!")
# ################################ COMPONENT ###################################
	def init(self):
		"""Sequenza di inizializzazione"""
		pass
	def finalize(self):
		"""Sequenza di terminazione"""
		pass
# ############################## PREFERENCES ###################################
	def SaveUI2Config(self):
		pass
	def RestoreUIFromConfig(self):
		pass
	def getPref(self,section,option,default=None):
		if not self.parent is None:
			return self.parent.getPref(section,option,default)
		if isinstance(default,bool): default="%s" % (default)
		if not self.config.has_section(section):
			self.config.add_section(section)
		if not self.config.has_option(section,option):# and default:
			self.config.set(section,option,default)
		return self.config.get(section,option,default)
	def setPref(self,section,option,valore):
		if not self.parent is None:
			self.parent.setPref(section,option,valore)
			return
		if not self.config.has_section(section):
			self.config.add_section(section)
		if isinstance( valore, bool ): valore ="%s"%(valore)
		self.config.set(section,option,valore)
	def SaveConfig(self):
		self.config.write( file(path_to_config,'w') )
# ############################## ... ###################################
	def Alert(self,msg, eccezione=None):
		if self.parent is None:
			return
		self.parent.Alert(msg,eccezione)
	def Confirm(self,msg):
		if self.parent is None:
			return True
		return self.parent.Confirm(msg)
	def SetStatusText(self,msg):
		if self.parent is None:
			return
		self.parent.SetStatusText(msg)
	def SetProgress(self,perc):
		if self.parent is None:
			return
		self.parent.SetProgress(perc)
# ################################ UTILITY ######################################
	def Log(self, message, level=0):
		self.logfile.write( "%s::%s> %s\n" % (datetime.datetime.now().strftime("%Y%m%d%H%M%S"), self.__class__.__name__, message) )
		self.logfile.flush()
	def SetStatusText(self,msg):
		self.parent.SetStatusText(msg)
		pass
	def emitStatusText(self,msg):
		self.emit(QtCore.SIGNAL('setStatusText(PyQt_PyObject)'),msg)
	def SetProgress(self,perc):
		self.parent.SetProgress(perc)
	def emitProgress(self,perc):
		self.emit(QtCore.SIGNAL('setProgress(int)'),perc)
# ############################# BUSINESS LOGIC #################################
	pass


class qtComponentContainer(qtComponent):
	def __init__(self, config, parent=None):
		qtComponent.__init__(self, config, parent)
		# BL
		self.components={}
	def addComponent(self,c):
		tmp_page = c.getPanel()
		self.components[c.getName()]=[c,tmp_page]
	pass
