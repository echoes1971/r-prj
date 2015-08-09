# -*- coding: utf-8 -*-

#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: __init__.py $
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

from collections import OrderedDict
import datetime, os, string, sys, traceback, uuid

class DBLayerException(Exception):
    """Base Exception for the layer"""
    pass

class ForeignKey:
    """Maps a table's foreign key"""
    def __init__(self, colonna_fk="", tabella_riferita="", colonna_riferita=""):
        self.colonna_fk=colonna_fk
        self.tabella_riferita=tabella_riferita
        self.colonna_riferita=colonna_riferita
    def __str__(self):
        return "FK( %s => %s.%s )" % ( self.colonna_fk, self.tabella_riferita, self.colonna_riferita )

class DBEntity:
    """DB Entity
    Superclass for DB persistent entities"""
    def __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'},schema=None ):
        """Parameters:
            names=field names array
            values=field values array
            attrs=dict names->values
            keys=table's key field(s)
        """
        if attrs is not None:
            for k in attrs.keys():
                setattr( self , k, attrs[k] )
        elif names and values:
            self.__dict__ = {}
            for i in range(0,len(names)):
                if isDateTime(values[i]):
                    setattr( self, names[i] , string2datetime(values[ i ]) )
                else:
                    setattr( self, names[i] , values[ i ] )
        self._tablename = tablename
        self._keys = keys
        self._columns = {}
        self._schema = schema
        self._indexes = []
    def __str__(self):
        nomi = self.getNames()
        myattr={}
        for n in nomi:
            v = getattr(self, n)
            if v: myattr[n]=v
        ret = "%s( %s )" % ( self.getTypeName(), myattr )
        return ret
    def _formatDateTime(self, datetime):
        tmp = "%s" % datetime
        tmp = tmp.replace('/','-')
        return tmp[0:16]
    def _getTodayString(self):
        today = datetime.datetime.now()
        ret = "%s-%02d-%02d %02d:%02d:%02d" % (
            today.year,today.month,today.day,
            today.hour,today.minute,today.second,
        )
        return ret
    def getColumns(self):
        return self._columns
    def getDefaultEntries(self):
        """Returns a list of dictionaries, each dictionary defines an entry on the table"""
        return []
    def getTypeName(self):
        """Returns printable name of the type"""
        return self.__class__.__name__
    def getSchemaName(self):
        return self._schema
    def getTableName(self):
        return self._tablename
    def getOrderBy(self):
        return []
    def getOrderByString(self):
        return ",".join(self.getOrderBy())
    def getKeys(self):
        """Returns the keys"""
        return self._keys
    def getFK(self):
        """Returns an array with the foreign keys of the table"""
        return []
    def getFKForTable(self, tablename):
        """IF exists, returns an array with the FKs for the given table"""
        fks=self.getFK()
        ret=[]
        for f in fks:
            if f.tabella_riferita==tablename:
                ret.append(f)
        return ret
    def getFKDefinition(self,fk_column_name):
        """Returns all the FKs for the given table' column"""
        fks=self.getFK()
        ret=[]
        for f in fks:
            if f.colonna_fk==fk_column_name:
                ret.append(f)
        return ret
    def readFKFrom(self, dbe):
        """Reads the content of the referenced columns in the referenced table
        mapped in the given dbe"""
        fks = self.getFKForTable( dbe.getTableName() )
        for f in fks:
            v = dbe.getValue( f.colonna_riferita )
            if v:
                setattr( self, f.colonna_fk, v )
    def writeFKTo(self, masterdbe):
        """Writes the content of the referenced columns in the referenced table
        mapped in the given dbe"""
        fks = self.getFKForTable( masterdbe.getTableName() )
        for f in fks:
            v = self.getValue( f.colonna_fk )
            if v:
                masterdbe.setValue( f.colonna_riferita, v )
        return masterdbe
    def isPrimaryKey(self,field_name):
        if field_name in self.getKeys().keys():
            return True
        return False
    def isFK(self, field_name, tabella_riferita=None):
        """Tells if the given field is fk towards the given table"""
        fks = self.getFK()
        tmp = [ f.colonna_fk for f in fks if tabella_riferita is None or tabella_riferita==f.tabella_riferita ]
        return field_name in tmp
    def getNames(self):
        """Per entita' specializzate (ie mapping di una tabella) fare l'overload
        di questo metodo facendogli ritornare i nomi delle colonne."""
        ret=[]
        for d in self.__dict__.keys():
            if not d.startswith('_') and not d.startswith('get'): ret.append( d )
        return ret
    def getValue( self, chiave ):
        if not chiave.startswith("_") and chiave in self.__dict__:
            return getattr( self, chiave )
        else:
            return None
    def setValue( self, chiave, valore ):
        try:
            self.__dict__[chiave]=valore
        except Exception,e:
            if not chiave.startswith("_"):
                setattr( self, chiave, valore )
    def getValuesDictionary(self):
        """Returns a name:value dict"""
        ret = {}
        mynames = self.getNames()
        for m in mynames:
            v = self.getValue(m)
            if v:
                ret[m]=v
        return ret
    def setValuesDictionary(self,mydict):
        for k in mydict.keys():
            self.setValue(k, mydict[k])
    def cleanKeyFields( self ):
        """Resets all the key fields DBE
        Utility method for the <b>copy</b> method of DBMgr"""
        chiavi = self.getKeys()
        for c in chiavi:
            if self.__dict__.has_key( c ):
                self.__dict__.pop( c, None )
    def isNew(self):
        """True <=> all key fields are empty"""
        keys = self.getKeys()
        for c in keys:
            if not self.getValue(c) is None:
                if type(self.getValue(c))==str and self.getValue(c)>u'':
                    return False
                elif type(self.getValue(c))==unicode and self.getValue(c)>'':
                    return False
                elif type(self.getValue(c))==int or type(self.getValue(c))==float\
                        and self.getValue(c)!=0:
                    return False
                elif type(self.getValue(c))==bool:
                    return False
        return True
    def hasSameKeysOf(self, another_dbe):
        """Checks whether the keys of the given dbe and the current dbe have same values:
        no matter of tables"""
        ret = True
        keys = self.getKeys()
        for c in keys:
            if self.getValue(c) <> another_dbe.getValue(c):
                ret = False
                break
        return ret
    def uuid2hex(self,s):
        if s is None or len(s)==0:
            return s
        str_len = len(s)
        if str_len<4: return s
        if s[:4]=='uuid': return s
        ret = "uuid"
        for i in range(len(s)):
            ret += hex( ord( s[i] ) ).replace('0x','')
        return ret
    uuid2hex = classmethod(uuid2hex)
    def hex2uuid(self,a_str):
        if a_str[:4]!="uuid": return a_str
        s=a_str[4:]
        str_len = len(s)
        ret = ""
        for i in range(str_len/2):
            ret += chr(eval( "0x%s" % s[i*2:(i+1)*2] ))
        return ret
    hex2uuid = classmethod(hex2uuid)
    def validate(self):
        """Validate the DBE and applies custom logics"""
        pass
    def _before_insert(self,dbmgr=None):
        """Action to be taken before insert"""
        pass
    def _after_insert(self,dbmgr=None):
        """Action to be taken after insert"""
        pass
    def _before_update(self,dbmgr=None):
        """Action to be taken before update"""
        pass
    def _after_update(self,dbmgr=None):
        """Action to be taken after update"""
        pass
    def _before_delete(self,dbmgr=None):
        """Action to be taken before delete"""
        pass
    def _after_delete(self,dbmgr=None):
        """Action to be taken after delete"""
        pass
    def _before_copy(self,dbmgr=None):
        """Action to be taken before copy"""
        pass
    def _after_copy(self,dbmgr=None):
        """Action to be taken after copy"""
        pass

