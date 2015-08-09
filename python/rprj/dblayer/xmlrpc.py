# -*- coding: utf-8 -*-
#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: xmlrpc.py $
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
from rprj.net.XmlrpcTransports import CookieTransport,SafeCookieTransport
from xmlrpclib import *
import codecs, datetime,os,sys,traceback
import threading # TEST

# 1 mega = 1048576
UPLOAD_SPLIT_SIZE = 1048576

class RemoteDBE(DBEntity):
	"""Questa classe viene usata al posto delle vere DBE quando
	viene richiesta la 'clazz' al DBMgr"""
	def __init__(self, tablename, clazzname, remoteDBMgr, keys=None, fks=None,orderBy=None):
		self._tablename = tablename
		self._myclazzname = clazzname
		self._remoteDBMgr=remoteDBMgr
		self._keys=keys
		self._fks=fks
		self._orderBy=orderBy
	def __call__(self, tablename=None, names=None, values=None, attrs=None, keys=None): #keys={'id':'number'} ):
		if not tablename is None: self._tablename=tablename
		ret = RemoteDBE(self._tablename, self._myclazzname, self._remoteDBMgr, self.getKeys(), self.getFK(), self.getOrderBy() )
		DBEntity.__init__(ret, self._tablename, names, values, attrs, self.getKeys())
		ret._tablename = self._tablename
		ret._myclazzname = self._myclazzname
		return ret
	def getTypeName(self):
		"""Returns printable name of the type"""
		return self._myclazzname
	def getOrderBy(self):
		"""Ritorna l'array dei campi orderBy"""
		if self._orderBy is None:
			tmp = self._remoteDBMgr.execDBEMethod( self, "getOrderBy", [] )
			self._orderBy=[x.data for x in tmp[1] ]
		return self._orderBy
	def getKeys(self):
		"""Ritorna i nomi dei campi chiave"""
		if self._keys is None:
			# Recupero le chiavi
			tmp = self._remoteDBMgr.execDBEMethod( self, "getKeys", [] )
			self._keys={}
			for k in tmp[1].keys():
				v=tmp[1][k].data
				if v=='int': v='number'
				self._keys[k]=v
		return self._keys
	def getFK(self):
		"""Ritorna un array con le foreign keys per questa tabella"""
		if self._fks is None:
			# Recupero le FKs
			tmp = self._remoteDBMgr.execDBEMethod( self, "getFK", [] ) #['abc',123,True] )
			self._fks=[ForeignKey(f["colonna_fk"].data, f["tabella_riferita"].data, f["colonna_riferita"].data ) for f in tmp[1] ]
		return self._fks

