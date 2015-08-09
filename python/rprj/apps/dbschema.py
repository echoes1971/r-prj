# -*- coding: utf-8 -*-
from collections import OrderedDict
import datetime, os.path
import sys, traceback
from rprj.dblayer import ForeignKey, DBEntity, DBAssociation, DBEFactory, DBMgr


# Predefined Groups
GROUP_ADMIN = '-2'
GROUP_USERS = '-3'
GROUP_GUESTS = '-4'
GROUP_PROJECT = '-5'
GROUP_WEBMASTER = '-6'

dbschema = OrderedDict()
#dbschema = {}

# TODO translate all those PHP functions: start.
# TODO translate all those PHP functions: end.

class DBEDBVersion(DBEntity):
    __columns = {}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEDBVersion.__columns.keys())==0:
            parentcols = self.getColumns()
            for k in parentcols.keys():
                DBEDBVersion.__columns[k] = parentcols[k]
            DBEDBVersion.__columns['version'] = ["int","not null"]
        self._columns=DBEDBVersion.__columns
    def getTableName(self):
        return "dbversion"
    def getKeys(self):
        return { 'version':'int' }
    def getFK(self):
        return [  ]
    def getOrderBy(self):
        return [ "version" ]
    def version(self):
        tmp = self.getValue('version')
        if tmp is None:
            return 0
        return tmp
dbschema['dbversion'] = DBEDBVersion

class DBEUserGroup(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEUserGroup.__columns.keys())==0:
            parentcols = self.getColumns()
            for k in parentcols.keys():
                DBEUserGroup.__columns[k] = parentcols[k]
            DBEUserGroup.__columns['user_id'] = ["uuid","not null"]
            DBEUserGroup.__columns['group_id'] = ["uuid","not null"]
        self._columns = DBEUserGroup.__columns
    def getTableName(self):
        return "users_groups"
    def getKeys(self):
        return { 'user_id':'uuid', 'group_id':'uuid' }
    def getFK(self):
        return [ ForeignKey("user_id","users","id"), ForeignKey("group_id","groups","id") ]
    def getOrderBy(self):
        return [ "user_id", "group_id" ]
    def getDefaultEntries(self):
        return [\
            {'user_id':'-1','group_id':'-2',},\
            {'user_id':'-1','group_id':'-5',},\
            {'user_id':'-1','group_id':'-6',},\
        ]
dbschema['users_groups'] = DBEUserGroup

class DBEGroup(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEGroup.__columns.keys())==0:
            parentcols = self.getColumns()
            for k in parentcols.keys():
                DBEGroup.__columns[k] = parentcols[k]
            DBEGroup.__columns['id'] = ["uuid","not null"]
            DBEGroup.__columns['name'] = ["varchar(255)","not null"]
            DBEGroup.__columns['description'] = ["text","default null"]
        self._columns = DBEGroup.__columns
    def getTableName(self):
        return "groups"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [  ]
    def getOrderBy(self):
        return [ "name" ]
    def getDefaultEntries(self):
        return [\
            {'id':'-2','name':'Admin','description':'System admins'},\
            {'id':'-3','name':'Users','description':'System users'},\
            {'id':'-4','name':'Guests','description':'System guests (read only)'},\
            {'id':'-5','name':'Project','description':'R-Project user'},\
            {'id':'-6','name':'Webmaster','description':'Web content creators'},\
        ]
    def _before_insert(self,dbmgr=None):
        if self.getValue('id') is None:
            myid = dbmgr.getNextUuid(self)
            self.setValue( 'id', myid )
    def _after_insert(self,dbmgr=None):
        if dbmgr.getDBEUser() is None:
            return
        dbe = DBEUserGroup()
        dbe.setValue('group_id',self.getValue('id'))
        dbe.setValue('user_id', dbmgr.getDBEUser().getValue('id'))
        dbmgr.insert(dbe)
        dbmgr.addGroup( self.getValue('id') )
    def _after_delete(self,dbmgr=None):
        cerca = DBEUserGroup()
        cerca.setValue('group_id', self.getValue('id'))
        lista = dbmgr.search(cerca, uselike=0)
        for ass in lista:
            dbmgr.delete(ass)
dbschema['groups'] = DBEGroup

class DBEUser(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEUser.__columns.keys())==0:
            parentcols = self.getColumns()
            for k in parentcols.keys():
                DBEUser.__columns[k] = parentcols[k]
            DBEUser.__columns['id'] = ["uuid","not null"]
            DBEUser.__columns['login'] = ["varchar(255)","not null"]
            DBEUser.__columns['pwd'] = ["varchar(255)","not null"]
            DBEUser.__columns['pwd_salt'] = ["varchar(4)","default ''"]
            DBEUser.__columns['fullname'] = ["text","default null"]
            DBEUser.__columns['group_id'] = ["uuid","not null"]
        self._columns=DBEUser.__columns
    def getValue( self, chiave ):
        if chiave=='name':
            return DBEntity.getValue( self, 'login' )
        return DBEntity.getValue( self, chiave )
    def setValue( self, chiave, valore ):
        if chiave=='name':
            return DBEntity.setValue( self, 'login', valore )
        return DBEntity.setValue( self, chiave, valore )
    def getTableName(self):
        return "users"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("group_id","groups","id") ]
    def getOrderBy(self):
        return [ "fullname" ]
    def getDefaultEntries(self):
        return [\
            {'id':'-1', 'login':'adm','pwd':'adm','pwd_salt':'','fullname':'Administrator','group_id':'-2'},\
        ]
    def checkNewPassword(self):
        ret = True
        return ret
    def _before_insert(self,dbmgr=None):
        if self.getValue('id') is None:
            myid = dbmgr.getNextUuid(self)
            self.setValue('id',myid)
        self.checkNewPassword()
        if self.checkNewPassword():
            self._createGroup(dbmgr)
    def _after_insert(self,dbmgr=None):
        self._checkGroupAssociation(dbmgr)
    def _after_update(self,dbmgr=None):
        self._checkGroupAssociation(dbmgr)
    def _after_delete(self,dbmgr=None):
        cerca = DBEUserGroup()
        cerca.setValue('user_id',self.getValue('id'))
        lista = dbmgr.search(cerca, uselike = False)
        for ass in lista:
            dbmgr.delete(ass)
        self._deleteGroup(dbmgr)
    def _createGroup(self,dbmgr):
        if not self.getValue('group_id') is None: return
        dbe = DBEGroup()
        dbe.setValue('name', self.getValue('login'))
        dbe.setValue('description', "Private group for %s-%s" % (self.getValue('id'),self.getValue('login')) )
        dbe = dbmgr.insert(dbe)
        self.setValue('group_id', dbe.getValue('id'))
    def _deleteGroup(self,dbmgr):
        dbe = DBEGroup()
        dbe.setValue('id', self.getValue('group_id'))
        dbe = dbmgr.delete(dbe)
    def _checkGroupAssociation(self,dbmgr):
        ug = DBEUserGroup()
        ug.setValue('user_id',self.getValue('id'))
        ug.setValue('group_id',self.getValue('group_id'))
        exists = dbmgr.exists(ug)
        if not exists:
            dbmgr.insert(ug)
    def isRoot(self):
        return self.getValue('id')==-1 or self.getValue('id')=='-1'
dbschema['users'] = DBEUser

class DBELog(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBELog.__columns.keys())==0:
            parentcols = self.getColumns()
            for k in parentcols.keys():
                DBELog.__columns[k] = parentcols[k]
            DBELog.__columns['ip'] = ["varchar(16)","not null"]
            DBELog.__columns['data'] = ["date","not null default '0000-00-00'"]
            DBELog.__columns['ora'] = ["time","not null default '00:00:00'"]
            DBELog.__columns['count'] = ["int","not null default 0"]
            DBELog.__columns['url'] = ["varchar(255)","default null"]
            DBELog.__columns['note'] = ["varchar(255)","not null default ''"]
            DBELog.__columns['note2'] = ["text","not null"]
        self._columns=DBELog.__columns
    def getTableName(self):
        return "log"
    def getKeys(self):
        return { 'ip':'varchar(16)', 'data':'date' }
    def getFK(self):
        return [  ]
    def getOrderBy(self):
        return [ "data desc", "ora desc" ]
    def _before_insert(self,dbmgr=None):
        pass
