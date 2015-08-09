# -*- coding: utf-8 -*-

from ConfigParser import ConfigParser
import datetime, httplib, json, os, sys, traceback,  types
from base64 import b64decode,b64encode

DEBUG_JSONLIB=False

def dbeToRaw(dbe):
    ret={}
    for n in dbe.getNames():
        tmpvalue = dbe.getValue(n)
        if isinstance( tmpvalue, datetime.datetime ) or isinstance( tmpvalue, datetime.date ) or isinstance( tmpvalue, datetime.time ): # or isinstance(tmpvalue,Binary):
            ret[n] = "%s" % tmpvalue
        else:
            ret[n] = tmpvalue
    ret['_typename'] = dbe.getTypeName()
    return ret
    #return [ dbe.getTypeName(), ret ]

def dataToRaw(d):
    if type(d)==types.InstanceType:
        return dbeToRaw(d)
    elif type(d)==list or type(d)==tuple:
        return [dataToRaw(x) for x in d]
    elif type(d)==dict:
        ret = {}
        for k, v in d.items():
            ret[k] = dataToRaw(v)
        return ret
    #elif type(d)==int or type(d)==float:
    #    return "%s"%d
    return d

def rawToData(dbmgr, d):
    print d
    if type(d)==list and len(d)==2 and type(d[0]) in [str,  unicode] and type(d[1])==dict:
        print d[1]
        return rawToDbe(dbmgr, d)
    return d
    #FINIRE: conversione da liste e dizionari a dbentity
#DA QUI

def rawToDbe(dbmgr, d):
    typename = d[0]
    rawdbe = d[1]
    nomi = [ n for n in rawdbe.keys() ]
    valori = [ n for n in rawdbe.values() ]
    valori2 = []
    for v in valori:
        #if isinstance(v, Binary): v = v.data
        if dbmgr.isDateTime(v): #rra.dblayer.isDateTime(v):
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
        elif (type(v)==str or type(v)==unicode) and v.startswith("base64:"):
            valori2.append( b64decode( v[len("base64:"):] ) )
        else:
            valori2.append(v)
    #if isinstance(typename, Binary): typename = typename.data
    myclazz = dbmgr.getClazzByTypeName(typename)
    #print dbmgr.getRegisteredTypes()
    #print typename, "=>", myclazz
    return myclazz( myclazz().getTableName(), names=nomi, values=valori2 )

class JSONRemoteMethod:
    def __init__(self,methodName,transport):
        self._methodName = methodName
        self._transport = transport
    def __call__(self, *args):
        #print "args:",args,len(args)
        #print "self._methodName:",self._methodName
        req = { 'method':self._methodName, 'params':args }
        return self._transport.sendRequest(req)
    def __getattr__(self,name):
        if self.__dict__.has_key(name) or name.startswith("_"):
            #print "CE L'HO",name
            return self.__dict__[name]
        self._methodName = "%s.%s" %(self._methodName,name)
        return self

class Binary:
    def __init__(self,payload):
        self._payload = payload
    def __str__(self):
        if (type(self._payload)==str or type(self._payload)==unicode) and self._payload.startswith("base64:"):
            return b64decode( self._payload[len("base64:"):] )
        return "base64:%s" % b64encode(self._payload)