class XmlrpcConnectionProvider(DBConnectionProvider):
	def __init__(self, host, db, user, pwd, verbose=0):
		"""host: here is the url of the server
		db: TODO is meaningless here?"""
		DBConnectionProvider.__init__(self, host, db, user, pwd, verbose)
		self.verbose = verbose
		if self._host.startswith('https'):
			self.transport = SafeCookieTransport()
		else:
			self.transport = CookieTransport()
		self._server = None
		self._cache = {}
		self.dbmgr = None # Needed by _xmlrpcToDbe
		self.semaforo = threading.Semaphore()
	def customInit(self):
		"""???"""
		pass
	def getConnection(self):
		self.semaforo.acquire(True)
		if self._server is None:
			self._server = Server(self._host,self.transport\
								,'utf-8'\
								#,verbose=1\
								)
		self.semaforo.release()
		return self
	def freeConnection(self, conn):
		self.semaforo.acquire(True)
		if not self._server is None:
			del self._server
		self._server = None
		self.semaforo.release()
	def getDBType(self):
		return "Xmlrpc"
	def isProxy(self):
		return True

	def _dbeToXmlrpc(self,dbe):
		ret={}
		for n in dbe.getNames():
			tmpvalue = dbe.getValue(n)
			if type(tmpvalue)==str or type(tmpvalue)==unicode:
				try:
					tmpvalue.decode('ascii')
				except Exception,e:
					tmpvalue=Binary(tmpvalue)	
			if isinstance( tmpvalue, datetime.datetime ):
				ret[n] = "%s" % tmpvalue
			else:
				ret[n] = tmpvalue
		return [ dbe.getTypeName(), ret ]
	def _xmlrpcToDbe(self, xmldbe):
		typename = xmldbe['_typename']
		xmldbe.pop('_typename')
		nomi = [ n for n in xmldbe.keys() ]
		valori = [ n for n in xmldbe.values() ]
		valori2=[]
		for v in valori:
			if isinstance(v, Binary): v = v.data
			#if isinstance(v,str): v=u"%s"%v
			if isDateTime(v): #rra.dblayer.isDateTime(v):
				#"0000-00-00 00:00:00"
				v2=None
				try:
					v2 = datetime.datetime(int(v[:4]),int(v[5:7]),int(v[8:10]),int(v[11:13]),int(v[14:16]),int(v[17:19]))
				except Exception,e:
					try:
						v2 = datetime.datetime(int(v[:4]),int(v[5:7]),int(v[8:10]),int(v[11:13]),int(v[14:16]))
					except Exception, e:
						try:
							v2 = datetime.time(int(v[11:13]),int(v[14:16]),int(v[17:19]))
						except Exception,e:
							v2 = datetime.time(int(v[11:13]),int(v[14:16]))
				valori2.append( v2 )
			else:
				valori2.append(v)
		if isinstance(typename, Binary): typename = typename.data
		myclazz = self.dbmgr.getClazzByTypeName(typename)
		if isinstance(myclazz,RemoteDBE):
			return myclazz( myclazz.getTableName(), names=nomi, values=valori2 )
		else:
			return myclazz( myclazz().getTableName(), names=nomi, values=valori2 )
	def getRegisteredTypes(self):
		"""Returns the registered types"""
		ret = {}
		try:
			self.semaforo.acquire(True)
			msg, lista = self._server.getTypeList()
			self.semaforo.release()
			self.lastMessages = msg.data
			for k,v in lista[0].iteritems():
				ret[k] = RemoteDBE(k,v.data,self)
			self._cache = ret
		except Exception, e:
			print "XmlrpcConnectionProvider.getRegisteredTypes: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "XmlrpcConnectionProvider.getRegisteredTypes: ECCEZIONE ############# Fine."
			raise DBLayerException( "Problemi di comunicazione col server: %s" % e )
		return ret
	def getClazzByTypeName(self, typename, case_sensitive=False):
		try:
			if self.verbose: print "XmlrpcConnectionProvider.getClazzByTypeName: typename=%s" % ( typename )
			if self._cache is None: self._cache = self.getRegisteredTypes()
			ret = DBEntity
			if self._cache.has_key('default'):
				ret = self._cache['default']
			for k in self._cache.keys():
				if self.verbose: print "DBEFactory.getClazzByTypeName: k=%s" % ( k )
				mytypename = ''
				if isinstance(self._cache[k],RemoteDBE):
					mytypename=self._cache[k].getTypeName()
				else:
					mytypename=self._cache[k]().getTypeName()
				if case_sensitive:
					if mytypename==typename:
						ret = self._cache[k]
						break
				else:
					if mytypename.lower()==typename.lower():
						ret = self._cache[k]
						break
			return ret
		except Exception, e:
			print "XmlrpcConnectionProvider.getClazzByTypeName: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "%s"%e
			print "XmlrpcConnectionProvider.getClazzByTypeName: ECCEZIONE ############# Fine."
			return self._cache['default']
	def getClazz(self, tablename):
		ret = None
		if self._cache.has_key( tablename ):
			ret = self._cache[tablename]
		else:
			ret=self._cache['default']
		return ret
	def insert(self, dbe):
		# Before Insert
		# 2012.04.04 dbe._before_insert(self.dbmgr)
		# 2012.04.04 dbe = self.dbmgr._before_insert(dbe)
		# Insert
		dbexml = self._dbeToXmlrpc(dbe)
		self.semaforo.acquire(True)
		tmp = self._server.insert( dbexml )
		self.semaforo.release()
		tmparray = tmp[1]
		self.lastMessages = tmp[0].data
		dbe = self._xmlrpcToDbe( tmparray[0] )
		# After Insert
		# 2012.04.04 dbe = self.dbmgr._after_insert(dbe)
		# 2012.04.04 dbe._after_insert(self.dbmgr)
		return dbe
	def update(self,dbe):
		# Before Update
		# 2012.04.04 dbe._before_update(self.dbmgr)
		# 2012.04.04 dbe = self.dbmgr._before_update(dbe)
		# Update
		dbexml = self._dbeToXmlrpc(dbe)
		self.semaforo.acquire(True)
		tmp = self._server.update( dbexml )
		self.semaforo.release()
		tmparray = tmp[1]
		self.lastMessages = tmp[0].data
		if len(tmparray)>0:
			dbe = self._xmlrpcToDbe( tmparray[0] )
			# After Update
			# 2012.04.04 dbe = self.dbmgr._after_update(dbe)
			# 2012.04.04 dbe._after_update(self.dbmgr)
		else:
			dbe = None
		return dbe
	def select(self,tablename,searchString):
		if self.verbose: print "XmlrpcConnectionProvider.select: tablename=%s" % (tablename)
		if self.verbose: print "XmlrpcConnectionProvider.select: searchString=%s" % (searchString)
		if self.verbose: print "XmlrpcConnectionProvider.select: start search=%s" % (datetime.datetime.now())
		try:
			self.semaforo.acquire(True)
			tmp = self._server.select( tablename, searchString )
			self.semaforo.release()
			res = tmp[1]
			self.lastMessages = tmp[0].data
		except DBLayerException, e:
			raise DBLayerException( "Problemi di comunicazione col server: %s" % e )
		if self.verbose: print "XmlrpcConnectionProvider.select: start xml->dbe=%s" % (datetime.datetime.now())
		ret = []
		for r in res:
			tmpdbe = self._xmlrpcToDbe(r)
			ret.append( tmpdbe )
		if self.verbose: print "XmlrpcConnectionProvider.select: end time = %s" % (datetime.datetime.now())
		return ret
	def delete(self,dbe):
		# Before Delete
		# 2012.04.04 dbe._before_delete(self.dbmgr)
		# 2012.04.04 dbe = self.dbmgr._before_delete(dbe)
		# Delete
		dbexml = self._dbeToXmlrpc(dbe)
		self.semaforo.acquire(True)
		tmp = self._server.delete( dbexml )
		self.semaforo.release()
		tmparray = tmp[1]
		self.lastMessages = tmp[0].data
		dbe = self._xmlrpcToDbe( tmparray[0] )
		# After Delete
		# 2012.04.04 dbe = self.dbmgr._after_delete(dbe)
		# 2012.04.04 dbe._after_delete(self.dbmgr)
		#dbe = None # 2012.04.04
		return dbe
	def search(self, dbe, uselike=1, orderby=None, ignore_deleted=True, full_object=True):
		casesensitive = 0
		if self.verbose: print "XmlrpcConnectionProvider.search: start time = %s" % (datetime.datetime.now())
		tablename = dbe.getTableName()
		# Search
		dbexml = self._dbeToXmlrpc(dbe)
		if self.verbose: print "XmlrpcConnectionProvider.search: dbexml = %s" % (dbexml)
		if self.verbose: print "XmlrpcConnectionProvider.search: uselike = %s" % (uselike)
		#if self.verbose: print "XmlrpcConnectionProvider.search: casesensitive = %s" % (casesensitive)
		if self.verbose: print "XmlrpcConnectionProvider.search: orderby = %s" % (orderby)
		try:
			if orderby is None: orderby=''
			self.semaforo.acquire(True)
			msg, lista = self._server.search( dbexml, uselike, casesensitive, orderby, ignore_deleted, full_object )
			self.semaforo.release()
			if self.verbose: print "XmlrpcConnectionProvider.search: msg=\n  ", "\n  ".join( ("%s"%msg).split("\n") )
			self.lastMessages = msg.data
		except Exception, e:
			print "XmlrpcConnectionProvider.search: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "XmlrpcConnectionProvider.search: ECCEZIONE ############# Fine."
			raise DBLayerException( "Problemi di comunicazione col server: %s" % e )
		ret = []
		for r in lista:
			tmpdbe = self._xmlrpcToDbe(r)
			ret.append( tmpdbe )
		if self.verbose: print "XmlrpcConnectionProvider.search: ret = %s" % ( len(ret) )
		if self.verbose: print "XmlrpcConnectionProvider.search: end time = %s" % (datetime.datetime.now())
		return ret
	def copy(self, dbe):
		raise DBLayerException("copy: not yet implemented")