class DBAssociation(DBEntity):
    """Models N-M associations"""
    def __init__(self,tablename=None,names=None,values=None,attrs=None,keys=None,columns=[]):
        DBEntity.__init__(self,tablename,names,values,attrs,keys,columns)
    def getFromTableName(self):
        return self._fk[0].tabella_riferita
    def getToTableName(self):
        return self._fk[1].tabella_riferita

class DBConnectionProvider:
    """Superclass for all the connection providers.
    Subclasses of this may also implement connection pooling,
    not only specific DB connection"""
    def __init__(self, host, db, user, pwd, verbose=0):
        self._host = host
        self._db = db
        self._user = user
        self._pwd = pwd
        self._verbose = verbose
        self._conn = None
        self.customInit()
    def customInit(self):
        """Redefine this for idiosynchratic behaviour"""
        pass
    def getHost(self):
        return self._host
    def getUser(self):
        return self._user
    def getDB(self):
        return self._db
    def getConnection(self):
        return self._conn
    def freeConnection(self, conn):
        pass
    def getDBType(self):
        return "generic"
    def isProxy(self):
        return False
    def dbeType2dbType(self,dbetype):
        """TO OVERRIDE"""
        ret = dbetype
        if dbetype=="uuid" or dbetype==u"uuid":
            ret = "varchar(16)"
        return ret
    def dbType2dbeType(self,dbetype):
        """TO OVERRIDE"""
        ret = dbetype
        if dbetype=="varchar(16)":
            ret = "uuid"
        return ret
    def dbeConstraints2dbConstraints(self,constraints):
        return constraints
    def dbConstraints2dbeConstraints(t,definition):
        """ FIXME should this be in the connection object?
        Translates MySQL constraints into dbeContraints"""
        constraints = []
        if definition['Null']=='NO':
            constraints.append("not null")
        if not definition['Default'] is None:
            apice="'"
            if definition['Type'] in ['int(11)','float']:
                apice=""
            constraints.append( "default %s%s%s" % (apice,definition['Default'],apice) )
        elif definition['Default'] is None and definition['Null']=='YES':
            constraints.append( "default null" )
        return " ".join( constraints )
    dbConstraints2dbeConstraints = classmethod(dbConstraints2dbeConstraints)
    # TODO translate all those PHP functions: start.
    #static function dbColumnDefinition2dbeColumnDefinition($def) {
        #$constraints = DBEntity::dbConstraints2dbeConstraints($def);
        #return "'$col'=>array('".DBEntity::dbType2dbeType($def['Type'])."',"
                #.($constraints>'' ? $constraints : '')
                #."),\n";
    #}
    # TODO translate all those PHP functions: end.
    def getColumnsForTable(self,  tablename):
        """@return dictionary with column definitions, None if the table does not exists"""
        raise DBLayerException("DBConnectionProvider.getColumnsForTable: not implemented.")
    def getLocalFilePath(self):
        """@ret the root file path for file storage"""
        raise DBLayerException("DBConnectionProvider.getLocalFilePath: not implemented.")
    def uploadFile(self,local_filename):
        raise DBLayerException("DBConnectionProvider.uploadFile: not implemented.")
    def downloadFile(self,remote_filename,local_filename):
        """@par remote_filename
        @par local_filename where to download the file
        @ret local_filename if ok, None otherwise"""
        raise DBLayerException("DBConnectionProvider.downloadFile: not implemented.")