class JSONClient:
    def __init__(self, host, transport=None, SESSION_ID_STRING='PHPSESSID'):
        self._protocol , tmp = host.split("://")
        tmp = tmp.split("/")
        self._host = tmp[0]
        self._port = 80
        if self._host.find(":")>=0:
            tmp2 = self._host.split(":")
            self._host = tmp2[-2]
            try:
                # TODO do this also for the xmlrpc connection :-)))
                self._port = int(tmp2[-1])
            except Exception,e:
                self._port = tmp2[-1]
        self._uri = "/%s" % "/".join(tmp[1:])
        if DEBUG_JSONLIB:
            print "JSONClient.__init__: self._protocol:",self._protocol
            print "JSONClient.__init__: self._host:",self._host
            print "JSONClient.__init__: self._port:",self._port
            print "JSONClient.__init__: self._uri:",self._uri
        self._transport=transport
        self._reqMethod="POST"
        self.mycookies=None
        self.mysessid=None
        self.SESSION_ID_STRING = SESSION_ID_STRING
        self._reqId = 0
    def __getattr__(self,name):
        if self.__dict__.has_key(name) or name.startswith("_"):
            #print "CE L'HO",name
            return self.__dict__[name]
        return JSONRemoteMethod(name,self)
    def sendRequest(self,req):
        req['id'] = self._reqId
        self._reqId += 1
        json_req = json.dumps(req)
        conn = httplib.HTTPConnection(self._host, self._port)
        req_body=json_req
        req_headers={}
        if not self.mysessid is None:
            req_headers["Cookie"] = "%s=%s" % (self.SESSION_ID_STRING,self.mysessid)
        conn.request(self._reqMethod, self._uri, req_body, req_headers)
        r1 = conn.getresponse()
        #print r1.status, r1.reason
        data1 = r1.read()
        #print dir(r1)
        headers = r1.getheaders()
        #print dir(headers)
        for h in headers:
            #print "h:",h
            nome,valore = h
            if nome=='set-cookie':
                self.mycookies = self.parseCookies(valore)
                if self.mysessid is None:
                    if self.mycookies.has_key(self.SESSION_ID_STRING):
                        self.mysessid = self.mycookies[self.SESSION_ID_STRING]
        conn.close()
        if DEBUG_JSONLIB: print "JSONClient.sendRequest: data1=%s"%data1
        try:
            return json.loads(data1)
        except Exception,e:
            print "JSONClient.sendRequest: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % ( e )
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            print "JSONClient.sendRequest: ECCEZIONE ############# Data"
            print data1
            print "JSONClient.sendRequest: ECCEZIONE ############# Fine."
    def test(self):
        conn = httplib.HTTPConnection(self._host)
        #print "conn:",conn
        req_body=""
        req_headers={}
        if not self.mysessid is None:
            req_headers["Cookie"] = "%s=%s" % (self.SESSION_ID_STRING,self.mysessid)
        conn.request("GET", self._uri, req_body, req_headers)
        r1 = conn.getresponse()
        #print r1.status, r1.reason
        data1 = r1.read()
        #print dir(r1)
        headers = r1.getheaders()
        #print dir(headers)
        for h in headers:
            #print "h:",h
            nome,valore = h
            if nome=='set-cookie':
                self.mycookies = self.parseCookies(valore)
                if self.mysessid is None:
                    if self.mycookies.has_key(self.SESSION_ID_STRING):
                        self.mysessid = self.mycookies[self.SESSION_ID_STRING]
        conn.close()
        print "self.mycookies:",self.mycookies
        return data1
    def parseCookies(self,s):
        if s is None: return {self.SESSION_ID_STRING:None}
        ret = {}
        tmp = s.split(';')
        for t in tmp:
            coppia = t.split('=')
            k = coppia[0].strip()
            v = coppia[1].strip()
            ret[k]=v
        return ret


# ############################## SERVER ###################################
if __name__=='__main__':
    sys.path.insert(0,"../..")