# ################### Remote Server ###################
	def ping(self):
		self.semaforo.acquire(True)
		self.msg, ret = self._server.ping()
		self.semaforo.release()
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		return ret[0]
	def login(self,user,pwd):
		self.semaforo.acquire(True)
		self.msg, ret = self._server.login(user,pwd)
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.login: msg=%s"%self.msg
			print "XmlrpcConnectionProvider.login: ret=%s"%ret
		if len(ret)==1:
			tmpdbe = self._xmlrpcToDbe(ret[0])
			# The remote server clean the password value in the returned dbe :-) more safe
			tmpdbe.setValue('pwd',pwd)
			return tmpdbe
		return None
	def getLoggedUser(self):
		self.semaforo.acquire(True)
		self.msg, ret = self._server.getLoggedUser()
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.getLoggedUser: msg=%s"%self.msg
			print "XmlrpcConnectionProvider.getLoggedUser: ret=%s"%ret
		if len(ret)==1:
			tmp = self._xmlrpcToDbe(ret[0])
			if self.verbose: print "XmlrpcConnectionProvider.getLoggedUser: tmp=%s"%tmp
			return tmp
		return None
	def getFormSchema(self,language='python',aClassname=''):
		self.semaforo.acquire(True)
		myformschema, tmp = self._server.getFormSchema(language,aClassname)
		self.semaforo.release()
		if self.verbose:
			print "myformschema:",myformschema
			print "tmp:",tmp
		return myformschema
	def getDBSchema(self,language='python',aClassname=''):
		self.semaforo.acquire(True)
		myformschema, tmp = self._server.getDBSchema(language,aClassname)
		self.semaforo.release()
		if self.verbose:
			print "myformschema:",myformschema
			print "tmp:",tmp
		return myformschema
	def objectById(self,myid,ignore_deleted=True):
		self.semaforo.acquire(True)
		self.msg, ret = self._server.objectById(myid,ignore_deleted)
		self.semaforo.release()
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		if len(ret)==1:
			return self._xmlrpcToDbe(ret[0])
		return None
	def fullObjectById(self,myid,ignore_deleted=True):
		self.semaforo.acquire(True)
		ret=[]
		try:
			self.msg, ret = self._server.fullObjectById(myid,ignore_deleted)
		except Exception,e:
			print "XmlrpcConnectionProvider.fullObjectById: ECCEZIONE ############# Start."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "XmlrpcConnectionProvider.fullObjectById: ECCEZIONE ############# End."
		finally:
			self.semaforo.release()
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		if len(ret)==1:
			return self._xmlrpcToDbe(ret[0])
		return None
	def objectByName(self,myid,ignore_deleted=True):
		self.semaforo.acquire(True)
		self.msg, ret = self._server.objectByName(Binary(myid),ignore_deleted)
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.objectByName: msg=%s"%self.msg
			print "XmlrpcConnectionProvider.objectByName: ret=%s"%ret
		# 2012.05.07: start.
		return [self._xmlrpcToDbe(dbe) for dbe in ret]
		#if len(ret)==1:
		#	return self._xmlrpcToDbe(ret[0])
		#return None
		# 2012.05.07: end.
	def fullObjectByName(self,myid,ignore_deleted=True):
		self.semaforo.acquire(True)
		ret=[]
		try:
			self.msg, ret = self._server.fullObjectByName(Binary(myid),ignore_deleted)
		except Exception,e:
			print "XmlrpcConnectionProvider.fullObjectByName: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "XmlrpcConnectionProvider.fullObjectByName: ECCEZIONE ############# Fine."
			raise DBLayerException( "Communication troubles with the remote server: %s" % e )
		finally:
			self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.fullObjectByName: msg=%s"%self.msg
			print "XmlrpcConnectionProvider.fullObjectByName: ret=%s"%ret
		# 2012.05.07: start.
		return [self._xmlrpcToDbe(dbe) for dbe in ret]
		#if len(ret)==1:
		#	return self._xmlrpcToDbe(ret[0])
		#return None
		# 2012.05.07: end.
	def uploadFile(self,local_filename):
		# Remote destination directory
		self.semaforo.acquire(True)
		messaggio, debugMsg, ret = self._server.GetRandomUploadDestinationDirectoryXmlrpc()
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.uploadFile: messaggio = %s" % ( messaggio )
			print "XmlrpcConnectionProvider.uploadFile: debugMsg = %s" % ( debugMsg )
			print "XmlrpcConnectionProvider.uploadFile: ret = %s" % ( ret )
		dest_dir = ret['dest_dir']
		if self.verbose:
			print "XmlrpcConnectionProvider.uploadFile: dest_dir:",dest_dir
		# Short local filename
		filename_corto = local_filename[ len(os.path.dirname(local_filename)): ]
		if filename_corto.startswith('/'):
			filename_corto = filename_corto[1:]
		if self.verbose: print "XmlrpcConnectionProvider.uploadFile: filename_corto: ", filename_corto
		# Load file
		myfile = file( local_filename ).read()
		# Upload TODO split the file in chunks
		self.semaforo.acquire(True)
		messaggio, debugMsg, ret = self._server.UploadXmlrpc(1,1,filename_corto,Binary(myfile), dest_dir)
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.uploadFile: messaggio = %s" % ( messaggio )
			print "XmlrpcConnectionProvider.uploadFile: debugMsg = %s" % ( debugMsg )
			print "XmlrpcConnectionProvider.uploadFile: ret = %s" % ( ret )
		filename = ret['filename']
		dest_dir = ret['dest_dir']
		if self.verbose:
			print "XmlrpcConnectionProvider.uploadFile: filename: ", filename
			print "XmlrpcConnectionProvider.uploadFile: dest_dir: ", dest_dir
		return "%s/%s" % (dest_dir,filename)
		#raise DBLayerException("XmlrpcConnectionProvider.uploadFile: not implemented.")
	def downloadFile(self,remote_filename,local_path,view_thumbnail=False):
		uuid = remote_filename
		self.semaforo.acquire(True)
		messaggio, debugMsg, ret = self._server.DownloadXmlrpc(uuid,view_thumbnail)
		self.semaforo.release()
		if self.verbose:
			print "XmlrpcConnectionProvider.downloadFile: messaggio = %s" % ( messaggio )
			print "XmlrpcConnectionProvider.downloadFile: debugMsg = %s" % ( debugMsg )
			print "XmlrpcConnectionProvider.downloadFile: ret = %s" % ( ret )
		mime = "%s" % ret['mime']
		filesize = ret['filesize']
		filename = "%s%s%s" % (local_path,os.path.sep,ret['filename'])
		if self.verbose:
			print "XmlrpcConnectionProvider.downloadFile: mime = %s" % ( mime )
			print "XmlrpcConnectionProvider.downloadFile: filesize = %s" % ( filesize )
			print "XmlrpcConnectionProvider.downloadFile: filename = %s" % ( filename )
		file(filename,'wb').write( ret['contents'].data )
		return filename