dbschema['log'] = DBELog

class DBEObject(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEObject.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEObject.__columns[k]=parentcols[k]
            DBEObject.__columns['id']=["uuid","not null"]
            DBEObject.__columns['owner']=["uuid","not null"]
            DBEObject.__columns['group_id']=["uuid","not null"]
            DBEObject.__columns['permissions']=["char(9)","not null default 'rwx------'"]
            DBEObject.__columns['creator']=["uuid","not null"]
            DBEObject.__columns['creation_date']=["datetime","default null"]
            DBEObject.__columns['last_modify']=["uuid","not null"]
            DBEObject.__columns['last_modify_date']=["datetime","default null"]
            DBEObject.__columns['deleted_by']=["uuid","default null"]
            DBEObject.__columns['deleted_date']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBEObject.__columns['father_id']=["uuid","default null"]
            DBEObject.__columns['name']=["varchar(255)","not null"]
            DBEObject.__columns['description']=["text","default null"]
        self._columns=DBEObject.__columns
    def getTableName(self):
        return "objects"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","objects","id") ]
    def getOrderBy(self):
        return [ "name" ]
    # ====== Object Methods ======
    def getOwnerId(self):
        return self.getValue('owner')
    def getGroupId(self):
        return self.getValue('group_id')
    def isDeleted(self):
        myvalue = self.getValue('deleted_date')
        if not myvalue is None and not type(myvalue)==str:
            myvalue = self._formatDateTime(myvalue)
        if myvalue>'0000-00-00 00:00:00':
            return True
        return False
    def canRead(self,kind=''):
        if not self.getValue('permissions')>'':
            return True
        elif kind=='U': # User
            tmp = self.getValue('permissions')[0+0:0+0+1]
            return tmp == 'r'
        elif kind=='G': # Group
            tmp = self.getValue('permissions')[0+3:0+3+1]
            return tmp == 'r'
        else: # All
            tmp = self.getValue('permissions')[0+6:0+6+1]
            return tmp == 'r'
    def canWrite(self,kind=''):
        if not self.getValue('permissions')>'':
            return True
        elif kind=='U': # User
            tmp = self.getValue('permissions')[1+0:1+0+1]
            return tmp == 'w'
        elif kind=='G': # Group
            tmp = self.getValue('permissions')[1+3:1+3+1]
            return tmp == 'w'
        else: # All
            tmp = self.getValue('permissions')[1+6:1+6+1]
            return tmp == 'w'
    def canExecute(self,kind=''):
        if not self.getValue('permissions')>'':
            return True
        elif kind=='U': # User
            tmp = self.getValue('permissions')[2+0:2+0+1]
            return tmp == 'x'
        elif kind=='G': # Group
            tmp = self.getValue('permissions')[2+3:2+3+1]
            return tmp == 'x'
        else: # All
            tmp = self.getValue('permissions')[2+6:2+6+1]
            return tmp == 'x'
    def setDefaultValues(self,dbmgr):
        myuser = dbmgr.getDBEUser()
        if not myuser is None:
            if not self.getValue('owner')>'':
                self.setValue('owner',myuser.getValue('id'))
            if not self.getValue('group_id')>'':
                self.setValue('group_id',myuser.getValue('group_id'))
            self.setValue('creator',myuser.getValue('id'))
            self.setValue('last_modify',myuser.getValue('id'))
        self.setValue('creation_date',self._getTodayString())
        self.setValue('last_modify_date',self._getTodayString())
        if dbmgr._connProvider.getDBType()=="POSTGRESQL":
            self.setValue('deleted_date',None)
        else:
            self.setValue('deleted_date','0000-00-00 00:00:00')
        
        if not self.getValue('permissions')>'':
            self.setValue('permissions','rwx------')
        
        father_id = self.getValue('father_id')
        if father_id is None or father_id==0 or len("%s"%father_id)==0:
            self.setValue('father_id','')
            
            if self.getValue('fk_obj_id')>'':
                fkobj = dbmgr.objectById( self.getValue('fk_obj_id') )
                self.setValue('group_id',fkobj.getValue('group_id'))
                self.setValue('permissions',fkobj.getValue('permissions'))
                self.setValue('father_id',self.getValue('fk_obj_id'))
        else:
            father = dbmgr.objectById( father_id )
            if not father is None:
                self.setValue('owner',father.getValue('owner'))
                self.setValue('group_id',father.getValue('group_id'))
                self.setValue('permissions',father.getValue('permissions'))
    def _before_insert(self, dbmgr):
        if self.getValue('id') is None:
            myid = dbmgr.getNextUuid(self)
            self.setValue( 'id', myid )
        self.setDefaultValues(dbmgr)
    def _before_update(self, dbmgr):
        myuser = dbmgr.getDBEUser()
        if not myuser is None:
            self.setValue('last_modify',myuser.getValue('id'))
        if dbmgr._connProvider.getDBType()=="POSTGRESQL" and self.getValue('deleted_date')=='0000-00-00 00:00:00':
            self.setValue('deleted_date',None)
        self.setValue('last_modify_date', self._getTodayString())
    def _before_delete(self, dbmgr):
        if self.isDeleted():
            return
        myuser = dbmgr.getDBEUser()
        if not myuser is None:
            self.setValue('deleted_by',myuser.getValue('id'))
        self.setValue('deleted_date', self._getTodayString())
dbschema['objects'] = DBEObject

class ObjectMgr(DBMgr):
    def __init__(self, connProvider, verbose=0, schema = ''):
        DBMgr.__init__(self, connProvider, verbose, schema)
    def canRead(self,obj):
        ret = False
        myuser = self.getDBEUser()
        if obj.canRead():
            ret = True
        elif obj.canRead('G') and self.hasGroup( obj.getGroupId() ):
            ret = True
        elif obj.canRead('U') and myuser is not None and myuser.getValue('id')==obj.getOwnerId():
            ret = True
        return ret
    def canWrite(self,obj):
        ret = False
        myuser = self.getDBEUser()
        if obj.canWrite():
            ret = True
        elif obj.canWrite('G') and self.hasGroup( obj.getGroupId() ):
            ret = True
        elif obj.canWrite('U') and myuser is not None and myuser.getValue('id')==obj.getOwnerId():
            ret = True
        return ret
    def canExecute(self,obj):
        ret = False
        myuser = self.getDBEUser()
        if obj.canExecute():
            ret = True
        elif obj.canExecute('G') and self.hasGroup( obj.getGroupId() ):
            ret = True
        elif obj.canExecute('U') and myuser is not None and myuser.getValue('id')==obj.getOwnerId():
            ret = True
        return ret
    def select(self,tablename,searchString):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self.conn.select(tablename,searchString)
        if self._verbose: print "ObjectMgr.select: start."
        tmp = DBMgr.select(self,tablename,searchString)
        myuser = self.getDBEUser()
        if not myuser is None and myuser.isRoot():
            return tmp
        ret = []
        for obj in tmp:
            if isinstance(obj, DBEObject):
                if not myuser is None and obj.getValue('creator')==myuser.getValue('id'):
                    ret.append(obj)
                    continue
                if obj.isDeleted():
                    continue
                if self.canRead(obj):
                    ret.append(obj)
                continue
            elif isinstance(obj, DBEUser):
                if myuser is None or self.hasGroup(GROUP_ADMIN) or obj.getValue('id')==myuser.getValue('id'):
                    ret.append(obj)
                continue
            elif isinstance(obj, DBEGroup):
                if myuser is None or self.hasGroup(GROUP_ADMIN) or self.hasGroup( obj.getValue('id') ):
                    ret.append(obj)
                continue
            ret.append( obj )
        if self._verbose:
            print "ObjectMgr.select: ret=%s" % ( len( ret ) )
            print "ObjectMgr.select: end."
        return ret
    def insert(self, dbe, cleanKeysIfError=True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self.conn.insert(dbe)
        have_permission = True
        if isinstance(dbe, DBEObject):
            have_permission = False
            myuser = self.getDBEUser()
            if not myuser is None and dbe.getValue('creator')==myuser.getValue('id'):
                have_permission = True
            elif self.canWrite( dbe ):
                have_permission = True
        if have_permission:
            return DBMgr.insert(self, dbe, cleanKeysIfError)
        return None
    def update(self, dbe):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self.conn.update(dbe)
        have_permission = True
        if isinstance(dbe, DBEObject):
            have_permission = False
            myuser = self.getDBEUser()
            if not myuser is None and dbe.getValue('creator')==myuser.getValue('id'):
                have_permission = True
            elif self.canWrite( dbe ):
                have_permission = True
        if have_permission:
            return DBMgr.update(self, dbe)
        return None
    def delete(self, dbe):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self.conn.delete(dbe)
        have_permission = True
        if isinstance(dbe, DBEObject):
            have_permission = False
            myuser = self.getDBEUser()
            if not myuser is None and dbe.getValue('creator')==myuser.getValue('id'):
                have_permission = True
            elif self.canWrite( dbe ):
                have_permission = True
        if not have_permission:
            return dbe
        if not isinstance(dbe, DBEObject) or dbe.isDeleted():
            return DBMgr.delete(self, dbe)
        # Before Delete
        dbe._before_delete(self)
        dbe = self._before_delete(dbe)
        ## Delete
        query = self._buildUpdateString( dbe )
        if self._verbose: print "ObjectMgr.delete: query=%s" % query
        try:
            cursor = self.getConnection().cursor()
            cursor.execute( query )
            self.getConnection().commit()
            # After Delete
            dbe = self._after_delete(dbe)
            dbe._after_delete(self)
        except Exception, e:
            if self._verbose:
                print "ObjectMgr.delete: ECCEZIONE=%s (%s)" % (e,query)
                print "\n".join(traceback.format_tb(sys.exc_info()[2]))
            try:
                ris = self.getConnection().query( query )
                self.getConnection().commit()
                # After Delete
                dbe = self._after_delete(dbe)
                dbe._after_delete(self)
            except Exception, e:
                dbe = None
                raise DBLayerException("ObjectMgr.delete: unable to execute\n\t%s.Error cause: %s" % ( query, e ))
        return dbe
    def db_version(self):
        cerca = DBEDBVersion()
        ris = self.search(cerca,1)
        if len(ris)==1:
           return ris[0].version()
        else:
           return 0
    def _buildSelectString(self, dbe, uselike=1,case_sensitive=True):
        if not isinstance(dbe, DBEObject) or dbe.getTypeName()!="DBEObject":
            return DBMgr._buildSelectString(self, dbe, uselike, case_sensitive)
        tipi = self.getRegisteredTypes()
        q = []
        for tablename, clazz in tipi.items():
            mydbe = clazz()
            if not isinstance(mydbe, DBEObject) or isinstance(mydbe, DBAssociation)\
                    or mydbe.getTypeName()=="DBEObject":
                    #or isinstance(mydbe,DBETodoTipo) or mydbe.getTypeName()=="DBEObject":
                continue
            mydbe.setValuesDictionary(dbe.getValuesDictionary())
            q.append(
                DBMgr._buildSelectString(self, mydbe, uselike, case_sensitive).replace(
                    "select * ",
                    "select '%s' as classname,id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description " % (mydbe.getTypeName())
                )
            )
        searchString = " union ".join( q )
        if self._verbose: print "ObjectMgr._buildSelectString: searchString=%s" % ( searchString )
        return searchString
    def search(self, dbe, uselike=1, orderby=None, ignore_deleted=True, full_object=True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self.conn.search(dbe, uselike, orderby, ignore_deleted, full_object)
        if self._verbose: print "ObjectMgr.search: start."
        if self._verbose: print "ObjectMgr.search: dbe.getTypeName()=%s." % (dbe.getTypeName())
        if dbe.getTypeName()!="DBEObject":
            if isinstance(dbe, DBEObject) and ignore_deleted:
                if self._connProvider.getDBType()=="POSTGRESQL":
                    dbe.setValue('deleted_date','null')
                else:
                    dbe.setValue('deleted_date','0000-00-00 00:00:00')
            ret = DBMgr.search(self, dbe, uselike, orderby)
            if self._verbose: print "ObjectMgr.search: quick end; ret = %s" % ( len(ret) )
            return ret
        if ignore_deleted:
            if self._connProvider.getDBType()=="POSTGRESQL":
                dbe.setValue('deleted_date','null')
            else:
                dbe.setValue('deleted_date','0000-00-00 00:00:00')
        ret = []
        tmp = DBMgr.search(self, dbe, uselike, orderby)
        if self._verbose:
            print "ObjectMgr.search: tmp=%s" % ( len(tmp) )
            for _t in tmp:
                print "ObjectMgr.search: -> %s" % ( _t )
        for _obj in tmp:
            cerca = self.getClazzByTypeName("%s"%_obj.getValue('classname'))()
            cerca.setValue('id',_obj.getValue('id'))
            lista = DBMgr.search(self,cerca,uselike=0)
            if self._verbose: print "ObjectMgr.search: lista=%s" % ( len(lista) )
            if len(lista)!=1:
                continue
            ret.append( lista[0] )
        if self._verbose: print "ObjectMgr.search: end."
        return ret
    def objectById(self,myid,ignore_deleted=True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.objectById(myid,ignore_deleted)
        tipi = self.getRegisteredTypes()
        q = []
        for tablename, clazz in tipi.items():
            mydbe = clazz()
            if not isinstance(mydbe, DBEObject) or isinstance(mydbe, DBAssociation): # or mydbe.getTypeName=="DBEObject":
                continue
            tmp_q = "select '%s' as classname,id,owner,group_id,permissions,creator,creation_date,last_modify,last_modify_date,father_id,name,description from %s where id='%s'" % (mydbe.getTypeName(), self.buildTableName(mydbe), DBEntity.hex2uuid(myid) )
            if ignore_deleted:
                if self._connProvider.getDBType()=="POSTGRESQL":
                    tmp_q += " and deleted_date is null"
                else:
                    tmp_q += " and deleted_date='0000-00-00 00:00:00'"
            q.append( tmp_q )
        searchString = " union ".join( q )
        if self._verbose: print "ObjectMgr.objectById: searchString=%s" % ( searchString )
        lista = self.select("objects", searchString)
        if len(lista)==1:
            return lista[0]
        if self._verbose: print "ObjectMgr.objectById: lista=%s" % ( len(lista) )
        return None
    def fullObjectById(self, myid,ignore_deleted=True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.fullObjectById(myid,ignore_deleted)
        myobj = self.objectById(myid,ignore_deleted)
        if myobj is None:
            return None
        cerca = self.getClazzByTypeName(myobj.getValue('classname'))()
        cerca.setValue('id',myobj.getValue('id'))
        lista = self.search(cerca,uselike=0,ignore_deleted=ignore_deleted)
        if self._verbose: print "ObjectMgr.fullObjectById: lista=%s" % ( len( lista ) )
        if len(lista)==1:
            return lista[0]
        return None
    def objectByName(self, name,ignore_deleted=True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.objectByName(name,ignore_deleted)
        tipi = self.getRegisteredTypes()
        q = []
        for tablename, clazz in tipi.items():
            mydbe = clazz()
            if not isinstance(mydbe, DBEObject) or isinstance(mydbe, DBAssociation): # or mydbe.getTypeName=="DBEObject":
                continue
            tmp_q = """
select '%s' as classname,id,owner,group_id,permissions,creator,
       creation_date,last_modify,last_modify_date,father_id,name,description
  from %s
 where name='%s'
""" % (mydbe.getTypeName(), self.buildTableName(mydbe), name )
            if ignore_deleted:
                if self._connProvider.getDBType()=="POSTGRESQL":
                    tmp_q += " and deleted_date is null"
                else:
                    tmp_q += " and deleted_date='0000-00-00 00:00:00'"
            q.append( tmp_q )
        searchString = " union ".join( q )
        if self._verbose: print "ObjectMgr.objectById: searchString=%s" % ( searchString )
        lista = self.select("objects", searchString)
        if len(lista)==1:
            return lista[0]
        return None
    def fullObjectByName(self, name, ignore_deleted = True):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.fullObjectByName(name,ignore_deleted)
        myobj = self.objectByName(name,ignore_deleted)
        if myobj is None:
            return None
        cerca = self.getClazzByTypeName(myobj.getValue('classname'))()
        cerca.setValue('id',myobj.getValue('id'))
        lista = self.search(cerca,uselike=0,ignore_deleted=ignore_deleted)
        if self._verbose: print "ObjectMgr.fullObjectById: lista=%s" % ( len( lista ) )
        if len(lista)==1:
            return lista[0]
        return None
    def downloadFile(self,remote_filename,local_path,view_thumbnail=False):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.downloadFile(remote_filename,local_path,view_thumbnail)
        myobj = self.fullObjectById(remote_filename)
        myobj._dest_directory = os.path.realpath(self.getConnectionProvider().getLocalFilePath())
        fullpath = myobj.getFullpath()
        local_filename = "%s%s%s" % (local_path,os.path.sep,myobj.getValue('name'))
        return self._connProvider.downloadFile(fullpath,local_filename,view_thumbnail)


class DBECountry(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBECountry.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBECountry.__columns[k]=parentcols[k]
            DBECountry.__columns['id']=["uuid","not null"]
            DBECountry.__columns['Common_Name']=["varchar(255)","default null"]
            DBECountry.__columns['Formal_Name']=["varchar(255)","default null"]
            DBECountry.__columns['Type']=["varchar(255)","default null"]
            DBECountry.__columns['Sub_Type']=["varchar(255)","default null"]
            DBECountry.__columns['Sovereignty']=["varchar(255)","default null"]
            DBECountry.__columns['Capital']=["varchar(255)","default null"]
            DBECountry.__columns['ISO_4217_Currency_Code']=["varchar(255)","default null"]
            DBECountry.__columns['ISO_4217_Currency_Name']=["varchar(255)","default null"]
            DBECountry.__columns['ITU_T_Telephone_Code']=["varchar(255)","default null"]
            DBECountry.__columns['ISO_3166_1_2_Letter_Code']=["varchar(255)","default null"]
            DBECountry.__columns['ISO_3166_1_3_Letter_Code']=["varchar(255)","default null"]
            DBECountry.__columns['ISO_3166_1_Number']=["varchar(255)","default null"]
            DBECountry.__columns['IANA_Country_Code_TLD']=["varchar(255)","default null"]
        self._columns=DBECountry.__columns
    def getTableName(self):
        return "countrylist"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [  ]
    def getOrderBy(self):
        return [ "id" ]
dbschema['countrylist'] = DBECountry

class DBECompany(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBECompany.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBECompany.__columns[k]=parentcols[k]
            DBECompany.__columns['street']=["varchar(255)","default null"]
            DBECompany.__columns['zip']=["varchar(255)","default null"]
            DBECompany.__columns['city']=["varchar(255)","default null"]
            DBECompany.__columns['state']=["varchar(255)","default null"]
            DBECompany.__columns['fk_countrylist_id']=["uuid","default null"]
            DBECompany.__columns['phone']=["varchar(255)","default null"]
            DBECompany.__columns['fax']=["varchar(255)","default null"]
            DBECompany.__columns['email']=["varchar(255)","default null"]
            DBECompany.__columns['url']=["varchar(255)","default null"]
            DBECompany.__columns['p_iva']=["varchar(16)","default null"]
        self._columns=DBECompany.__columns
    def getTableName(self):
        return "companies"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","companies","id"), ForeignKey("fk_countrylist_id","countrylist","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['companies'] = DBECompany

class DBEPeople(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEPeople.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEPeople.__columns[k]=parentcols[k]
            DBEPeople.__columns['street']=["varchar(255)","default null"]
            DBEPeople.__columns['zip']=["varchar(255)","default null"]
            DBEPeople.__columns['city']=["varchar(255)","default null"]
            DBEPeople.__columns['state']=["varchar(255)","default null"]
            DBEPeople.__columns['fk_countrylist_id']=["uuid","default null"]
            DBEPeople.__columns['fk_companies_id']=["uuid","default null"]
            DBEPeople.__columns['fk_users_id']=["uuid","default null"]
            DBEPeople.__columns['phone']=["varchar(255)","default null"]
            DBEPeople.__columns['office_phone']=["varchar(255)","default null"]
            DBEPeople.__columns['mobile']=["varchar(255)","default null"]
            DBEPeople.__columns['fax']=["varchar(255)","default null"]
            DBEPeople.__columns['email']=["varchar(255)","default null"]
            DBEPeople.__columns['url']=["varchar(255)","default null"]
            DBEPeople.__columns['codice_fiscale']=["varchar(20)","default null"]
            DBEPeople.__columns['p_iva']=["varchar(16)","default null"]
        self._columns=DBEPeople.__columns
    def getTableName(self):
        return "people"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","people","id"), ForeignKey("fk_countrylist_id","countrylist","id"), ForeignKey("fk_companies_id","companies","id"), ForeignKey("fk_users_id","users","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['people'] = DBEPeople

class DBEEvent(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEEvent.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEEvent.__columns[k]=parentcols[k]
            DBEEvent.__columns['fk_obj_id']=["uuid","default null"]
            DBEEvent.__columns['start_date']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBEEvent.__columns['end_date']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBEEvent.__columns['all_day']=["char(1)","not null default '1'"]
            DBEEvent.__columns['url']=["varchar(255)","default null"]
            DBEEvent.__columns['alarm']=["char(1)","default '0'"]
            DBEEvent.__columns['alarm_minute']=["int","default 0"]
            DBEEvent.__columns['alarm_unit']=["char(1)","default '0'"]
            DBEEvent.__columns['before_event']=["char(1)","default '0'"]
            DBEEvent.__columns['category']=["varchar(255)","default ''"]
            DBEEvent.__columns['recurrence']=["char(1)","default '0'"]
            DBEEvent.__columns['recurrence_type']=["char(1)","default '0'"]
            DBEEvent.__columns['daily_every_x']=["int","default 0"]
            DBEEvent.__columns['weekly_every_x']=["int","default 0"]
            DBEEvent.__columns['weekly_day_of_the_week']=["char(1)","default '0'"]
            DBEEvent.__columns['monthly_every_x']=["int","default 0"]
            DBEEvent.__columns['monthly_day_of_the_month']=["int","default 0"]
            DBEEvent.__columns['monthly_week_number']=["int","default 0"]
            DBEEvent.__columns['monthly_week_day']=["char(1)","default '0'"]
            DBEEvent.__columns['yearly_month_number']=["int","default 0"]
            DBEEvent.__columns['yearly_month_day']=["int","default 0"]
            DBEEvent.__columns['yearly_week_number']=["int","default 0"]
            DBEEvent.__columns['yearly_week_day']=["char(1)","default '0'"]
            DBEEvent.__columns['yearly_day_of_the_year']=["int","default 0"]
            DBEEvent.__columns['recurrence_times']=["int","default 0"]
            DBEEvent.__columns['recurrence_end_date']=["datetime","not null default '0000-00-00 00:00:00'"]
        self._columns=DBEEvent.__columns
    def getTableName(self):
        return "events"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","events","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['events'] = DBEEvent

class DBEFile(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEFile.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEFile.__columns[k]=parentcols[k]
            DBEFile.__columns['fk_obj_id']=["uuid","default null"]
            DBEFile.__columns['path']=["text","default null"]
            DBEFile.__columns['filename']=["text","not null"]
            DBEFile.__columns['checksum']=["char(40)","default null"]
            DBEFile.__columns['mime']=["varchar(255)","default null"]
            DBEFile.__columns['alt_link']=["varchar(255)","not null default ''"]
        self._columns=DBEFile.__columns
        
        self._dest_directory = ''
    def getTableName(self):
        return "files"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","files","id"), ForeignKey("father_id","folders","id"), ForeignKey("fk_obj_id","pages","id"), ForeignKey("father_id","pages","id"), ForeignKey("fk_obj_id","news","id"), ForeignKey("father_id","news","id") ]
    def getOrderBy(self):
        return [ "path", "filename" ]
    def generaObjectPath(self,a_dbe=None):
        _dbe = self
        if not a_dbe is None:
            _dbe = a_dbe
        dest_path = ''
        if _dbe.getValue('path')>'':
            dest_path = _dbe.getValue('path')
        father_id = _dbe.getValue('father_id')
        if father_id>'':
            if dest_path>'':
                dest_path = "%s%s%s" % (father_id,os.path.sep,dest_path)
            else:
                dest_path = "%s" % (father_id)
        return dest_path
    def getFullpath(self,a_dest_dir="",a_dbe=None):
        _dbe = self
        if not a_dbe is None:
            _dbe = a_dbe
        dest_path = _dbe.generaObjectPath()
        dest_dir = a_dest_dir
        if a_dest_dir=='':
            dest_dir = self._dest_directory
        if dest_path>'': dest_dir="%s%s%s" % (dest_dir,os.path.sep,dest_path)
        ret = dest_dir="%s%s%s" % (dest_dir,os.path.sep,_dbe.getValue('filename'))
        return ret
    def generaFilename(self, aId=None, aFilename=None):
        nomefile = self.getValue('filename')
        if not aFilename is None:
            nomefile = aFilename
        _id=self.getValue('id')
        if not aId is None:
            _id = aId
        prefisso = "r_%s_" % _id
        if nomefile.find(prefisso)>=0:
            nomefile = nomefile.replace(prefisso,"")
        return "%s%s" % (prefisso,nomefile)
    # Image management: start.
    def getThumbnailFilename(self):
        return "%s_thumb.jpg" % (self.getValue('filename'))
    def isImage(self):
        _mime = self.getValue('mime')
        return _mime>'' and _mime[0:5]=='image'
    # TODO
    def createThumbnail(self,fullpath,pix_width=100,pix_height=100):
        try:
            # Please install Python Imaging :-)
            from PIL import Image
            image = Image.open(fullpath)
            image = image.resize((pix_width, pix_height), Image.ANTIALIAS)
            image.save("%s_thumb.jpg" % fullpath)
            return "%s_thumb.jpg" % fullpath
        except Exception,e:
            return ""
    def deleteThumbnail(self,fullpath):
        os.remove("%s_thumb.jpg" % fullpath)
    # Image management: end.
    
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        self._dest_directory = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
        
        if not self.getValue('filename')>'' or not os.path.exists(self.getValue('filename')):
            return
        if self.getValue('name')=='New DBEFile':
            self.setValue('name','')
        
        if self.getValue('filename')>'':
            # Uploading
            local_filename = self.getValue('filename')
            self.setValue('filename', dbmgr.uploadFile(self.getValue('filename')) )
            
            dest_path = self.generaObjectPath()
            from_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            dest_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            if dest_path>'':
                dest_dir = "%s%s%s" % (dest_dir,os.path.sep,dest_path)
            if not os.path.exists(dest_dir): os.mkdir(dest_dir)
            nuovo_filename = self.generaFilename(\
                    self.getValue('id'),\
                    self.getValue('filename').split(os.path.sep)[-1]\
                    )
            filefrom=self.getValue('filename')
            fileto="%s%s%s" % (dest_dir,os.path.sep,nuovo_filename )
            os.rename( filefrom, fileto )
            if not self.getValue('name')>'':
                self.setValue('name',self.getValue('filename').split(os.path.sep)[-1])
            self.setValue('filename', nuovo_filename)
        # Checksum
        _fullpath = self.getFullpath()
        if os.path.exists(_fullpath):
            f = open(_fullpath,'rb')
            import hashlib
            h = hashlib.sha1()
            h.update(f.read())
            newchecksum = h.hexdigest()
            f.close()
            self.setValue('checksum',newchecksum)
        else:
            self.setValue('checksum',"File '%s' not found!"%self.getValue('filename'))
        # Mime type
        mime = 'text/plain'
        if os.path.exists(_fullpath):
            import mimetypes
            mime,altro = mimetypes.guess_type("file://%s"%_fullpath)
        self.setValue('mime',mime)
        # Image
        if self.isImage():
            thumbpath = self.createThumbnail(_fullpath)
    def _before_update(self,dbmgr=None):
        DBEObject._before_update(self,dbmgr)
        self._dest_directory = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
        # Inherits father' root
        father_id = self.getValue('father_id')
        if father_id>'':
            query="select fk_obj_id from %s  where id='%s'" % (dbmgr.buildTableName(self),self.getValue('father_id'))
            tmp = dbmgr.select(self.getTableName(),query)
            if len(tmp)==1:
                self.setValue('fk_obj_id', tmp[0].getValue('fk_obj_id') )
        # An old file exists?
        cerca = dbmgr.getClazzByTypeName(self.getTypeName())()
        cerca.setValue('id',self.getValue('id'))
        lista = dbmgr.search(cerca,uselike=0)
        myself = lista[0]
        if self.getValue('filename')>'' and myself.getValue('filename')!=self.getValue('filename')\
                and os.path.exists(self.getValue('filename')):
            # Different filenames ==> remove the old file
            dest_path = myself.generaObjectPath()
            dest_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            if dest_path>'':
                dest_dir = "%s%s%s" % (dest_dir,os.path.sep,dest_path)
            dest_file = "%s%s%s" % (dest_dir,os.path.sep,myself.generaFilename())
            if os.path.exists(dest_file):
                os.remove(dest_file)
                if self.isImage():
                    self.deleteThumbnail(dest_file)
        
        if self.getValue('filename')>'':
            # Computing from_*
            from_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            from_path = self.generaObjectPath(myself)
            from_filename = self.generaFilename(\
                    self.getValue('id'),\
                    self.getValue('filename').split(os.path.sep)[-1]\
                    )
            if from_path>'':
                # FIXME why this works?
                from_dir = "%s%s%s" % (from_dir,os.path.sep,from_path)
                #from_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
                #filefrom=self.getValue('filename')
            # Uploading
            if os.path.exists(self.getValue('filename')):
                local_filename = self.getValue('filename')
                self.setValue('filename', dbmgr.uploadFile(self.getValue('filename')) )
                from_filename = self.getValue('filename')
                filefrom = from_filename
            else:
                filefrom="%s%s%s" % (from_dir,os.path.sep,from_filename)
            dest_path = self.generaObjectPath()
            dest_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            if dest_path>'':
                dest_dir = "%s%s%s" % (dest_dir,os.path.sep,dest_path)
            if not os.path.exists(dest_dir): os.mkdir(dest_dir)
            nuovo_filename = self.generaFilename(\
                    self.getValue('id'),\
                    self.getValue('filename').split(os.path.sep)[-1]\
                    )
            fileto="%s%s%s" % (dest_dir,os.path.sep,nuovo_filename)
            os.rename( filefrom, fileto )
            #raise DBLayerException("File '%s' does not exists"%filefrom)
            if not os.path.exists(fileto):
                raise DBLayerException("File '%s' does not exists"%fileto)
            if not self.getValue('name')>'':
                self.setValue('name',self.getValue('filename').split(os.path.sep)[-1])
            self.setValue('filename', nuovo_filename)
        elif myself.getValue('path')!=self.getValue('path'):
            from_path = self.generaObjectPath()
            from_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            if from_path>'':
                from_dir = "%s%s%s" % (from_dir,os.path.sep,from_path)
            dest_path = self.generaObjectPath()
            dest_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
            if dest_path>'':
                dest_dir = "%s%s%s" % (dest_dir,os.path.sep,dest_path)
            if not os.path.exists(dest_dir): os.mkdir(dest_dir)
            os.rename( "%s%s%s" % (from_dir,os.path.sep,myself.getValue('filename')),\
                        "%s%s%s" % (dest_dir,os.path.sep,myself.getValue('filename') ) )
            self.setValue('filename', myself.getValue('filename'))
        else:
            self.setValue('filename', myself.getValue('filename'))
        # Checksum
        _fullpath = self.getFullpath()
        if os.path.exists(_fullpath):
            f = open(_fullpath,'rb')
            import hashlib
            h = hashlib.sha1() 
            h.update(f.read()) 
            newchecksum = h.hexdigest() 
            f.close()
            self.setValue('checksum',newchecksum)
        else:
            self.setValue('checksum',"File '%s' not found!"%self.getValue('filename'))
        # Mime type
        mime = 'text/plain'
        if os.path.exists(_fullpath):
            import mimetypes
            mime,altro = mimetypes.guess_type("file://%s"%_fullpath)
        self.setValue('mime',mime)
        # Image
        if self.isImage():
            thumbpath = self.createThumbnail(_fullpath)
    def _before_delete(self,dbmgr=None):
        is_deleted = self.isDeleted()
        DBEObject._before_delete(self,dbmgr)
        self._dest_directory = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
        if is_deleted:
            # An old file exists?
            cerca = dbmgr.getClazzByTypeName(self.getTypeName())()
            cerca.setValue('id',self.getValue('id'))
            tmp = dbmgr.search(cerca,0,None,False)#,False,None,False)
            if len(tmp)>0:
                myself=tmp[0]
                if myself.getValue('filename')>'':
                    # ==> remove file
                    dest_path = self.generaObjectPath()
                    dest_dir = os.path.realpath(dbmgr.getConnectionProvider().getLocalFilePath())
                    if dest_path>'':
                        dest_dir = "%s%s%s" % (dest_dir,os.path.sep,dest_path)
                    os.remove( "%s%s%s" % (dest_dir,os.path.sep,myself.generaFilename() ) )
                    # Image
                    if self.isImage():
                        self.deleteThumbnail("%s%s%s" % (dest_dir,os.path.sep,myself.generaFilename() ))
dbschema['files'] = DBEFile

class DBEFolder(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEFolder.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEFolder.__columns[k]=parentcols[k]
            DBEFolder.__columns['fk_obj_id']=["uuid","default null"]
            DBEFolder.__columns['childs_sort_order']=["text","default null"]
        self._columns=DBEFolder.__columns
    def getTableName(self):
        return "folders"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","folders","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
    def setDefaultValues(self,dbmgr):
        DBEObject.setDefaultValues(self,dbmgr)
        
        if self.getValue('father_id') is None:
            pass
        else:
            cerca = DBEFolder()
            cerca.setValue('id',self.getValue('father_id'))
            lista = dbmgr.search(cerca,uselike=0)
            if len(lista)==1:
                father = lista[0]
                self.setValue('fk_obj_id',father.getValue('fk_obj_id'))
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        father_id = self.getValue('father_id')
        if father_id>'':
            query = "select fk_obj_id from %s where id='%s'"%(\
                dbmgr.buildTableName(self),\
                ("%-16s"%self.getValue('father_id')).replace(' ','\0')\
                )
            tmp = dbmgr.select(self.getTableName(),query)
            if len(tmp)==1:
                self.setValue('fk_obj_id', tmp[0].getValue('fk_obj_id') )
    def _before_update(self,dbmgr=None):
        DBEObject._before_update(self,dbmgr)
        father_id = self.getValue('father_id')
        if father_id>'':
            query = "select fk_obj_id from %s where id='%s'"%(\
                dbmgr.buildTableName(self),\
                ("%-16s"%self.getValue('father_id')).replace(' ','\0')\
                )
            tmp = dbmgr.select(self.getTableName(),query)
            if len(tmp)==1:
                self.setValue('fk_obj_id', tmp[0].getValue('fk_obj_id') )
    def _before_delete(self,dbmgr=None):
        DBEObject._before_delete(self,dbmgr)
        cerca = DBEObject()
        cerca.setValue('father_id',self.getValue('id'))
        lista = dbmgr.search(cerca,uselike=0)
        for l in lista:
            dbmgr.delete(l)
dbschema['folders'] = DBEFolder

class DBELink(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBELink.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBELink.__columns[k]=parentcols[k]
            DBELink.__columns['href']=["varchar(255)","not null"]
            DBELink.__columns['target']=["varchar(255)","default '_blank'"]
            DBELink.__columns['fk_obj_id']=["uuid","default null"]
        self._columns=DBELink.__columns
    def getTableName(self):
        return "links"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","links","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id"), ForeignKey("fk_obj_id","pages","id"), ForeignKey("father_id","pages","id"), ForeignKey("fk_obj_id","news","id"), ForeignKey("father_id","news","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['links'] = DBELink

class DBENote(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBENote.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBENote.__columns[k]=parentcols[k]
            DBENote.__columns['fk_obj_id']=["uuid","default null"]
        self._columns=DBENote.__columns
    def getTableName(self):
        return "notes"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","notes","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['notes'] = DBENote

class DBEPage(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEPage.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEPage.__columns[k]=parentcols[k]
            DBEPage.__columns['html']=["text","default null"]
            DBEPage.__columns['fk_obj_id']=["uuid","default null"]
            DBEPage.__columns['language']=["varchar(5)","default 'en_us'"]
        self._columns=DBEPage.__columns
    def getTableName(self):
        return "pages"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","pages","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['pages'] = DBEPage

class DBENews(DBEPage):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEPage.__init__(self, tablename, names, values, attrs, keys )
        if len(DBENews.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBENews.__columns[k]=parentcols[k]
        self._columns=DBENews.__columns
    def getTableName(self):
        return "news"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","news","id"), ForeignKey("fk_obj_id","companies","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","people","id"), ForeignKey("fk_obj_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
dbschema['news'] = DBENews

class DBEBanned(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEBanned.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEBanned.__columns[k]=parentcols[k]
            DBEBanned.__columns['ban_ip']=["varchar(40)","default null"]
            DBEBanned.__columns['redirected_to']=["varchar(40)","default 'http://adf.ly/XdZw'"]
            DBEBanned.__columns['give_reason']=["varchar(255)","default 'Your IP has been blocked.<br/>See http://adf.ly/XdZw for more details.'"]
        self._columns=DBEBanned.__columns
    def getTableName(self):
        return "banned"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","banned","id") ]
    def getOrderBy(self):
        return [ "creation_date desc, ban_ip" ]
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        self.setValue( 'permissions', 'rwxr--r--' )
dbschema['banned'] = DBEBanned

class DBEMail(DBEFile):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEFile.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEMail.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEMail.__columns[k]=parentcols[k]
            DBEMail.__columns['msgid']=["varchar(255)","not null default ''"]
            DBEMail.__columns['subject']=["varchar(255)","default null"]
            DBEMail.__columns['msgfrom']=["varchar(255)","default null"]
            DBEMail.__columns['msgto']=["varchar(255)","default null"]
            DBEMail.__columns['msgcc']=["varchar(255)","default null"]
            DBEMail.__columns['msgdate']=["datetime","default '1000-01-01 00:00:00'"]
            DBEMail.__columns['msgbody']=["text","default ''"]
        self._columns=DBEMail.__columns
    def getTableName(self):
        return "mail"
    def getKeys(self):
        return { 'id':'uuid',  'msgid':'string' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","mail","id"), ForeignKey("father_id","folders","id"), ForeignKey("fk_obj_id","pages","id"), ForeignKey("father_id","pages","id"), ForeignKey("fk_obj_id","news","id"), ForeignKey("father_id","news","id") ]
    def getOrderBy(self):
        return [ "msgdate desc" ]
    def _before_insert(self,dbmgr=None):
        DBEFile._before_insert(self,dbmgr)
        self.setValue( 'permissions', 'rwx------' )
        _fullpath = self.getFullpath()
        self.parseEmail(_fullpath)
        self.checkUniqueMessageId( self.getValue('msgid'), dbmgr)
    def checkUniqueMessageId( self,  msgid, dbmgr ):
        if msgid is None or len(msgid)==0:
            return
        cerca = DBEMail()
        cerca.setValue('msgid', msgid)
        lista = dbmgr.search(cerca, uselike=0)
        if len(lista)>0:
            raise DBLayerException("More than one email with the same Message-ID: '%s'"%msgid)
    def _before_update(self,dbmgr=None):
        DBEFile._before_update(self,dbmgr)
        _fullpath = self.getFullpath()
        self.parseEmail(_fullpath)
    def parseEmail(self,filename):
        f = file(filename,'rb').read()
        import email
        msg = email.message_from_string(f)
        if msg.has_key('Message-ID'):
            self.setValue('msgid', self._filterString(msg['Message-ID']) )
        if msg.has_key('Subject'):
            self.setValue('subject', self._filterString(msg['Subject']) )
            self.setValue('name', self._filterString(msg['Subject']) )
        if msg.has_key('From'):
            self.setValue('msgfrom', self._filterString(msg['From']) )
        if msg.has_key('To'):
            self.setValue('msgto', self._filterString(msg['To']) )
        if msg.has_key('Cc'):
            self.setValue('msgcc', self._filterString(msg['Cc']) )
        if msg.has_key('Date'):
            try:
                mytuple = email.utils.parsedate("%s"%msg['Date'])
                self.setValue('msgdate',datetime.datetime( mytuple[0],mytuple[1],mytuple[2],mytuple[3],mytuple[4],mytuple[5],mytuple[6] ))
            except Exception,e:
                pass
        msgbody = ''
        for part in msg.walk():
            if part.get_content_type()=='text/plain':
                msgbody = part.get_payload()
                if len(msgbody.strip())>0:
                    break
        self.setValue('msgbody', self._filterString(msgbody) )
    def _filterString(self, s):
        """See http://docs.python.org/howto/unicode.html"""
        ret = unicode(s, errors='replace') # replace with \uXXXX 
        return ret
dbschema['mail'] = DBEMail


# #### Projects: start.

class DBEProject(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProject.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProject.__columns[k]=parentcols[k]
        self._columns=DBEProject.__columns
    def getTableName(self):
        return "projects"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","projects","id") ]
    def getOrderBy(self):
        return [ "name" ]
    def _before_delete(self,dbmgr=None):
        DBEObject._before_delete(self,dbmgr)
dbschema['projects'] = DBEProject

class DBEProjectCompanyRole(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectCompanyRole.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectCompanyRole.__columns[k]=parentcols[k]
            DBEProjectCompanyRole.__columns['order_position']=["int","default 0"]
        self._columns=DBEProjectCompanyRole.__columns
    def getTableName(self):
        return "projects_companies_roles"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","projects_companies_roles","id") ]
    def getOrderBy(self):
        return [ "order_position", "id" ]
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        query="select max(order_position) as order_position from %s" % dbmgr.buildTableName(self)
        tmp = dbmgr.select(self.getTableName(),query)
        if len(tmp)==1:
            tmp_position = tmp[0].getValue('order_position')
            if tmp_position is None:
                tmp_position = 0
            else:
                tmp_position = int(tmp_position)
            self.setValue('order_position', tmp_position + 1 )
        else:
            self.setValue('order_position', 1 )
dbschema['projects_companies_roles'] = DBEProjectCompanyRole

class DBEProjectCompany(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectCompany.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectCompany.__columns[k]=parentcols[k]
            DBEProjectCompany.__columns['project_id']=["uuid","not null default ''"]
            DBEProjectCompany.__columns['company_id']=["uuid","not null default ''"]
            DBEProjectCompany.__columns['projects_companies_role_id']=["uuid","not null default ''"]
        self._columns=DBEProjectCompany.__columns
    def getTableName(self):
        return "projects_companies"
    def getKeys(self):
        return { 'project_id':'uuid', 'company_id':'uuid', 'projects_companies_role_id':'uuid' }
    def getFK(self):
        return [ ForeignKey("project_id","projects","id"), ForeignKey("company_id","companies","id"), ForeignKey("projects_companies_role_id","projects_companies_roles","id") ]
    def getOrderBy(self):
        return [ "project_id", "company_id", "projects_companies_role_id" ]
dbschema['projects_companies'] = DBEProjectCompany

class DBEProjectPeopleRole(DBEObject,DBAssociation): #DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectPeopleRole.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectPeopleRole.__columns[k]=parentcols[k]
            DBEProjectPeopleRole.__columns['order_position']=["int","default 0"]
        self._columns=DBEProjectPeopleRole.__columns
    def getTableName(self):
        return "projects_people_roles"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [  ]
    def getOrderBy(self):
        return [ "order_position", "id" ]
    def getDefaultEntries(self):
        return [\
            {'order_position':'1','name':'Project Manager','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1',},\
            {'order_position':'2','name':'Analyst','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1',},\
            {'order_position':'3','name':'Designer','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1',},\
            {'order_position':'4','name':'Developer','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1',},\
            {'order_position':'5','name':'Graphical Designer','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1',},\
            ]
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        tmp = []
        query="select max(order_position) as order_position from %s" % dbmgr.buildTableName(self)
        try:
            tmp = dbmgr.select(self.getTableName(),query)
        except Exception,  e:
            # TODO Likely we are on MongoDB
            pass
        if len(tmp)==1:
            tmp_position = tmp[0].getValue('order_position')
            if tmp_position is None:
                tmp_position = 0
            else:
                tmp_position = int(tmp_position)
            self.setValue('order_position', tmp_position + 1 )
        else:
            self.setValue('order_position', 1 )
dbschema['projects_people_roles'] = DBEProjectPeopleRole

class DBEProjectPeople(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectPeople.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectPeople.__columns[k]=parentcols[k]
            DBEProjectPeople.__columns['project_id']=["uuid","not null default ''"]
            DBEProjectPeople.__columns['people_id']=["uuid","not null default ''"]
            DBEProjectPeople.__columns['projects_people_role_id']=["uuid","not null default ''"]
        self._columns=DBEProjectPeople.__columns
    def getTableName(self):
        return "projects_people"
    def getKeys(self):
        return { 'project_id':'uuid', 'people_id':'uuid', 'projects_people_role_id':'uuid' }
    def getFK(self):
        return [ ForeignKey("project_id","projects","id"), ForeignKey("people_id","people","id"), ForeignKey("projects_people_role_id","projects_people_roles","id") ]
    def getOrderBy(self):
        return [ "project_id", "people_id", "projects_people_role_id" ]
dbschema['projects_people'] = DBEProjectPeople

class DBEProjectProjectRole(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectProjectRole.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectProjectRole.__columns[k]=parentcols[k]
            DBEProjectProjectRole.__columns['order_position']=["int","default 0"]
        self._columns=DBEProjectProjectRole.__columns
    def getTableName(self):
        return "projects_projects_roles"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","projects_projects_roles","id") ]
    def getOrderBy(self):
        return [ "order_position", "id" ]
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        query="select max(order_position) as order_position from %s" % dbmgr.buildTableName(self)
        tmp = dbmgr.select(self.getTableName(),query)
        if len(tmp)==1:
            tmp_position = tmp[0].getValue('order_position')
            if tmp_position is None:
                tmp_position = 0
            else:
                tmp_position = int(tmp_position)
            self.setValue('order_position', tmp_position + 1 )
        else:
            self.setValue('order_position', 1 )
dbschema['projects_projects_roles'] = DBEProjectProjectRole

class DBEProjectProject(DBEntity):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEntity.__init__(self, tablename, names, values, attrs, keys )
        if len(DBEProjectProject.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBEProjectProject.__columns[k]=parentcols[k]
            DBEProjectProject.__columns['project_id']=["uuid","not null default ''"]
            DBEProjectProject.__columns['project2_id']=["uuid","not null default ''"]
            DBEProjectProject.__columns['projects_projects_role_id']=["uuid","not null default ''"]
        self._columns=DBEProjectProject.__columns
    def getTableName(self):
        return "projects_projects"
    def getKeys(self):
        return { 'project_id':'uuid', 'project2_id':'uuid', 'projects_projects_role_id':'uuid' }
    def getFK(self):
        return [ ForeignKey("project_id","projects","id"), ForeignKey("project2_id","projects","id"), ForeignKey("projects_projects_role_id","projects_projects_roles","id") ]
    def getOrderBy(self):
        return [ "project_id", "project2_id", "projects_projects_role_id" ]
dbschema['projects_projects'] = DBEProjectProject

class DBETimetrack(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBETimetrack.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBETimetrack.__columns[k]=parentcols[k]
            DBETimetrack.__columns['fk_obj_id']=["uuid","default null"]
            DBETimetrack.__columns['fk_progetto']=["uuid","default null"]
            DBETimetrack.__columns['dalle_ore']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBETimetrack.__columns['alle_ore']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBETimetrack.__columns['ore_intervento']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBETimetrack.__columns['ore_viaggio']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBETimetrack.__columns['km_viaggio']=["int","not null default 0"]
            DBETimetrack.__columns['luogo_di_intervento']=["int","not null default 0"]
            DBETimetrack.__columns['stato']=["int","not null default 0"]
            DBETimetrack.__columns['costo_per_ora']=["float","not null default 0"]
            DBETimetrack.__columns['costo_valuta']=["varchar(255)","default null"]
        self._columns=DBETimetrack.__columns
    def getTableName(self):
        return "timetracks"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","timetracks","id"), ForeignKey("fk_obj_id","projects","id"), ForeignKey("fk_obj_id","folders","id"), ForeignKey("fk_obj_id","todo","id"), ForeignKey("fk_progetto","projects","id") ]
    def getOrderBy(self):
        return [ "fk_progetto", "stato desc", "dalle_ore desc", "fk_obj_id", "name" ]
dbschema['timetracks'] = DBETimetrack

class DBETodo(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBETodo.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBETodo.__columns[k]=parentcols[k]
            DBETodo.__columns['priority']=["int","not null default 0"]
            DBETodo.__columns['data_segnalazione']=["datetime","not null default '0000-00-00 00:00:00'"]
            DBETodo.__columns['fk_segnalato_da']=["uuid","default null"]
            DBETodo.__columns['fk_cliente']=["uuid","default null"]
            DBETodo.__columns['fk_progetto']=["uuid","default null"]
            DBETodo.__columns['fk_funzionalita']=["uuid","default null"]
            DBETodo.__columns['fk_tipo']=["uuid","default null"]
            DBETodo.__columns['stato']=["int","not null default 0"]
            DBETodo.__columns['descrizione']=["text","not null"]
            DBETodo.__columns['intervento']=["text","not null"]
            DBETodo.__columns['data_chiusura']=["datetime","not null default '0000-00-00 00:00:00'"]
        self._columns=DBETodo.__columns
    def getTableName(self):
        return "todo"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","todo","id"), ForeignKey("fk_segnalato_da","people","id"), ForeignKey("fk_cliente","companies","id"), ForeignKey("fk_progetto","projects","id"), ForeignKey("father_id","folders","id"), ForeignKey("father_id","todo","id"), ForeignKey("fk_tipo","todo_tipo","id") ]
    def getOrderBy(self):
        return [ "priority desc", "data_segnalazione desc", "fk_progetto", "name" ]
    def setDefaultValues(self,dbmgr):
        DBEObject.setDefaultValues(self,dbmgr)
        data_segnalazione = self.getValue('data_segnalazione')
        if data_segnalazione is None or data_segnalazione=="" or data_segnalazione=="00:00"\
                or self._formatDateTime(data_segnalazione).find("0000-00-00 00:00")>=0:
            self.setValue('data_segnalazione', self._getTodayString())
    def _before_insert(self,dbmgr=None):
        DBEObject._before_insert(self,dbmgr)
        data_segnalazione = self.getValue('data_segnalazione')
        if data_segnalazione is None or data_segnalazione=="" or data_segnalazione=="00:00"\
                or self._formatDateTime(data_segnalazione).find("0000-00-00 00:00")>=0:
            self.setValue('data_segnalazione', self._getTodayString())
        if dbmgr.getConnectionProvider().getDBType()=="POSTGRESQL":
            if self.getValue('data_segnalazione') in ['0000-00-00 00:00', '0000-00-00 00:00:00']:
                self.setValue('data_segnalazione',None)
            if self.getValue('data_chiusura') in ['0000-00-00 00:00', '0000-00-00 00:00:00']:
                self.setValue('data_chiusura',None)
        self._RULE_check_closed(dbmgr)
    def _before_update(self,dbmgr=None):
        DBEObject._before_update(self,dbmgr)
        if dbmgr.getConnectionProvider().getDBType()=="POSTGRESQL":
            if self.getValue('data_segnalazione') in ['0000-00-00 00:00', '0000-00-00 00:00:00']:
                self.setValue('data_segnalazione',None)
            if self.getValue('data_chiusura') in ['0000-00-00 00:00', '0000-00-00 00:00:00']:
                self.setValue('data_chiusura',None)
        self._RULE_check_closed(dbmgr)
        self._RULE_check_reopening(dbmgr)
    def _RULE_check_closed(self,dbmgr):
        data_chiusura = self.getValue('data_chiusura')
        if data_chiusura is None or data_chiusura==""\
                or self._formatDateTime(data_chiusura).find("0000-00-00 00:00")>=0:
            stato = 0
            try:
                stato = int( self.getValue('stato') )
            except Exception,e:
                pass
            if stato>=100:
                self.setValue('data_chiusura', dbmgr.getTodayString())
                self.setValue('stato', 100)
    def _RULE_check_reopening(self,dbmgr):
        data_chiusura = self.getValue('data_chiusura')
        if data_chiusura is None or data_chiusura==""\
                or self._formatDateTime(data_chiusura).find("0000-00-00 00:00")>=0:
            pass
        else:
            stato = 0
            try:
                stato = int( self.getValue('stato') )
            except Exception,e:
                pass
            if stato<100:
                self.setValue('data_chiusura', None)
dbschema['todo'] = DBETodo

class DBETodoTipo(DBEObject):
    __columns={}
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):
        DBEObject.__init__(self, tablename, names, values, attrs, keys )
        if len(DBETodoTipo.__columns.keys())==0:
            parentcols=self.getColumns()
            for k in parentcols.keys():
                DBETodoTipo.__columns[k]=parentcols[k]
            DBETodoTipo.__columns['order_position']=["int","default 0"]
        self._columns=DBETodoTipo.__columns
    def getTableName(self):
        return "todo_tipo"
    def getKeys(self):
        return { 'id':'uuid' }
    def getFK(self):
        return [ ForeignKey("owner","users","id"), ForeignKey("group_id","groups","id"), ForeignKey("creator","users","id"), ForeignKey("last_modify","users","id"), ForeignKey("deleted_by","users","id"), ForeignKey("father_id","todo_tipo","id") ]
    def getOrderBy(self):
        return [ "order_position", "id" ]
    def getDefaultEntries(self):
        return [\
            {'order_position':1, 'name':'Analysis','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':2, 'name':'Design','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':3, 'name':'Development','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':4, 'name':'Test','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':5, 'name':'Bug','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':6, 'name':'Documentation','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
            {'order_position':7, 'name':'Install','owner':'-1','group_id':'-2','creator':'-1','last_modify':'-1','permissions':'rwxr--r--',},\
        ]
dbschema['todo_tipo'] = DBETodoTipo

# #### Projects: end.


dbeFactory = DBEFactory(False)
for k in dbschema.keys():
    v = dbschema[k]
    dbeFactory.register( k, v )