from rprj.apps import dbschema
from rprj.dblayer import createConnection
RPRJ_CONFIG = "%s%s.config%srprj%srprj.cfg" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
RPRJ_LOCAL_DB = "sqlite:%s%s.config%srprj%srprj.db" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
class JSONServer:
    def __init__(self, config_file=RPRJ_CONFIG, db_url=RPRJ_LOCAL_DB):
        self.LoadConfig(config_file)
        # TODO? self.SetupPlugins(RPRJ_PLUGINS)
        # Logic
        self.dbmgr = None
        self.dbeFactory = dbschema.dbeFactory
        url = self.getPref("DB", "url", db_url)
        self.SaveConfig()
        self.connectToServer(url)
        self.SaveConfig()
    def __del__(self):
        #print "JSONServer.__del__: start."
        self.SaveConfig()
        #print "JSONServer.__del__: end."
    def ping(self, *args): #**kargv):
        return [ '', ['pong'] ]
    def echo(self,*args):
        return args[0]
    def handle_request(self, request_body):
        """req: string"""
        ret = None
        try:
            json_request = json.loads(request_body)
            if hasattr(self, json_request['method']):
                ret = getattr(self, json_request['method'])(json_request['params'])
            # TODO method with 'dots'
            elif json_request['method'].find(".")>=0:
                try:
                    pycode = "ret = self.%s(%s)" % (json_request['method'], ",".join(json_request['params']))
                    print "pycode:", pycode
                    exec(compile(pycode, "myeval.py","exec"))
                except Exception,e:
                    myerr = "%s" % e
                    print myerr
                    print "".join(traceback.format_tb(sys.exc_info()[2]))
            else:
                ret = json_request
            #ret = {'cippa':'lippa', 'bin':True, 'num':123.4, 'request': json_request, }
        except Exception,e:
            print "JSONServer.sendRequest: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % ( e )
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            print "JSONServer.sendRequest: ECCEZIONE ############# Data"
            print request_body
            print "JSONServer.sendRequest: ECCEZIONE ############# Fine."
            ret = ["ERROR TODO: handle the errors!!!\nEXCEPTION: %s\n%s" %(e, "".join( traceback.format_tb(sys.exc_info()[2]) ))]
        # TODO: all the following should be in a serializer method
        print "type(ret):", type(ret), ret
        return json.dumps( dataToRaw(ret) )

# ############################## PREFERENCES ###################################
    def _checkPath(self,mycfgfile):
        dirs = mycfgfile.split(os.path.sep)[:-1]
        tmpdir = []
        for d in dirs:
            if d == "":
                continue
            tmpdir.append(d)
            tmpstr = "%s%s" % (os.path.sep, os.path.sep.join(tmpdir))
            if sys.platform=='win32':
                tmpstr = os.path.sep.join(tmpdir)
            if not os.path.exists(tmpstr):
                os.mkdir(tmpstr)
        return os.path.exists(tmpstr)
    def LoadConfig(self,config_file):
        self.config_filename = config_file
        self._checkPath(self.config_filename)
        self.config = ConfigParser()
        try:
            self.config.readfp(file(self.config_filename,'rt'))
        except Exception,e:
            print e
    def SaveConfig(self):
        self._checkPath(self.config_filename)
        self.config.write( file(self.config_filename,'w') )
    def getPref(self,section,option,default=None):
        if isinstance(default,bool): default="%s" % (default)
        if not self.config.has_section(section):
            self.config.add_section(section)
        if not self.config.has_option(section,option):
            self.config.set(section,option,default)
        tmp = self.config.get(section,option,default)
        ret = tmp
        try:
            pycode = "ret = %s" % (tmp)
            exec(compile(pycode, "myeval.py","exec"))
        except Exception,e:
            pass
        return ret
# ############################## LOGIC ###################################
    def connectToServer(self,url):
        myurl = url
