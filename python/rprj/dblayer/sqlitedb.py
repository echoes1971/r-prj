# -*- coding: utf-8 -*-
#
# @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: sqlitedb.py $
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

import os,sys

from rprj.dblayer import *

major_v,minor_v, subminor_v,etichetta,altri = sys.version_info
if major_v==2 and minor_v==4:
	from pysqlite2 import dbapi2 as sqlite
else:
	from sqlite3 import dbapi2 as sqlite

def convert_text(s):
	# XXX do not use Unicode
	return s.decode("utf-8")
sqlite.register_converter("TEXT", convert_text)


class SQLiteConnectionProvider(DBConnectionProvider):
	"""SQLite Connection provider"""
	def __init__(self, host, db, user, pwd, verbose=0):
		"""@param host?
		@param db: path al file
		@param user?
		@param pwd?
		"""
		DBConnectionProvider.__init__(self,  host, db, user, pwd, verbose )
	def customInit(self):
		"""Redefine this for idiosynchratic behaviour"""
		pass
	def getConnection(self):
		if self._conn is None:
			try:
				self._conn = sqlite.connect(self._db)#, detect_types=sqlite.PARSE_DECLTYPES )
				# 2012.03.16: start.
				# 2012.03.16: FIXED - Now handles non utf8 encoded strings :-)))
				self._conn.text_factory = lambda x: unicode(x, "utf-8", "ignore")\
						.replace(u"\\r",u"\r")\
						.replace(u"\\n",u"\n")\
						.replace(u"\\t",u"\t")\
						.replace(u"\\\"",u"\"")\
						.replace(u"\\\\",u"\\")
				# 2012.03.16: end.
			except Exception, e:
				if self._verbose: print "SQLiteConnectionProvider.getConnection: db=%s Error=%s" % (self._db,e)
		return self._conn
	def freeConnection(self,conn):
		try:
			conn.close()
		except Exception, e:
			if self._verbose: print "SQLiteConnectionProvider.freeConnection: Error=%s" % (e)
		del conn
		del self._conn
		self._conn = None
	def getDBType(self):
		return "SQLite"
	def getColumnsForTable(self,  tablename):
		"""@return dictionary with column definitions, None if the table does not exists"""
		if self._verbose:
			#print "SQLiteConnectionProvider.getColumnsForTable: tablename=%s" % (tablename)
			#print "SQLiteConnectionProvider.getColumnsForTable: %s" % dir(self._conn)
			pass
		cursor = self.getConnection().cursor()
		ret = {}
		searchString = "select * from %s where 1=0" % tablename
		try:
			cursor.execute( searchString )
			for d in cursor.description:
				ret[ d[0] ] = [ d[0], d[1], d[2], d[3], d[4], d[5], d[6] ]
				#if self._verbose:
				#	print "SQLiteConnectionProvider.getColumnsForTable: d=(%s,%s,%s,%s,%s,%s,%s) %s" % (d[0], d[1], d[2], d[3], d[4], d[5], d[6], len(d))
		except sqlite.OperationalError, oe:
				# Table does not exists
				#print "SQLiteConnectionProvider.getColumnsForTable: OperationalError=%s (%s)" % (oe,searchString)
				ret = None
		except Exception,e:
			if self._verbose:
				print "SQLiteConnectionProvider.getColumnsForTable: ECCEZIONE=%s (%s)" % (e,searchString)
				print "".join(traceback.format_tb(sys.exc_info()[2]))
			raise e
		finally:
			cursor.close()
		#print "SQLiteConnectionProvider.getColumnsForTable: ret=%s" % (ret)
		return ret
	def getLocalFilePath(self):
		dest_dir = "%s%sfiles" % ( os.path.sep.join( self.getDB().split( os.path.sep )[:-1] ), os.path.sep)
		return dest_dir
	def uploadFile(self,local_filename):
		dest_dir = "%s%sfiles" % ( os.path.sep.join( self.getDB().split( os.path.sep )[:-1] ), os.path.sep)
		if not os.path.exists(dest_dir): os.mkdir(dest_dir)
		if self._verbose: print "SQLiteConnectionProvider.uploadFile: dest_dir:",dest_dir
		# Short local filename
		filename_corto = local_filename[ len(os.path.dirname(local_filename)): ]
		if filename_corto.startswith('/'):
			filename_corto = filename_corto[1:]
		if self._verbose: print "SQLiteConnectionProvider.uploadFile: filename_corto:",filename_corto
		dest_file = "%s%s%s" % (dest_dir,os.path.sep,filename_corto)
		if self._verbose: print "SQLiteConnectionProvider.uploadFile: dest_file:",dest_file
		# Load file
		myfile = file( local_filename, 'rb' ).read()
		# Upload
		file(dest_file,'wb').write( myfile )
		return dest_file
	def downloadFile(self,remote_filename,local_filename,view_thumbnail=False):
		"""@par uuid
		@par local_filename where to download the file
		@ret local_filename if ok, None otherwise"""
		file(local_filename,'wb').write( file(remote_filename,'rb').read() )
		return local_filename
