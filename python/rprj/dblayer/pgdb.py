# -*- coding: utf-8 -*-
#
# @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: mydb.py $
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

from rprj.dblayer import *

from psycopg2 import *

class PGConnectionProvider(DBConnectionProvider):
	"""PostgreSQL Connection provider"""
	
	__super_init = DBConnectionProvider.__init__
	
	def __init__(self, host, db, user, pwd, verbose=0):
		self.__super_init( host, db, user, pwd, verbose )

	def getConnection(self):
		if self._conn is None:
			try:
				connString = "host=%s dbname=%s user=%s password=%s" % (self._host,self._db,self._user,self._pwd)
				#if self._verbose: print "PGConnectionProvider.getConnection: connString=%s" % ( connString )
				self._conn = connect( connString )
				self._conn.autocommit = True
			except Exception, e:
				if self._verbose: print "PGConnectionProvider.getConnection: Error=%s" % (e)
		return self._conn

	def freeConnection(self,conn):
		try:
			conn.close()
		except Exception, e:
			if self._verbose: print "PGConnectionProvider.freeConnection: Error=%s" % (e)
		del conn
		del self._conn
		self._conn = None

	def getDBType(self):
		return "POSTGRESQL"

	def dbeType2dbType(self,dbetype):
		"""TO OVERRIDE"""
		ret = dbetype
		if dbetype=="uuid" or dbetype==u"uuid":
			ret = "varchar(16)"
		elif dbetype=="datetime":
			ret = "timestamp"
		return ret
	def dbType2dbeType(self,dbetype):
		"""TO OVERRIDE"""
		ret = dbetype
		if dbetype=="varchar(16)":
			ret = "uuid"
		elif dbetype=="timestamp":
			ret = "datetime"
		return ret
	def dbeConstraints2dbConstraints(self,constraints):
		return constraints\
			.replace("not null default '0000-00-00'","default null")\
			.replace("not null default '0000-00-00 00:00:00'","default null")
			#.replace("00:00:00","00:00")