#        if myurl.startswith("http"):
#            if myurl.endswith("/xmlrpc_server.php"):
#                pass # OK
#            elif myurl.endswith("/"):
#                myurl = "%sxmlrpc_server.php" % ( myurl )
#            else:
#                myurl = "%s/xmlrpc_server.php" % ( myurl )
        # Connection
        myconn = createConnection(myurl,self.getPref("Debug","connection",False))
        # Server
        self.dbmgr = dbschema.ObjectMgr(myconn, self.getPref("Debug","server",False), self.getPref('DB','schema','rprj'))
        self.dbmgr.setDBEFactory(self.dbeFactory)
    def login(self, *args):
        print "args:", args
        user = args[0][0]
        pwd = args[0][1]
        print "user:", user
        print "pwd:", pwd
        self.dbmgr.connect()
        myerr = ""
        msg = ''
        ret = None
        try:
            ret = self.dbmgr.login(user,pwd)
        except Exception,e:
            myerr = "%s" % e
            print myerr
            print "".join(traceback.format_tb(sys.exc_info()[2]))
        if not self.dbmgr.user is None:
            msg = "Logged in: %s (%s)" % (self.dbmgr.user.getValue('login'), self.dbmgr.user.getValue('fullname'))
        elif len(myerr)>0:
            msg = "Login error: %s."%myerr
        else:
            msg = "Login error!"
        return [msg, [ret]]
    def getLoggedUser(self, *args):
        ret = self.dbmgr.getDBEUser()
        if ret:
            return ['', [self.dbmgr.getDBEUser()] ]
        else:
            return [b64encode('Not logged in!'), [] ]
    def search(self, *args):
        # NOTE casesensitive NOT handled!!!
        dbe, uselike, casesensitive, orderby, ignore_deleted, full_object = args[0]
        dbe = rawToDbe(self.dbmgr, dbe)
        if orderby=='': orderby = None
        print "dbe:", dbe
        print "uselike:", uselike
        return ['', self.dbmgr.search(dbe, uselike, orderby, ignore_deleted, full_object) ]
    def insert(self, *args):
        dbe = rawToDbe(self.dbmgr, args[0][0])
        ret = self.dbmgr.insert(dbe)
        if ret is None:
            return ['', [] ]
        else:
            return ['', [ret] ]
    def update(self, *args):
        dbe = rawToDbe(self.dbmgr, args[0][0])
        ret = self.dbmgr.update(dbe)
        if ret is None:
            return ['', [] ]
        else:
            return ['', [ret] ]
    def delete(self, *args):
        dbe = rawToDbe(self.dbmgr, args[0][0])
        ret = self.dbmgr.delete(dbe)
        if ret is None:
            return ['', [] ]
        else:
            return ['', [ret] ]
    def objectById(self, *args):
        myid, ignore_deleted = args[0]
        ret = self.dbmgr.objectById(myid, ignore_deleted)
        if ret is None:
            return ['', [] ]
        else:
            return ['', [ret] ]
    def objectByName(self, *args):
        myid, ignore_deleted = args[0]
        print "myid:", myid
        ret = self.dbmgr.objectByName(myid, ignore_deleted)
        if ret is None:
            return ['', [] ]
        else:
            return ['', [ret] ]

if __name__=='__main__':
    import sys
    s = None
    if sys.platform=='darwin':
        s = JSONClient("http://localhost/~robertoroccoangeloni/rproject/plugins/jsonserver/jsonserver.php")
    else:
        s = JSONClient("http://localhost/~roberto/rproject/plugins/jsonserver/jsonserver.php")
    s = JSONClient("http://localhost:8000/")
    if os.environ.has_key('SESSION_MANAGER') and os.environ['SESSION_MANAGER'].find('roberto-ra')>=0:
        # I'm at work :-)
        print "ping:",s.ping()
        #print "s.mycookies:",s.mycookies
        print "echo:",s.echo("cippa",123.4,False,u"Lippà")
        #print "s.mycookies:",s.mycookies
        print "login:",s.login('roberto','robertopix4d')
        #print "s.mycookies:",s.mycookies
        print "dbmgr.db_version():",s.dbmgr.db_version()
        print "dbmgr.getDBEUser():",s.dbmgr.getDBEUser()
        print "dbmgr.getUserGroupsList():",s.dbmgr.getUserGroupsList()
    else:
        #print s.test()
        #print "s.mycookies:",s.mycookies
        print "ping:",s.ping()
        #print "s.mycookies:",s.mycookies
        print "echo:",s.echo("cippa",123.4,False,u"Lippà")
        #print "s.mycookies:",s.mycookies
        print "login:",s.login('roberto','echoestrade')
        #print "s.mycookies:",s.mycookies
        print "dbmgr.db_version():", rawToData( s.dbmgr, s.dbmgr.db_version() )
        print "dbmgr.getDBEUser():", rawToData( s.dbmgr, s.dbmgr.getDBEUser() )
        print "dbmgr.getUserGroupsList():", rawToData( s.dbmgr, s.dbmgr.getUserGroupsList() )

