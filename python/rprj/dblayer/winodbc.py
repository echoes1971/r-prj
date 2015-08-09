#
# @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: winodbc.py $
# @package dblayer
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

# -*- coding: utf-8 -*-

import os,sys

# Path to RRA python framework: start.
mypath = os.path.realpath( os.path.dirname(os.path.realpath(__file__)) +'/../../../python')
if not os.path.exists( mypath):
	mypath = os.path.realpath( os.path.dirname(os.path.realpath(__file__)) +'/../../python')
if not mypath in sys.path:
	sys.path.append(mypath)
# Required to redirect outout to a file
import codecs
sys.stdout = codecs.lookup('utf8')[-1](sys.stdout)   # 4
# Path to RRA python framework: end.


from rprj.dblayer import *

import odbc

class WinOdbcConnectionProvider(DBConnectionProvider):
	"""WinOdbc Connection provider"""
	
	__super_init = DBConnectionProvider.__init__
	
	def __init__(self, dsn, host=None, db=None, user=None, pwd=None, verbose=1):
		self.__super_init( host, db, user, pwd, verbose )
		self.dsn=dsn
	
	def getConnection(self):
		if self._conn is None:
			try:
				self._conn = odbc.odbc( self.dsn )
			except Exception, e:
				if self._verbose: print "WinOdbcConnectionProvider.getConnection: Error=%s" % (e)
		return self._conn
	
	def freeConnection(self,conn):
		try:
			conn.close()
		except Exception, e:
			if self._verbose: print "WinOdbcConnectionProvider.freeConnection: Error=%s" % (e)
		del conn
		del self._conn
		self._conn = None
	
	def getDBType(self):
		return "WinODBC"
	
	def _description2names(self, desc):
		"""Converte la description del resultset fornita dalle api DB
		in un array di nomi"""
		ret = []
		for d in desc:
			ret.append( d[0] )
		return ret
	def filterAccessName(self, accessName):
		return accessName.replace("[","").replace("]","").replace(" ","") #.replace(" ","_")
	def description2sql(self,desc,debug=False):
		"""(name, type_code, display_size, internal_size, precision, scale, null_ok)"""
		if debug: print "(name, type_code, display_size, internal_size, precision, scale, null_ok)"
		if debug: print desc
		ret=[]
		for d in desc:
			if debug: print d
			name, type_code, display_size, internal_size, precision, scale, null_ok = d
			tmp=["",""]
			tmp.append(name)
			if type_code=='STRING':
				if display_size==internal_size:
					tmp.append("varchar(%s)"%internal_size)
				elif display_size==0 and internal_size>0:
					tmp.append("text")
			elif type_code=='NUMBER':
				tmp.append("int(%s)"%internal_size)
			elif type_code=='DATE':
				tmp.append("char(%s)"%internal_size)
			else:
				tmp.append("%s(%s,%s,%s,%s)" % (type_code, display_size, internal_size, precision, scale))
			if not null_ok:
				tmp.append("not null")
			ret.append( " ".join(tmp) )
		return ",\n".join(ret)
	def table2sql(self,tablename,keys=[],debug=False):
		_tablename = self.filterAccessName(tablename)
		cursor = self.getConnection().cursor()
		cursor.execute( "select * from %s" % tablename )
		names = None
		ret = ["create table %s ( " % _tablename]
		try:
			names = self._description2names(cursor.description)
			ret.append("%s," % self.description2sql(cursor.description,debug))
		except TypeError,e:
			if self._verbose: print "DBMgr.select: ECCEZIONE=%s (%s)" % (e,searchString)
			pass
		cursor.close()
		ret.append("  primary key (%s)" % (",".join(keys)) )
		ret.append(");")
		return "\n".join(ret)
	def dumpData(self,tablename, debug=False):
		_tablename = self.filterAccessName(tablename)
		cursor = self.getConnection().cursor()
		cursor.execute( "select * from %s" % tablename )
		names = None
		ret = []
		try:
			names = self._description2names(cursor.description)
			listavalori = cursor.fetchall()
			numres = len(listavalori)
			for i in range( numres ):
				riga=[u"insert into %s values (" % _tablename]
				tmp=[]
				for n in range(0,len(names)):
					if listavalori[ i ][n] is None:
						tmp.append("NULL")
						continue
					type_code=cursor.description[n][1]
					valore = "%s"%listavalori[ i ][n]
					valore = valore.replace(u'\u2013','-')
					valore = valore.replace(u'\u2019','\'')
					valore = valore.replace(u'\u2026',"...")
					valore = valore.replace(u'\u20ac',"<Euro>")
					valore = valore.replace('\'','\'\'')
					valore = valore.replace('\r\n','\\r\\n')
					valore = valore.replace('\n','\\n')
					if type_code=='STRING' or type_code=='DATE':
						tmp.append( u"'%s'" % valore )
					else:
						tmp.append( u"%s" % valore )
				riga.append(u",".join(tmp))
				riga.append( u")" )
				try:
					#print "".join(riga)
					ret.append("".join(riga))
				except Exception,e:
					if debug:
						for n in range(0,len(names)):
							valore = "%s"%listavalori[ i ][n]
							valore = valore.replace(u'\u2013','-')
							valore = valore.replace(u'\u2019','\'')
							valore = valore.replace(u'\u2026',"...")
							valore = valore.replace(u'\u20ac','<Euro>')
							print names[n],valore
		except TypeError,e:
			if self._verbose: print "DBMgr.select: ECCEZIONE=%s" % (e)
			pass
		cursor.close()
		return u";\n".join(ret)

	def execute(self,searchString):
		"""Returns a list of DBE according to the searchString"""
		if self._verbose: print "DBMgr.select: searchString=%s" % ( searchString )
		cursor = self.getConnection().cursor()
		cursor.execute( searchString )
		#numres = cursor.rowcount
		names = None
		ret = []
		try:
			#print "cursor:",dir(cursor)
			#print "description:",cursor.description
			names = self._description2names(cursor.description)
			listavalori = cursor.fetchall()
			numres = len(listavalori)
			if self._verbose: print "DBMgr.select: found %s rows." % ( numres )
			for i in range( numres ):
				tmp={}
				for n in range(0,len(names)):
					#if isDateTime(values[i]): # values[i]=string2datetime(values[i])
						#setattr( self, names[i] , string2datetime(values[ i ]) )
					#else:
					tmp[names[n]] = listavalori[ i ][n]
				ret.append( tmp )
		except TypeError,e:
			if self._verbose: print "DBMgr.select: ECCEZIONE=%s (%s)" % (e,searchString)
			pass
		cursor.close()
		return ret
