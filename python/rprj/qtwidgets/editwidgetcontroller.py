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

pyuic4 editwidget.ui -o editwidget.py
pyrcc4 rprj.qrc -o rprj_rc.py

"""
from PyQt4 import QtCore,QtGui
import editwidget

class EditWidgetController(QtGui.QWidget):
	__EDIT_HEADER="""<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">
<html><head><meta name="qrichtext" content="1" /><style type="text/css">
p, li { white-space: pre-wrap; }
</style></head><body style=" font-family:'Sans Serif'; font-size:10pt; font-weight:400; font-style:normal;">
"""
	__EDIT_FOOTER="""</body></html>"""
	def __init__(self,parent,objectName):
		QtGui.QWidget.__init__(self,parent)
		self.uiEdit = editwidget.Ui_EditWidget()
		self.uiEdit.setupUi(self)
		self.setObjectName(objectName)
		self.uiEdit.comboFontSize.addItem("6")
		self.uiEdit.comboFontSize.addItem("7")
		self.uiEdit.comboFontSize.addItem("8")
		self.uiEdit.comboFontSize.addItem("9")
		self.uiEdit.comboFontSize.addItem("10")
		self.uiEdit.comboFontSize.addItem("11")
		self.uiEdit.comboFontSize.addItem("12")
		self.uiEdit.comboFontSize.addItem("14")
		self.uiEdit.comboFontSize.addItem("16")
		self.uiEdit.comboFontSize.addItem("18")
		self.uiEdit.comboFontSize.addItem("20")
		self.uiEdit.comboFontSize.addItem("22")
		self.uiEdit.comboFontSize.addItem("24")
		self.uiEdit.comboFontSize.addItem("26")
		self.uiEdit.comboFontSize.addItem("28")
		self.uiEdit.comboFontSize.addItem("36")
		self.uiEdit.comboFontSize.addItem("48")
		self.uiEdit.comboFontSize.addItem("72")
		self.uiEdit.comboFontSize.setCurrentIndex(3)
		self.uiEdit.comboInsertList.addItem( "", -1)
		self.uiEdit.comboInsertList.addItem( QtGui.QApplication.translate("EditWidgetController","Disc", None, QtGui.QApplication.UnicodeUTF8), QtGui.QTextListFormat.ListDisc)
		self.uiEdit.comboInsertList.addItem( QtGui.QApplication.translate("EditWidgetController","Circle", None, QtGui.QApplication.UnicodeUTF8), QtGui.QTextListFormat.ListCircle)
		self.uiEdit.comboInsertList.addItem( QtGui.QApplication.translate("EditWidgetController","Square", None, QtGui.QApplication.UnicodeUTF8), QtGui.QTextListFormat.ListSquare)
		self.uiEdit.comboInsertList.addItem( "123", QtGui.QTextListFormat.ListDecimal)
		self.uiEdit.comboInsertList.addItem( "abc", QtGui.QTextListFormat.ListLowerAlpha)
		self.uiEdit.comboInsertList.addItem( "ABC", QtGui.QTextListFormat.ListUpperAlpha)
		self.uiEdit.comboInsertList.setCurrentIndex(0)
	def setText(self,text):
		self.uiEdit.textEdit.setHtml(text)
	def text(self):
		ret = u"%s" % self.uiEdit.textEdit.toHtml()
		ret = ret.encode('ascii','xmlcharrefreplace')
		ret = ret.replace(EditWidgetController.__EDIT_HEADER,"").replace(EditWidgetController.__EDIT_FOOTER,"")
		ret = ret.replace("font-weight:600;","font-weight:bold;")
		start = ret.find('<body')
		if start>=0:
			ret = ret[start+1:]
			end = ret.find('>')
			ret = ret[end+1:]
		#print "EditWidgetController.text: ret=%s"%ret
		return ret
	def slotBold(self):
		if self.uiEdit.textEdit.fontWeight()==QtGui.QFont.Bold:
			self.uiEdit.textEdit.setFontWeight(QtGui.QFont.Normal)
		else:
			self.uiEdit.textEdit.setFontWeight(QtGui.QFont.Bold)
		self.uiEdit.textEdit.setFocus()
	def slotItalic(self):
		self.uiEdit.textEdit.setFontItalic( not self.uiEdit.textEdit.fontItalic() )
		self.uiEdit.textEdit.setFocus()
	def slotUnderline(self):
		self.uiEdit.textEdit.setFontUnderline( not self.uiEdit.textEdit.fontUnderline() )
		self.uiEdit.textEdit.setFocus()
	def slotStrikeout(self):
		f = self.uiEdit.textEdit.currentFont()
		f.setStrikeOut( not f.strikeOut() )
		self.uiEdit.textEdit.setCurrentFont(f)
		self.uiEdit.textEdit.setFocus()
	def slotTextChanged(self):
		#if(self.uiEdit.textEdit.toPlainText().size()==0)
			#return;
		#emit textChanged()
		#if(!this->windowTitle().endsWith(QChar('*')))
			#this->setWindowTitle( this->windowTitle().append(QChar('*')) )
		#print "EditWidgetController.slotTextChanged: TODO"
		pass
	def slotTextColor(self):
		c = QtGui.QColorDialog.getColor( self.uiEdit.textEdit.textColor() )
		self.uiEdit.textEdit.setTextColor(c)
		self.uiEdit.textEdit.setFocus()
	def slotFillColor(self):
		c = QtGui.QColorDialog.getColor( self.uiEdit.textEdit.textBackgroundColor() )
		self.uiEdit.textEdit.setTextBackgroundColor(c)
		self.uiEdit.textEdit.setFocus()
	def slotFontSize(self,s):
		mysize,success = s.toInt()
		if not success:
			return
		if mysize<=0: return
		f = self.uiEdit.textEdit.currentFont()
		f.setPointSize( mysize )
		self.uiEdit.textEdit.setCurrentFont(f)
	def slotAlignLeft(self):
		self.uiEdit.textEdit.setAlignment(QtCore.Qt.AlignLeft)
		self.uiEdit.textEdit.setFocus()
	def slotAlignCenter(self):
		self.uiEdit.textEdit.setAlignment(QtCore.Qt.AlignCenter)
		self.uiEdit.textEdit.setFocus()
	def slotAlignRight(self):
		self.uiEdit.textEdit.setAlignment(QtCore.Qt.AlignRight)
		self.uiEdit.textEdit.setFocus()
	def slotAlignFill(self):
		self.uiEdit.textEdit.setAlignment(QtCore.Qt.AlignJustify)
		self.uiEdit.textEdit.setFocus()
	def slotCursorPositionChanged(self):
		f = self.uiEdit.textEdit.currentFont();
		i = self.uiEdit.fontComboBox.findText(f.family() )
		if i>=0:
			self.uiEdit.fontComboBox.setCurrentIndex(i)
		i = self.uiEdit.comboFontSize.findText( "%s" % f.pointSize() )
		if i>=0:
			self.uiEdit.comboFontSize.setCurrentIndex(i)
	def slotInsertList(self,i):
		if i==0: return
		mystyle,success = self.uiEdit.comboInsertList.itemData(i).toInt()
		if not success:
			return
		cursor = self.uiEdit.textEdit.textCursor()
		cursor.insertList( mystyle )
		self.uiEdit.textEdit.setTextCursor(cursor)
		self.uiEdit.textEdit.setFocus()
	def slotIndent(self):
		print "EditWidgetController.slotIndent: TODO"
	def slotUnindent(self):
		print "EditWidgetController.slotUnindent: TODO"
	def slotInsertHR(self):
		cursor = self.uiEdit.textEdit.textCursor()
		cursor.insertHtml( "<hr />" )
		self.uiEdit.textEdit.setTextCursor(cursor)
		self.uiEdit.textEdit.setFocus()
	def slotInsertLink(self):
		myUrl, success = QtGui.QInputDialog.getText(self,QtGui.QApplication.translate("EditWidgetController","Insert Link", None, QtGui.QApplication.UnicodeUTF8),QtGui.QApplication.translate("EditWidgetController","Enter URL:", None, QtGui.QApplication.UnicodeUTF8),QtGui.QLineEdit.Normal,"http://")
		if not success: return
		myDesc, success = QtGui.QInputDialog.getText(self,QtGui.QApplication.translate("EditWidgetController","Insert Link", None, QtGui.QApplication.UnicodeUTF8),QtGui.QApplication.translate("EditWidgetController","Enter link description:", None, QtGui.QApplication.UnicodeUTF8),QtGui.QLineEdit.Normal,"Link")
		if not success: return
		cursor = self.uiEdit.textEdit.textCursor()
		cursor.insertHtml( "<a href=\"%s\">%s</a>" % (myUrl,myDesc) )
		self.uiEdit.textEdit.setTextCursor(cursor)
		self.uiEdit.textEdit.setFocus()
	def slotInsertImage(self):
		#imageDialog.setAcceptMode(QFileDialog::AcceptOpen);
		#//d.setFilters( myfilters );
		#imageDialog.exec();
		#QStringList l = imageDialog.selectedFiles();
		#if( l.size()<=0 )
			#return;
		#QFileInfo fi(l[0]);
		#QImage img;
		#bool loaded = img.load(l[0]);
		#if(!loaded) {
			#emit SetStatusText(tr("Unable to load image: ").append( fi.fileName() ));
			#return;
		#}
		#QTextCursor cursor = self.uiEdit.textEdit.textCursor();
		#cursor.insertImage(img,fi.fileName());
		#self.uiEdit.textEdit.setTextCursor(cursor);
		#emit ImageAdded(fi.fileName(), l[0]);
		#self.uiEdit.textEdit.setFocus();
		print "EditWidgetController.slotInsertImage: TODO"