class DBMgr:
    def __init__(self, connProvider, verbose=0, schema = ''):
        self._connProvider = connProvider
        self._verbose = verbose
        self.conn = None
        self.lastMessages = None
        self._schema = schema
        self.user = None
        self.user_groups_list = []
        self.dbeFactory = None
        if self._connProvider.isProxy():
            self._connProvider.dbmgr = self

    def _description2names(self, desc):
        """Converts the resultset description (from the db api) in a names array"""
        return [d[0] for d in desc]
    def connect(self):
        """Open connection to the db"""
        self.conn = self._connProvider.getConnection()
    def disconnect(self):
        """Closes connection to the db"""
        self._connProvider.freeConnection( self.conn )
        self.conn = None
    def getConnectionProvider(self):
        return self._connProvider
    def getConnection(self):
        return self.conn
    def setDBEFactory( self, myfactory ):
        self.dbeFactory = myfactory
    def getDBEFactory(self):
        return self.dbeFactory
    def getRegisteredTypes(self):
        """Returns the registered types"""
        ret = {}
        tmp = self.getDBEFactory().getRegisteredTypes()
        for k in tmp.keys():
            if k!='default':
                ret[ k ] = tmp[k]
        if self._connProvider.isProxy():
            if len(ret.keys())>1 or ( len(ret.keys())==1 and ret.keys()[0]!='default' ):
                return ret
            ret =  self._connProvider.getRegisteredTypes(self)
        return ret
    def getClazz(self, typename):
        """Returns the registered type"""
        ret = self.getDBEFactory().getClazz(typename)
        if self._connProvider.isProxy() and ret==DBEntity:
            return self._connProvider.getClazz(self,typename)
        return ret
    def getClazzByTypeName(self, typename):
        """Returns the registered type"""
        ret = self.getDBEFactory().getClazzByTypeName(typename)
        if self._connProvider.isProxy() and ret==DBEntity:
            ret = self._connProvider.getClazzByTypeName(typename)
        return ret
    def initDB(self):
        """Initialize DB"""
        mytypes = self.dbeFactory.getRegisteredTypes()
        for tablename in mytypes.keys():
            if self._verbose: print "DBMgr.initDB: tablename = %s" % ( tablename )
            if tablename=='default':
                continue
            mydbe = mytypes[tablename]()
            # 1. Table definition
            self._createTable(mydbe)
            # 2. Default entries
            defaultEntries = mydbe.getDefaultEntries()
            if len(defaultEntries)==0: continue
            for d in defaultEntries:
                if self._verbose: print "DBMgr.initDB: d = %s" % (d)
                newdbe = mytypes[tablename]( attrs=d )
                try:
                    nuova = DBMgr.insert(self, newdbe)
                    print "DBMgr.initDB: nuova=%s (%s)" % (nuova,  newdbe)
                except Exception, e:
                    print "DBMgr.initDB: Exception = %s" % (e)
                    print "".join(traceback.format_tb(sys.exc_info()[2]))
    def _createTable(self, dbe):
        """Create the table for the given dbe"""
        myquery = self.dbe2sql(dbe)
        if self._verbose: print "DBMgr._createTable: myquery = %s" % ( myquery )
        try:
            cursor = self.getConnection().cursor()
            cursor.execute( myquery )
            numres = cursor.rowcount
            if self._verbose: print "DBMgr._createTable: numres = %s" % numres
            if self._connProvider.getDBType()=="POSTGRESQL":
                cursor.execute("commit")
        except Exception,e:
            if self._verbose:
                print "DBMgr._createTable: Exception = %s" % (e)
                print "".join(traceback.format_tb(sys.exc_info()[2]))
            if self._connProvider.getDBType()=="POSTGRESQL":
                cursor.execute( "rollback" )
    def getColumnsForTable(self, tablename):
        return self.getConnectionProvider().getColumnsForTable(tablename)
    def checkDB(self):
        """Checks whether the db and the dbe defs in the dbe factory are sync'd"""
        mytypes = self.dbeFactory.getRegisteredTypes()
        for tablename in mytypes.keys():
            if tablename=='default':
                continue
            mydbe = mytypes[tablename]()
            # 1. Check table definition TODO
            mytablename = self.buildTableName(mydbe)
            columns = self.getColumnsForTable( mytablename )
            if columns is None:
                # Table does not exists
                if self._verbose: print "DBMgr.checkDB: Table '%s' does not exists." % (mytablename)
                myquery = self.dbe2sql(mydbe)
                try:
                    cursor = self.getConnection().cursor()
                    cursor.execute( myquery )
                    numres = cursor.rowcount
                    print "DBMgr.checkDB: numres = %s" % numres
                    if self._connProvider.getDBType()=="POSTGRESQL":
                        cursor.execute("commit")
                except Exception,e:
                    if self._verbose:
                        print "DBMgr.checkDB: Exception = %s" % (e)
                        print "".join(traceback.format_tb(sys.exc_info()[2]))
            else:
                #print "DBMgr.checkDB: columns=%s" % ( [x for x in columns.keys()] )
                columnsdefinitions = mydbe.getColumns()
                # Columns to add
                delta = [x for x in columnsdefinitions.keys() if not x in columns.keys()]
                for c in delta:
                    myquery = "alter table %s add column %s %s" % ( self._buildTableName(mydbe), c, self.column2sql( columnsdefinitions[c], mydbe.isPrimaryKey(c) ) )
                    try:
                        cursor = self.getConnection().cursor()
                        cursor.execute( myquery )
                        if self._connProvider.getDBType()=="POSTGRESQL":
                            cursor.execute("commit")
                    except Exception,e:
                        if self._verbose or True:
                            print "DBMgr.checkDB: Exception = %s" % (e)
                            print "".join(traceback.format_tb(sys.exc_info()[2]))
                # Columns to drop
                delta = [ x for x in columns.keys() if not x in columnsdefinitions.keys() ]
                for c in delta:
                    myquery = "alter table %s drop column %s %s" % ( self._buildTableName(mydbe), c, self.column2sql( columnsdefinitions[c], mydbe.isPrimaryKey(c) ) )
                    try:
                        cursor = self.getConnection().cursor()
                        cursor.execute( myquery )
                        if self._connProvider.getDBType()=="POSTGRESQL":
                            cursor.execute("commit")
                    except Exception,e:
                        if self._verbose:
                            print "DBMgr.checkDB: Exception = %s" % (e)
                            print "".join(traceback.format_tb(sys.exc_info()[2]))
            # 2. Check default entries
            defaultEntries = mydbe.getDefaultEntries()
            if len(defaultEntries)==0: continue
            for d in defaultEntries:
                if self._verbose: print "DBMgr.checkDB: d = %s" % (d)
                cerca = mytypes[tablename]( attrs=d )
                tmp = DBMgr.search(self, cerca, uselike=False)
                if len(tmp)==0:
                    try:
                        nuova = DBMgr.insert(self, cerca)
                    except Exception,e:
                        if self._verbose:
                            print "DBMgr.checkDB: Exception = %s" % (e)
                            print "".join(traceback.format_tb(sys.exc_info()[2]))
    def removeDB(self):
        """BEWARE: Remove all the tables and all the rows from the DB!!!"""
        mytypes = self.dbeFactory.getRegisteredTypes()
        for tablename in mytypes.keys():
            if tablename=='default':
                continue
            mydbe = mytypes[tablename]()
            myquery = "drop table %s" % ( self._buildTableName(mydbe) )
            if self._verbose: print "DBMgr.removeDB: myquery = %s" % ( myquery )
            try:
                cursor = self.getConnection().cursor()
                cursor.execute( myquery )
                numres = cursor.rowcount
                if self._verbose: print "DBMgr.removeDB: numres = %s" % numres
            except Exception,e:
                if self._verbose: print "DBMgr.removeDB: Exception = %s" % (e)
                if self._connProvider.getDBType()=="POSTGRESQL":
                    cursor.execute( "rollback" )
    def dbe2sql(self,dbe):
        """Creates the 'create table' statement"""
        ret = []
        cols = dbe.getColumns()
        ret.append( "--" )
        ret.append( "-- %s" % ( dbe.getTypeName() ) )
        ret.append( "--" )
        ret.append( "create table %s (" % ( self._buildTableName(dbe) ) )
        for c in cols.keys():
            ret.append( " %s %s," % ( c, self.column2sql( cols[c], dbe.isPrimaryKey(c) ) ) )
        ret.append( " primary key(%s)" % ( ",".join( dbe.getKeys().keys() ) ) )
        ret.append( ");" )
        ret.append("")
        return "\n".join(ret)
    def column2sql(self, mydef,  isPrimaryKey):
            mytype = self._connProvider.dbeType2dbType( mydef[0] )
            constraints = ""
            if len(mydef)>1:
                constraints = mydef[1]
            not_null=""
            if isPrimaryKey:
                not_null="not null"
            if len(constraints)==0:
                constraints = not_null
            constraints = self._connProvider.dbeConstraints2dbConstraints(constraints)
            return "%s %s" % (mytype,constraints)
    def _escapeString(self,s):
        return "'%s'" % s.replace("\\","\\\\").replace("\'","''")
    def _formatDateTime(self, datetime):
        tmp = "%s" % datetime
        tmp = tmp.replace('/','-')
        # 2013-04-09: start.
        if len(tmp)==8:
            return "0000-00-00 %s" % (tmp)
        # 2013-04-09: end.
        return tmp[0:16]
    def getTodayString(self):
        today = datetime.datetime.now()
        ret = "%s-%02d-%02d %02d:%02d:%02d" % (
            today.year,today.month,today.day,
            today.hour,today.minute,today.second,
        )
        return ret
    def buildTableName(self,dbe):
        return self._buildTableName(dbe)
    def _buildTableName(self,dbe):
        tablename=''
        __schema = dbe.getSchemaName()
        if __schema is None or __schema=="":
            __schema = self._schema
        if __schema>"":
            tablename = "%s_%s" % ( __schema, dbe.getTableName() )
        return tablename
    def _buildKeysCondition(self, dbe):
        ret = []
        chiavi = dbe.getKeys()
        for c in chiavi.keys():
            if chiavi[c]=='number':
                ret.append( "%s=%s" % ( c, getattr(dbe,c) ) )
            else:
                ret.append( "%s='%s'" % ( c, getattr(dbe,c) ) )
            pass
        return string.join(ret, ' AND ')
    def buildKeysCondition(self, dbe):
        return self._buildKeysCondition(dbe)
    def _buildInsertString(self,dbe):
        nomicampi = dbe.getNames()
        tmpnomi=[]
        tmpvalori=[]
        for n in nomicampi:
            v = dbe.getValue(n)
            if v is None: continue
            tmpnomi.append(n)
            if isinstance(v,str) or isinstance( v, unicode ):
                if not isDateTime(v):
                    tmpvalori.append( "%s" % ( self._escapeString( v ) ) )
                else:
                    tmpvalori.append( "'%s'" % ( v ) )
            elif v.__class__.__name__=='DateTime' or isinstance(v,datetime.datetime):
                tmpvalori.append( "'%s'" % ( self._formatDateTime(v) ) )
            elif isinstance(v,bool):
                if v:
                    tmpvalori.append('1')
                else:
                    tmpvalori.append('0')
            else:
                    tmpvalori.append( "%s" % (v) )
        query = "insert into %s (%s) values (%s)" % ( self._buildTableName(dbe), string.join(tmpnomi,","), string.join(tmpvalori,",") )
        return query
    def buildInsertString(self,dbe):
        return self._buildInsertString(dbe)
    def _buildUpdateString(self,dbe):
        nomicampi = dbe.getNames()
        chiavi = dbe.getKeys()
        setstring=[]
        for n in nomicampi:
            v = dbe.getValue(n)
            if not (n in chiavi):
                if v is None:
                    continue
                elif isinstance(v,str) or isinstance( v, unicode ):
                    if not isDateTime(v):
                        setstring.append( "%s=%s" % ( n, self._escapeString( v ) ) )
                    else:
                        setstring.append( "%s='%s'" % ( n, v ) )
                elif isinstance(v,bool):
                    if v:
                        setstring.append( "%s=1" % (n) )
                    else:
                        setstring.append( "%s=0" % (n) )
                elif v.__class__.__name__=='DateTime' or isinstance(v,datetime.datetime):
                    setstring.append( "%s='%s'" % ( n, self._formatDateTime(v) ) )
                elif v.__class__.__name__=='Time' or isinstance(v,datetime.time):
                    setstring.append( "%s='%s'" % ( n, self._formatDateTime(v) ) )
                else:
                    setstring.append( "%s=%s" % ( n, v ) )
                # 2012.04.04: start.
                # 2012.04.04: not set in the same method of PHP
                #else:
                    #setstring.append( "%s=NULL" % (n) )
                # 2012.04.04: end.
        query = "update %s set %s where %s" % ( self._buildTableName(dbe),\
                                                 string.join(setstring,", "),\
                                                 self._buildKeysCondition(dbe) )
        return query
    def _buildWhereCondition( self, dbe, uselike=1,case_sensitive=True):
        nomicampi = dbe.getNames()
        clausole=[]
        for n in nomicampi:
            is_from = n[0:5]=='from_'
            is_to = n[0:3]=='to_'
            v = dbe.getValue(n)
            if v is None:
                if self._connProvider.getDBType()=="POSTGRESQL":
                    clausole.append( "%s is null" % (n) )
            elif v=='null':
                clausole.append( "%s is null" % (n) )
            elif isinstance( v, str ) or isinstance( v, unicode ):
                if is_from:
                    if len(v)>0: clausole.append( "%s>='%s'" % (n[5:],v) )
                elif is_to:
                    if len(v)>0: clausole.append( "%s<='%s'" % (n[3:],v) )
                elif v.find('0000-00-00 00:00')>=0:
                    clausole.append( "(%s='%s' or %s is null)" % (n,v,n) )
                elif uselike:
                    if len(v)>0:
                        myv = "%%%s%%" % v
                        if v.find('*')>=0:
                            myv = "%s" % ( v.replace("*","%%") )
                        if case_sensitive:
                            clausole.append( "%s like '%s'" % (n,myv) )
                        else:
                            clausole.append( "lower(%s) like '%s'" % (n,myv.lower()) )
                else:
                    clausole.append( "%s='%s'" % (n,v) )
            else:
                if is_from:
                    if not v is None: clausole.append( "%s>=%s" % (n[5:],v) )
                elif is_to:
                    if not v is None: clausole.append( "%s<=%s" % (n[3:],v) )
                else:
                    clausole.append( "%s=%s" % (n,v) )
        return clausole
    def _buildSelectString(self, dbe, uselike=1,case_sensitive=True):
        clausole = self._buildWhereCondition( dbe, uselike, case_sensitive )
        if len(clausole)>0:
            query = "select * from %s where %s" % ( self._buildTableName(dbe), string.join(clausole," and ") )
        else:
            query = "select * from %s" % ( self._buildTableName(dbe) )
        return query
    def _buildDeleteString(self,dbe):
        query = "delete from %s where %s" % ( self._buildTableName(dbe), self._buildKeysCondition(dbe) )
        return query
    def _before_insert(self, dbe):
        """Action to be taken before insert"""
        return dbe
    def _after_insert(self, dbe):
        """Action to be taken after insert"""
        return dbe
    def _before_update(self, dbe):
        """Action to be taken before update"""
        return dbe
    def _after_update(self, dbe):
        """Action to be taken after update"""
        return dbe
    def _before_delete(self, dbe):
        """Action to be taken before delete"""
        return dbe
    def _after_delete(self, dbe):
        """Action to be taken after delete"""
        return dbe
    def _before_copy(self, dbe):
        """Action to be taken before copy"""
        return dbe
    def _after_copy(self, dbe):
        """Action to be taken after copy"""
        return dbe
    def insert(self, dbe, cleanKeysIfError=True):
        """Insert the new dbe into the db"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.insert(dbe)
        # Before Insert
        dbe._before_insert(self)
        dbe = self._before_insert(dbe)
        # Insert
        query = self._buildInsertString(dbe)
        if self._verbose: print "DBMgr.insert: query=%s" % ( query )
        try:
            cursor = self.getConnection().cursor()
            cursor.execute( query )
            self.getConnection().commit()
            # After Insert
            dbe = self._after_insert(dbe)
            dbe._after_insert(self)
        except Exception, e:
            if self._verbose:
                print "DBMgr.insert: Probblemi: %s" % (e)
                print "DBMgr.insert: self._connProvider = %s - %s" % ( self._connProvider._db, self._connProvider )
                print "DBMgr.insert: query=%s" % (query)
                print "".join(traceback.format_tb(sys.exc_info()[2]))
            try:
                ris = self.getConnection().execute( query )
                self.getConnection().commit()
                # After Insert
                dbe = self._after_insert(dbe)
                dbe._after_insert(self)
            except Exception, e1:
                if cleanKeysIfError: dbe.cleanKeyFields()
                if self._verbose:
                    print dir(self.getConnection())
                    print "DBMgr.insert: Probblemi: %s" % (e1)
                    print "DBMgr.insert: self._connProvider = %s - %s" % ( self._connProvider._db, self._connProvider )
                    print "DBMgr.insert: query=%s" % (query)
                    print "".join(traceback.format_tb(sys.exc_info()[2]))
                raise DBLayerException("DBMgr.insert: unable to execute - %s.Error cause: %s" % ( query, e ))
        return dbe
    def update(self,dbe):
        """Update the given dbe"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.update(dbe)
        # Before Update
        dbe._before_update(self)
        dbe = self._before_update(dbe)
        # Update
        query = self._buildUpdateString(dbe)
        if self._verbose: print "DBMgr.update: query=%s" % ( query )
        try:
            cursor = self.getConnection().cursor()
            cursor.execute( query )
            self.getConnection().commit()
            # After Update
            dbe = self._after_update(dbe)
            dbe._after_update(self)
        except Exception, e:
            if self._verbose:
                print "DBMgr.update: Probblemi: %s" % (e1)
                print "DBMgr.update: query=%s" % (query)
                print "".join(traceback.format_tb(sys.exc_info()[2]))
            try:
                ris = self.getConnection().execute( query )
                self.getConnection().commit()
                # After Update
                dbe = self._after_update(dbe)
                dbe._after_update(self)
            except Exception, e1:
                dbe = None
                if self._verbose:
                    print "DBMgr.update: Probblemi: %s" % (e1)
                    #print "DBMgr.update: self._connProvider = %s - %s" % ( self._connProvider._db, self._connProvider )
                    print "DBMgr.update: query=%s" % (query)
                    print "".join(traceback.format_tb(sys.exc_info()[2]))
                raise DBLayerException("DBMgr.update: unable to execute\n\t%s.Error cause: %s" % ( query, e1 ))
        return dbe
    def select(self,tablename,searchString):
        """Returns a list of DBE according to the searchString"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.select(tablename,searchString)
        cursor = self.getConnection().cursor()
        ret = []
        try:
            cursor.execute( searchString )
            numres = cursor.rowcount
            names = None
            try:
                names = self._description2names(cursor.description)
                if self._connProvider.getDBType()=="MYSQL":
                    if self._verbose: print "DBMgr.select: found %s rows." % ( numres )
                    for i in range( 0, numres ):
                        valori = cursor.fetchone()
                        ret.append( self.getDBEFactory()( tablename, names=names, values=valori ) )
                else:
                    listavalori = cursor.fetchall()
                    numres = len(listavalori)
                    if self._verbose: print "DBMgr.select: found %s rows." % ( numres )
                    for i in range( numres ):
                        ret.append( self.getDBEFactory()( tablename, names=names, values=listavalori[i] ) )
            except TypeError,e:
                if self._verbose:
                    print "DBMgr.select: ECCEZIONE=%s (%s)" % (e,searchString)
                    print "".join(traceback.format_tb(sys.exc_info()[2]))
        except Exception,e:
            if self._connProvider.getDBType()=="POSTGRESQL":
                cursor.execute( "rollback" )
            if self._verbose:
                print "DBMgr.select: ECCEZIONE=%s (%s)" % (e,searchString)
                print "".join(traceback.format_tb(sys.exc_info()[2]))
            raise e
        cursor.close()
        return ret
    def delete(self,dbe):
        """Delete the given dbe"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.delete(dbe)
        # Before Delete
        dbe._before_delete(self)
        dbe = self._before_delete(dbe)
        # Delete
        query = "delete from %s where %s" % ( self._buildTableName(dbe), self._buildKeysCondition(dbe) )
        if self._verbose: print "Delete: query=%s" % query
        try:
            ris = self.getConnection().execute( query )
            self.getConnection().commit()
            # After Delete
            dbe = self._after_delete(dbe)
            dbe._after_delete(self)
        except Exception, e:
            if self._verbose:
                print "DBMgr.delete: ECCEZIONE=%s (%s)" % (e,searchString)
                print "".join(traceback.format_tb(sys.exc_info()[2]))
            try:
                ris = self.getConnection().query( query )
                self.getConnection().commit()
                # After Delete
                dbe = self._after_delete(dbe)
                dbe._after_delete(self)
            except Exception, e:
                dbe = None
                raise DBLayerException("DBMgr.delete: unable to execute\n\t%s.Error cause: %s" % ( query, e ))
        return dbe
    def search(self, dbe, uselike=1, orderby=None, ignore_deleted=True, full_object=True):
        """Provides Search methods"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.search(dbe, uselike, orderby, ignore_deleted, full_object)
        query = self._buildSelectString(dbe,uselike)
        if orderby is not None: query += " ORDER BY %s" % (orderby)
        if self._verbose: print "DBMgr.search: query=%s" % query
        return self.select( dbe.getTableName(), query )
    def getNextId(self, dbe):
        nomeTabella = "%s_seq_id" % ( self._schema )
        tmp = self.select(nomeTabella, "select id from %s where name=''" % ( nomeTabella ) )
        myid = 1
        if len(tmp)>0:
            myid = tmp[0].getValue('id') + 1
            self.select(nomeTabella, "insert into %s (id,name) values (%s,'')" % (nomeTabella,myid) )
        else:
            self.select(nomeTabella, "update %s set id=%s where name=''" % (nomeTabella,myid) )
        return myid
    def getNextUuid(self, dbe, length=16):
        return ( ("%s" % uuid.uuid4()).replace('-','') )[:16]
    def copy(self, dbe):
        """Copy the given DBE"""
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.copy(dbe)
        # 1. Read the whole DBE from db
        tmp = self.search( dbe, uselike=0 )
        if len(tmp)!=1: raise DBLayerException("Unable to copy the given DBE; found %s instances." % ( len(tmp) ) )
        mydbe = tmp[0]
        # 2. Resetting keys
        mydbe.cleanKeyFields()
        dbe = mydbe
        # 3. Insert a new entry
        dbe._before_copy( self )
        dbe = self._before_copy( dbe )
        dbe = self.insert( dbe )
        dbe = self._after_copy( dbe )
        dbe._after_copy( self )
        return dbe
    def searchByKeys(self,dbe):
        """Returns a list of dbe using the keys of the dbe in input as a search key
        Typically will return a list with zero or one element"""
        chiavi = dbe.getKeys()
        # 2012.06.04: start.
        # 2012.06.04: this works better with NoSQL dbs
        cerca = self.getClazzByTypeName(dbe.getTypeName())()
        for k in chiavi.keys():
            cerca.setValue(k,  dbe.getValue(k))
        cerca.setValuesDictionary(chiavi)
        ret = self.search(cerca, uselike=0)
        #query = "select * from %s where %s" % (self._buildTableName(dbe), self._buildKeysCondition(dbe))
        #ret = self.select( dbe.getTableName(), query )
        # 2012.06.04: end.
        return ret
    def exists(self,dbe):
        """Tells if another record exists in the database with the same key values
        as the dbe in input"""
        if dbe.isNew():
            return False
        tmp = self.searchByKeys(dbe)
        return len(tmp)>0
    def isDateTime(self,s):
        return isDateTime(s)
    def datetime2string(self, dt):
        tmpdata = "%s" % ( dt )
        tmpdata = "%s-%s-%s 00:00" % (tmpdata[:4],tmpdata[5:7],tmpdata[8:10])
        return tmpdata.replace('/','-')
    def string2datetime(self, dt):
        return DateTime(dt)
    def millis2datetime(self, millis):
        return DateTime(millis)
    def convertDate(self, dt):
        tmpdata = "%s" % ( dt )
        tmpdata = "%s-%s-%s" % (tmpdata[8:10],tmpdata[5:7],tmpdata[:4])
        return tmpdata.replace('/','-')
    def getLastMessages(self):
        return self.lastMessages
    def ping(self):
        if self._connProvider.isProxy():
            return self._connProvider.ping()
        return "pong"
    def _loadUserGroups(self):
        if self.user is None:
            return
        cerca = self.getClazzByTypeName('DBEUserGroup')()
        cerca.readFKFrom(self.user)
        lista=self.search( cerca, uselike=0 )
        lista_gruppi=[]
        for g in lista:
            lista_gruppi.append( "%s"%g.getValue('group_id') )
        if not "%s"%self.user.getValue('group_id') in lista_gruppi:
            lista_gruppi.append( "%s"%self.user.getValue('group_id') )
        self.setUserGroupsList(lista_gruppi)
    def login(self,user,pwd):
        if self._connProvider.isProxy():
            self.user = self._connProvider.login(user,pwd)
            if self.user is None:
                return None
            self._loadUserGroups()
            return self.user
        if pwd is None or len(pwd)==0 or user is None or len(user)==0:
            raise DBLayerException("Missing username or password")
        cerca = self.getClazzByTypeName('DBEUser')(attrs={'login':user,'pwd':pwd})
        ret = []
        try:
            ret = self.search(cerca,uselike=False)
        except Exception,e:
            try:
                self.initDB()
                newuser = self.getClazzByTypeName('DBEUser')(attrs={'login':user,'pwd':pwd})
                newuser = self.insert(newuser)
                searchGroup = self.getClazzByTypeName('DBEGroup')(attrs={'name':user})
                newgroup = self.search(searchGroup,uselike=False)[0]
                if self._verbose: print "DBMgr.login: newuser=%s" % ( newuser )
                newfolder = self.getClazzByTypeName('DBEFolder')(attrs={\
                    'owner':newuser.getValue('id'),\
                    'group_id':newgroup.getValue('id'),\
                    'creator':newuser.getValue('id'),\
                    'last_modify':newuser.getValue('id'),\
                    'name':user})
                newfolder = self.insert(newfolder)
                if self._verbose: print "DBMgr.login: newfolder=%s" % ( newfolder )
                ret = self.search(cerca,uselike=False)
            except Exception,e1:
                if self._verbose: print "DBMgr.login: ECCEZIONE=%s" % ( e )
                if self._verbose: print "".join(traceback.format_tb(sys.exc_info()[2]))
        if len(ret)==1:
            self.user = ret[0]
            self._loadUserGroups()
        return self.user
    def relogin(self):
        myuser = self.getDBEUser()
        if myuser is None:
            return False
        return self.login( myuser.getValue('login'), myuser.getValue('pwd') )
    def getServerIDString(self):
        """Returns a string uniquely identifying the user and the connection"""
        d = self.getConnectionProvider().getDBType()
        u = "nobody"
        if not self.getDBEUser() is None:
            u = self.getDBEUser().getValue('login')
        h = "%s" % self.getConnectionProvider().getHost()
        if d=="SQLite":
            h = ( "%s" % self.getConnectionProvider().getDB() ).split( os.path.sep )[-1]
        if h.find("://")>0:
            _url = h.split("://")
            if len(_url)>1:
                h = "%s:%s" % ( _url[0],_url[1].split("/")[0] )
        return "%s@%s" % (u,h)
    def getDBEUser(self):
        return self.user
    def setDBEUser(self,dbeuser):
        self.user = dbeuser
    def isLoggedIn(self):
        remoteuser = self.getDBEUser()
        if self._connProvider.isProxy():
            remoteuser = self._connProvider.getLoggedUser()
        return not remoteuser is None
    def getUserGroupsList(self):
        return self.user_groups_list
    def setUserGroupsList(self,user_groups_list):
        self.user_groups_list = user_groups_list
    def hasGroup(self,group_id):
        """ The logged user has the requested group_id ? """
        return "%s"%group_id in self.user_groups_list
    def addGroup(self,group_id):
        if not group_id in self.user_groups_list:
            self.user_groups_list.append(group_id)
    def getFormSchema(self,language='python',aClassname=''):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.getFormSchema(language,aClassname)
        raise DBLayerException("DBMgr.getFormSchema: TODO")
    def getDBSchema(self,language='python',aClassname=''):
        if self._connProvider.isProxy():
            # 2012.04.02: start.
            if not self.isLoggedIn() and not self.relogin():
                raise DBLayerException("Proxy connection down!")
            # 2012.04.02: end.
            return self._connProvider.getDBSchema(language,aClassname)
        raise DBLayerException("DBMgr.getDBSchema: TODO")
    def uploadFile(self,local_filename):
        return self._connProvider.uploadFile(local_filename)
    def downloadFile(self,remote_filename,local_filename,view_thumbnail=False):
        return self._connProvider.downloadFile(remote_filename,local_filename,view_thumbnail)

class DBEFactory:
    """Returns the correct class for the given tablename"""
    def __init__(self, verbose = False):
        self._cache = OrderedDict() #{}
        self._cache_by_typename = OrderedDict() #{}
        self.register('default', DBEntity)
        self.verbose=verbose
    def register(self, tablename, clazz):
        self._cache[ tablename ]=clazz
        self._cache_by_typename[ clazz().getTypeName() ]=clazz
    def getRegisteredTypes(self):
        return self._cache
    def getClazz(self, tablename):
        ret = None
        if self._cache.has_key( tablename ):
            ret = self._cache[tablename]
        else:
            ret=self._cache['default']
        return ret
    def getClazzByTypeName(self, typename, case_sensitive=True):
        if self.verbose: print "DBEFactory.getClazzByTypeName: typename=%s" % ( typename )
        ret = self._cache['default']
        if case_sensitive:
            if self._cache_by_typename.has_key(typename):
                ret = self._cache_by_typename[typename]
        else:
            chiavi = [ k.lower() for k in self._cache_by_typename.keys() ]
            typename_lower = typename.lower()
            if typename_lower in chiavi:
                for k in self._cache_by_typename.keys():
                    if k.lower()==typename_lower:
                        ret = self._cache_by_typename[k]
                        break
        if self.verbose: print "DBEFactory.getClazzByTypeName: ret=%s" % ( ret )
        return ret
    def __call__(self, tablename, names=None, values=None, *args):
        """Some kind of magic..."""
        if self.verbose:
            print "DBEFactory.__call__: tablename=%s" % ( tablename )
        myclazz = self.getClazz(tablename)
        ret = myclazz( tablename, names, values )
        if self.verbose:
            print "DBEFactory.__call__: Classe: %s\tIstanza: %s" % ( myclazz, ret )
        return ret

def isDateTime(s):
    # 2005-04-12 00:00
    # 2012/1/21 17:51:56
    try:
        if s[4]=='-' and s[7]=='-' and ( s[13]==':' or len(s)==10 ):
            return True
        else:
            data,ora = s.split(" ")
            if ( len( data.split("/") )==3 or len( data.split("-") )==3
                    ) and (
                    len(ora.split(":"))>=2 or len(ora.split("."))>=2
                    ):
                return True
            return False
    except Exception,e:
        return False
def string2datetime(s):
    if len(s)>=15: #len(s)>=16:
        data,ora = s.split(" ")
        _y,_m,_d = 0,0,0
        if data.find("/")>=0:
            _y,_m,_d = data.split("/")
        elif data.find("-")>=0:
            _y,_m,_d = data.split("-")
        if ora.find(":")>=0:
            _h,_M = ( ora.split(":") )[:2]
        elif ora.find(".")>=0:
            _h,_M = ( ora.split(".") )[:2]
        try:
            _y,_m,_d,_h,_M = int(_y),int(_m),int(_d),int(_h),int(_M)
        except Exception, e:
            #print "s:", s
            return None
        if _y==0 and _m==0 and _d==0 and _h==0 and _M==0:
            return None
        if _y==0 and _m==0 and _d==0:
            return datetime.time(_h,_M)
        try:
            return datetime.datetime(_y,_m,_d,_h,_M)
        except Exception,e:
            raise DBLayerException("string2datetime(%s): exception=%s" % (s,e))
    else:
        if s.find("/")>=0:
            _y,_m,_d = s.split("/")
        elif s.find("-")>=0:
            _y,_m,_d = s.split("-")
        _y,_m,_d = int(_y),int(_m),int(_d)
        if _y==0 and _m==0 and _d==0:
            return None
        return datetime.datetime(_y,_m,_d)

def createConnection(aUrl,aVerbose=False):
    myconn = None
    myurl = aUrl
    if myurl.startswith("http"):
        if myurl.endswith("/xmlrpc_server.php"):
            pass # OK
        elif myurl.endswith("/"):
            myurl = "%sxmlrpc_server.php" % ( myurl )
        else:
            myurl = "%s/xmlrpc_server.php" % ( myurl )
        from rprj.dblayer.xmlrpc import XmlrpcConnectionProvider
        myconn = XmlrpcConnectionProvider(myurl,'','','',aVerbose) #1)
    elif myurl.startswith("json"):
        # Quick patch: if there is a port, then don't add the default page
        if not myurl.find(":"):
            if myurl.endswith("/jsonserver.php"):
                pass # OK
            elif myurl.endswith("/"):
                myurl = "%sjsonserver.php" % ( myurl )
            else:
                myurl = "%s/jsonserver.php" % ( myurl )
        from rprj.dblayer.jsonconn import JsonConnectionProvider
        myconn = JsonConnectionProvider(myurl,'','','',aVerbose) #1)
    elif myurl.startswith("mysql"):
        xx,host, db, user, pwd = myurl.split(":")
        from rprj.dblayer.mydb import MYConnectionProvider
        myconn = MYConnectionProvider(host, db, user, pwd,aVerbose)
    elif myurl.startswith("postgresql"):
        xx,host, db, user, pwd = myurl.split(":")
        from rprj.dblayer.pgdb import PGConnectionProvider
        myconn = PGConnectionProvider(host, db, user, pwd,aVerbose)
    elif myurl.startswith("sqlite"):
        tmp_path=myurl.split(":")
        path = "".join(tmp_path[1:])
        if len(tmp_path)>2:
            tmp_path[1] = "%s:"%tmp_path[1]
            path = "".join(tmp_path[1:])
        if path.startswith("///"):
            path = path[2:]
        from rprj.dblayer.sqlitedb import SQLiteConnectionProvider
        myconn = SQLiteConnectionProvider('',path,'','',aVerbose)
    return myconn
