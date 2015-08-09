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

from rprj.dblayer import DBConnectionProvider,DBLayerException,isDateTime
import codecs,datetime,os,sys,traceback
from base64 import b64decode

import pymongo

# 1 mega = 1048576
UPLOAD_SPLIT_SIZE = 1048576


class MongoConnectionProvider(DBConnectionProvider):
	def __init__(self, host, db, user, pwd, verbose=0):
		"""host: here is the url of the server
		db: TODO is meaningless here?"""
		DBConnectionProvider.__init__(self, host, db, user, pwd, verbose)
		self.verbose = verbose
		self._server = None
		self._conn = None
		self._cache = {}
		self.dbmgr = None # Needed by _jsonToDbe
		self.user = None # Web should act as a proxy
	def customInit(self):
		"""???"""
		pass
	def getConnection(self):
		if self._conn is None:
			self._conn = pymongo.Connection(self._host)
			self._server = self._conn[self._db]
		return self
	def freeConnection(self, conn):
		if not self._conn is None:
			del self._conn
		self._server = None
	def getDBType(self):
		return "Mongo"
	def isProxy(self):
		# NOTE non è un proxy MA le query deve costruirle lui
		return True

	def _dbeToJson(self,dbe):
		ret = dbe.getValuesDictionary()
		if ret.has_key('id'):
			ret['_id']=ret['id']
			ret.pop('id')
		return ret
	def _jsonToDbe(self, tablename, dbedict):
		ret = self.dbmgr.getClazz(tablename)()
		chiavi = ret.getKeys().keys()
		for k in dbedict.keys():
			if k=='_id' and 'id' in chiavi:
				ret.setValue('id', dbedict[k])
				continue
			ret.setValue(k, dbedict[k])
		return ret
		#typename = xmldbe['_typename']
		#xmldbe.pop('_typename')
		#nomi = [ n for n in xmldbe.keys() ]
		#valori = [ n for n in xmldbe.values() ]
		#valori2=[]
		#for v in valori:
			##if isinstance(v, Binary): v = v.data
			#if isDateTime(v): #rra.dblayer.isDateTime(v):
				##"0000-00-00 00:00:00"
				#v2=None
				#try:
					#v2 = datetime.datetime(int(v[:4]),int(v[5:7]),int(v[8:10]),int(v[11:13]),int(v[14:16]),int(v[17:19]))
				#except Exception,e:
					#try:
						#v2 = datetime.datetime(int(v[:4]),int(v[5:7]),int(v[8:10]),int(v[11:13]),int(v[14:16]))
					#except Exception, e:
						#try:
							#v2 = datetime.time(int(v[11:13]),int(v[14:16]),int(v[17:19]))
						#except Exception,e:
							#v2 = datetime.time(int(v[11:13]),int(v[14:16]))
				#valori2.append( v2 )
			#elif (type(v)==str or type(v)==unicode) and v.startswith("base64:"):
				#valori2.append( b64decode( v[len("base64:"):] ) )
			#else:
				#valori2.append(v)
		##if isinstance(typename, Binary): typename = typename.data
		#myclazz = self.dbmgr.getClazzByTypeName(typename)
		#return myclazz( myclazz().getTableName(), names=nomi, values=valori2 )
	def insert(self, dbe):
		# Before Insert
		dbe._before_insert(self.dbmgr)
		dbe = self.dbmgr._before_insert(dbe)
		# Insert
		dbejson = self._dbeToJson(dbe)
		print "dbejson:",dbejson
		collectionName = self.dbmgr.buildTableName(dbe)
		print "collectionName:",collectionName
		coll = self._server[ collectionName ]
		try:
			dbejson = coll.insert( dbejson )
		except Exception, e:
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
		print "=> dbejson:",dbejson
		#raise DBLayerException("DA FINIRE")
		self.lastMessages = self._server.error()
		#dbe = self._jsonToDbe( tmparray[0] )
		# After Insert
		dbe = self.dbmgr._after_insert(dbe)
		dbe._after_insert(self.dbmgr)
		return dbe
	def update(self,dbe):
		# Before Update
		# 2012.04.04 dbe._before_update(self.dbmgr)
		# 2012.04.04 dbe = self.dbmgr._before_update(dbe)
		# Update
		dbexml = self._dbeToJson(dbe)
		#self.semaforo.acquire(True)
		tmp = self._server.update( dbexml )
		#self.semaforo.release()
		tmparray = tmp[1]
		self.lastMessages = b64decode(tmp[0])
		if len(tmparray)>0:
			dbe = self._jsonToDbe( tmparray[0] )
			# After Update
			# 2012.04.04 dbe = self.dbmgr._after_update(dbe)
			# 2012.04.04 dbe._after_update(self.dbmgr)
		else:
			dbe = None
		return dbe
	def select(self,tablename,searchString):
		if self.verbose: print "MongoConnectionProvider.select: tablename=%s" % (tablename)
		if self.verbose: print "MongoConnectionProvider.select: searchString=%s" % (searchString)
		if self.verbose: print "MongoConnectionProvider.select: start search=%s" % (datetime.datetime.now())
		try:
			#self.semaforo.acquire(True)
			tmp = self._server.select( tablename, searchString )
			#self.semaforo.release()
			res = tmp[1]
			self.lastMessages = b64decode(tmp[0])
		except DBLayerException, e:
			raise DBLayerException( "Problemi di comunicazione col server: %s" % e )
		if self.verbose: print "MongoConnectionProvider.select: start xml->dbe=%s" % (datetime.datetime.now())
		ret = []
		for r in res:
			tmpdbe = self._jsonToDbe(r)
			ret.append( tmpdbe )
		if self.verbose: print "MongoConnectionProvider.select: end time = %s" % (datetime.datetime.now())
		return ret
	def delete(self,dbe):
		# Delete
		dbexml = self._dbeToJson(dbe)
		#self.semaforo.acquire(True)
		tmp = self._server.delete( dbexml )
		#self.semaforo.release()
		tmparray = tmp[1]
		self.lastMessages = b64decode(tmp[0])
		if self.verbose: print "MongoConnectionProvider.delete: self.lastMessages = %s" % (self.lastMessages)
		dbe = self._jsonToDbe( tmparray[0] )
		return dbe
	def search(self, dbe, uselike=1, orderby=None, ignore_deleted=True, full_object=True):
		casesensitive = 0
		if self.verbose: print "MongoConnectionProvider.search: start time = %s" % (datetime.datetime.now())
		tablename = dbe.getTableName()
		# Search
		dbedict = dbe.getValuesDictionary()
		if self.verbose: print "MongoConnectionProvider.search: dbedict = %s" % (dbedict)
		if self.verbose: print "MongoConnectionProvider.search: uselike = %s" % (uselike)
		#if self.verbose: print "MongoConnectionProvider.search: casesensitive = %s" % (casesensitive)
		if self.verbose: print "MongoConnectionProvider.search: orderby = %s" % (orderby)
		lista = []
		try:
			if orderby is None: orderby=''
			#self.semaforo.acquire(True)
			collectionName = self.dbmgr.buildTableName(dbe)
			#if not collectionName in self._server.collection_names():
			#	raise DBLayerException("Collection '%s' does not exists." % (collectionName))
			coll = self._server[ collectionName ]
			if self.verbose: print "MongoConnectionProvider.search: coll=%s (%s)\n  %s" % (coll,  coll.count(),  dir(coll))
			lista = coll.find( dbedict )
			#self.lastMessages = msg
		except Exception, e:
			print "MongoConnectionProvider.search: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "MongoConnectionProvider.search: ECCEZIONE ############# Fine."
			raise DBLayerException( "Problemi di comunicazione col server: %s" % e )
		ret = []
		if self.verbose: print "MongoConnectionProvider.search: lista=%s (%s)" % (lista, lista.count())
		for r in lista:
			if self.verbose: print "  %s" % r
			tmpdbe = self._jsonToDbe(tablename, r)
			if self.verbose: print "  %s" % tmpdbe
			ret.append( tmpdbe )
		if self.verbose: print "MongoConnectionProvider.search: ret = %s" % ( len(ret) )
		if self.verbose: print "MongoConnectionProvider.search: end time = %s" % (datetime.datetime.now())
		return ret
	def copy(self, dbe):
		raise DBLayerException("copy: not yet implemented")

# ################### Remote Server ###################
	def ping(self):
		#self.semaforo.acquire(True)
		serverinfo = self._conn.server_info()
		#self.semaforo.release()
		if self.verbose:
			print "MongoConnectionProvider.ping: serverinfo"
			for k in serverinfo.keys():
				print "%s: %s" % (k,serverinfo[k])
		return "pong"
	def initDB(self):
		"""Initialize DB"""
		if self.verbose: print "MongoConnectionProvider.initDB: start."
		mytypes = self.dbmgr.dbeFactory.getRegisteredTypes()
		for tablename in mytypes.keys():
			if tablename=='default':
				continue
			mydbe = mytypes[tablename]()
			collectionName = self.dbmgr.buildTableName(mydbe)
			if self.verbose: print "MongoConnectionProvider.initDB: collectionName = %s" % ( collectionName )
			# 1. Table definition
			try:
				self._server.create_collection(collectionName)
				dbErrors = self._server.error()
				if not dbErrors is None:
					if self.verbose: print "MongoConnectionProvider.initDB: dbErrors = %s" % dbErrors
					raise DBLayerException(dbErrors)
			except Exception,e:
				if self.verbose:
					print "MongoConnectionProvider.initDB: Exception = %s" % (e)
					print "".join(traceback.format_tb(sys.exc_info()[2]))
		# 2. Default entries
		# 2012.06.04: start.
		for tablename in mytypes.keys():
			if tablename=='default':
				continue
			mydbe = mytypes[tablename]()
			# 2012.06.04: end.
			defaultEntries = mydbe.getDefaultEntries()
			if len(defaultEntries)==0: continue
			for d in defaultEntries:
				if self.verbose: print "MongoConnectionProvider.initDB: d = %s" % (d)
				newdbe = mytypes[tablename]( attrs=d )
				self.insert(newdbe)
		if self.verbose: print "MongoConnectionProvider.initDB: end."
	def login(self,user,pwd):
		if self.verbose: print "MongoConnectionProvider.login: start."
		if pwd is None or len(pwd)==0 or user is None or len(user)==0:
			raise DBLayerException("Missing username or password")
		cerca = self.dbmgr.getClazzByTypeName('DBEUser')(attrs={'login':user,'pwd':pwd})
		ret = []
		try:
			collectionName = self.dbmgr.buildTableName(cerca)
			if not collectionName in self._server.collection_names():
				raise DBLayerException("Collection '%s' does not exists." % (collectionName))
			ret = self.search(cerca,uselike=False)
		except Exception,e:
			myerr = "%s" % e
			print myerr
			print "".join(traceback.format_tb(sys.exc_info()[2]))
			#raise e
			try:
				self.user = cerca # Sporco trucco: altrimenti i controlli isLoggedIn falliscono!!!
				self.initDB()
				newuser = self.dbmgr.getClazzByTypeName('DBEUser')(attrs={'login':user,'pwd':pwd})
				newuser = self.insert(newuser)
				self.user = newuser
				searchGroup = self.dbmgr.getClazzByTypeName('DBEGroup')(attrs={'name':user})
				newgroup = self.search(searchGroup,uselike=False)[0]
				if self.verbose: print "MongoConnectionProvider.login: newuser=%s" % ( newuser )
				newfolder = self.dbmgr.getClazzByTypeName('DBEFolder')(attrs={\
					'owner':newuser.getValue('id'),'group_id':newgroup.getValue('id'),\
					'creator':newuser.getValue('id'),'last_modify':newuser.getValue('id'),\
					'name':user})
				newfolder = self.insert(newfolder)
				if self.verbose: print "MongoConnectionProvider.login: newfolder=%s" % ( newfolder )
				ret = self.search(cerca,uselike=False)
			except Exception,e1:
				if self.verbose: print "MongoConnectionProvider.login: ECCEZIONE=%s" % ( e )
				if self.verbose: print "".join(traceback.format_tb(sys.exc_info()[2]))
		if len(ret)==1:
			self.user = ret[0]
		else:
			self.user = None
		if self.verbose: print "MongoConnectionProvider.login: self.user = %s" % ( self.user )
		if self.verbose: print "MongoConnectionProvider.login: end."
		return self.user
	def getLoggedUser(self):
		return self.user
	def getFormSchema(self,language='python',aClassname=''):
		#self.semaforo.acquire(True)
		myformschema, tmp = self._server.getFormSchema(language,aClassname)
		#self.semaforo.release()
		if self.verbose:
			print "myformschema:",myformschema
			print "tmp:",tmp
		return myformschema
	def getDBSchema(self,language='python',aClassname=''):
		#self.semaforo.acquire(True)
		myformschema, tmp = self._server.getDBSchema(language,aClassname)
		#self.semaforo.release()
		if self.verbose:
			print "myformschema:",myformschema
			print "tmp:",tmp
		return myformschema
	def objectById(self,myid,ignore_deleted=True):
		tipi = self.dbmgr.getRegisteredTypes()
		DBEObject = self.dbmgr.getClazzByTypeName("DBEObject")
		#DBAssociation = self.dbmgr.getClazzByTypeName("DBAssociation")
		q = []
		for tablename, clazz in tipi.items():
			mydbe = clazz()
			if not isinstance(mydbe, DBEObject):# or isinstance(mydbe, DBAssociation): # or mydbe.getTypeName=="DBEObject":
				continue
			tmp_q = "select '%s' as classname,id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description from %s where id='%s'" % (mydbe.getTypeName(), self.dbmgr.buildTableName(mydbe), DBEObject.hex2uuid(myid) )
			if ignore_deleted:
				tmp_q += " and deleted_date='0000-00-00 00:00:00'"
			q.append( tmp_q )
		searchString = " union ".join( q )
		if self.verbose: print "ObjectMgr.objectById: searchString=%s" % ( searchString )
		raise DBLayerException("DA FINIRE")
		lista = self.select("objects", searchString)
		if len(lista)==1:
			return lista[0]
		if self.verbose: print "ObjectMgr.objectById: lista=%s" % ( len(lista) )
		return None

	def fullObjectById(self,myid,ignore_deleted=True):
		#self.semaforo.acquire(True)
		ret=[]
		try:
			self.msg, ret = self._server.fullObjectById(myid,ignore_deleted)
			self.msg = b64decode(self.msg)
		except Exception,e:
			print "MongoConnectionProvider.fullObjectById: ECCEZIONE ############# Start."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "MongoConnectionProvider.fullObjectById: ECCEZIONE ############# End."
		finally:
			#self.semaforo.release()
			pass
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		if len(ret)==1:
			return self._jsonToDbe(ret[0])
		return None
	def objectByName(self,myid,ignore_deleted=True):
		#self.semaforo.acquire(True)
		ret=[]
		tmp = self._server.objectByName(myid,ignore_deleted)
		if len(tmp)>0:
			self.msg = b64decode(tmp[0])
		if len(tmp)>1:
			ret=tmp[1:][0]
		#self.semaforo.release()
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		# 2012.05.07: start.
		return [self._jsonToDbe(x) for x in ret]
		#if len(ret)==1:
		#	return self._jsonToDbe(ret[0])
		#return None
		# 2012.05.07: end.
	def fullObjectByName(self,myid,ignore_deleted=True):
		#self.semaforo.acquire(True)
		ret=[]
		try:
			tmp = self._server.objectByName(myid,ignore_deleted)
			if len(tmp)>0:
				self.msg = b64decode(tmp[0])
			if len(tmp)>1:
				ret=tmp[1:][0]
			#self.msg, ret = self._server.fullObjectByName(myid,ignore_deleted)
			#self.msg = b64decode(self.msg)
		except Exception,e:
			print "MongoConnectionProvider.fullObjectByName: ECCEZIONE ############# Inizio."
			print "ECCEZIONE: %s" % ( e )
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			print "MongoConnectionProvider.fullObjectByName: ECCEZIONE ############# Fine."
			raise DBLayerException( "Communication troubles with the remote server: %s" % e )
		finally:
			#self.semaforo.release()
			pass
		if self.verbose:
			print "msg:",self.msg
			print "ret:",ret
		# 2012.05.07: start.
		return [self._jsonToDbe(x) for x in ret]
		#if len(ret)==1:
		#	return self._jsonToDbe(ret[0])
		#return None
		# 2012.05.07: end.
	def uploadFile(self,local_filename):
		# Remote destination directory
		#self.semaforo.acquire(True)
		messaggio, ret = self._server.GetRandomUploadDestinationDirectory()
		messaggio = b64decode(messaggio)
		#self.semaforo.release()
		if self.verbose:
			print "MongoConnectionProvider.uploadFile: messaggio = %s" % ( messaggio )
			print "MongoConnectionProvider.uploadFile: ret = %s" % ( ret )
		dest_dir = ret['dest_dir']
		if self.verbose:
			print "MongoConnectionProvider.uploadFile: dest_dir:",dest_dir
		# Short local filename
		filename_corto = local_filename[ len(os.path.dirname(local_filename)): ]
		if filename_corto.startswith('/'):
			filename_corto = filename_corto[1:]
		if self.verbose: print "MongoConnectionProvider.uploadFile: filename_corto: ", filename_corto
		# Load file
		myfile = file( local_filename ).read()
		# Upload TODO split the file in chunks
		#self.semaforo.acquire(True)
		messaggio, ret = self._server.Upload(1,1,filename_corto,u"%s" % Binary(myfile), dest_dir)
		messaggio = b64decode(messaggio)
		#self.semaforo.release()
		if self.verbose:
			print "MongoConnectionProvider.uploadFile: messaggio = %s" % ( messaggio )
			print "MongoConnectionProvider.uploadFile: ret = %s" % ( ret )
		filename = ret['filename']
		dest_dir = ret['dest_dir']
		if self.verbose:
			print "MongoConnectionProvider.uploadFile: filename: ", filename
			print "MongoConnectionProvider.uploadFile: dest_dir: ", dest_dir
		return "%s/%s" % (dest_dir,filename)
		#raise DBLayerException("MongoConnectionProvider.uploadFile: not implemented.")
	def downloadFile(self,remote_filename,local_path,view_thumbnail=False):
		uuid = remote_filename
		#self.semaforo.acquire(True)
		messaggio, ret = self._server.Download(uuid,view_thumbnail)
		messaggio = b64decode(messaggio)
		#self.semaforo.release()
		if self.verbose:
			print "MongoConnectionProvider.downloadFile: messaggio = %s" % ( messaggio )
			tmp = {}
			for k in ret.keys():
				if k=='contents': continue
				tmp[k]=ret[k]
			print "MongoConnectionProvider.downloadFile: ret (senza contents) = %s" % ( tmp )
		mime = "%s" % ret['mime']
		filesize = ret['filesize']
		filename = "%s%s%s" % (local_path,os.path.sep,ret['filename'])
		if self.verbose:
			print "MongoConnectionProvider.downloadFile: mime = %s" % ( mime )
			print "MongoConnectionProvider.downloadFile: filesize = %s" % ( filesize )
			print "MongoConnectionProvider.downloadFile: filename = %s" % ( filename )
		file(filename,'wb').write( b64decode(ret['contents']) )
		return filename


