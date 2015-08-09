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


from rprj.dblayer import DBConnectionProvider, DBLayerException, isDateTime
from rprj.net.jsonlib import JSONClient, Binary
import codecs
import datetime
import os
import sys
import traceback
from base64 import b64decode
# import threading # TEST

# 1 mega = 1048576
UPLOAD_SPLIT_SIZE = 1048576


class JsonConnectionProvider(DBConnectionProvider):
    def __init__(self, host, db, user, pwd, verbose=0):
        """host: here is the url of the server
        db: TODO is meaningless here?"""
        DBConnectionProvider.__init__(self, host, db, user, pwd, verbose)
        self.verbose = verbose
        if self._host.startswith('jsons'):
            raise DBLayerException("TODO: Json via https")
        self._server = None
        self._cache = {}
        self.dbmgr = None  # Needed by _jsonToDbe
        # self.semaforo = threading.Semaphore()

    def customInit(self):
        """???"""
        pass

    def getConnection(self):
        # self.semaforo.acquire(True)
        if self._server is None:
            self._server = JSONClient(self._host)
        # self.semaforo.release()
        return self

    def freeConnection(self, conn):
        # self.semaforo.acquire(True)
        if self._server is not None:
            del self._server
        self._server = None
        # self.semaforo.release()

    def getDBType(self):
        return "Json"

    def isProxy(self):
        return True

    def _dbeToJson(self, dbe):
        ret = {}
        for n in dbe.getNames():
            tmpvalue = dbe.getValue(n)
            if isinstance(tmpvalue, datetime.datetime) or isinstance(tmpvalue, datetime.date) \
                    or isinstance(tmpvalue, datetime.time):  # or isinstance(tmpvalue,Binary):
                ret[n] = "%s" % tmpvalue
            else:
                ret[n] = tmpvalue
        return [dbe.getTypeName(), ret]

    def _jsonToDbe(self, xmldbe):
        typename = xmldbe['_typename']
        xmldbe.pop('_typename')
        nomi = [n for n in xmldbe.keys()]
        valori = [n for n in xmldbe.values()]
        valori2 = []
        for v in valori:
            # if isinstance(v, Binary): v = v.data
            if isDateTime(v):  # rra.dblayer.isDateTime(v):
                # "0000-00-00 00:00:00"
                v2 = None
                try:
                    v2 = datetime.datetime(int(v[:4]), int(v[5:7]), int(v[8:10]), int(v[11:13]), int(v[14:16]),
                                           int(v[17:19]))
                except Exception, e:
                    try:
                        v2 = datetime.datetime(int(v[:4]), int(v[5:7]), int(v[8:10]), int(v[11:13]), int(v[14:16]))
                    except Exception, e:
                        try:
                            v2 = datetime.time(int(v[11:13]), int(v[14:16]), int(v[17:19]))
                        except Exception, e:
                            v2 = datetime.time(int(v[11:13]), int(v[14:16]))
                valori2.append(v2)
            elif (type(v) == str or type(v) == unicode) and v.startswith("base64:"):
                valori2.append(b64decode(v[len("base64:"):]))
            else:
                valori2.append(v)
        # if isinstance(typename, Binary): typename = typename.data
        myclazz = self.dbmgr.getClazzByTypeName(typename)
        return myclazz(myclazz().getTableName(), names=nomi, values=valori2)

    def getRegisteredTypes(self):
        """Returns the registered types"""
        ret = {}
        try:
            # self.semaforo.acquire(True)
            msg, lista = self._server.getTypeList()
            # self.semaforo.release()
            self.lastMessages = msg.data
            for k, v in lista[0].iteritems():
                ret[k] = RemoteDBE(k, v.data, self)
            self._cache = ret
        except Exception, e:
            print "JsonConnectionProvider.getRegisteredTypes: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % (e)
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            print "JsonConnectionProvider.getRegisteredTypes: ECCEZIONE ############# Fine."
            raise DBLayerException("Problemi di comunicazione col server: %s" % e)
        return ret

    def getClazzByTypeName(self, typename, case_sensitive=False):
        try:
            if self.verbose: print "JsonConnectionProvider.getClazzByTypeName: typename=%s" % (typename)
            if self._cache is None: self._cache = self.getRegisteredTypes()
            ret = DBEntity
            if 'default' in self._cache:
                ret = self._cache['default']
            for k in self._cache.keys():
                if self.verbose: print "DBEFactory.getClazzByTypeName: k=%s" % (k)
                mytypename = ''
                if isinstance(self._cache[k], RemoteDBE):
                    mytypename = self._cache[k].getTypeName()
                else:
                    mytypename = self._cache[k]().getTypeName()
                if case_sensitive:
                    if mytypename == typename:
                        ret = self._cache[k]
                        break
                else:
                    if mytypename.lower() == typename.lower():
                        ret = self._cache[k]
                        break
            return ret
        except Exception, e:
            print "JsonConnectionProvider.getClazzByTypeName: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % (e)
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            print "%s" % e
            print "JsonConnectionProvider.getClazzByTypeName: ECCEZIONE ############# Fine."
            return self._cache['default']

    def getClazz(self, tablename):
        ret = None
        if tablename in self._cache:
            ret = self._cache[tablename]
        else:
            ret = self._cache['default']
        return ret

    def insert(self, dbe):
        # Before Insert
        # 2012.04.04 dbe._before_insert(self.dbmgr)
        # 2012.04.04 dbe = self.dbmgr._before_insert(dbe)
        # Insert
        dbexml = self._dbeToJson(dbe)
        # self.semaforo.acquire(True)
        tmp = self._server.insert(dbexml)
        # self.semaforo.release()
        tmparray = tmp[1]
        self.lastMessages = b64decode(tmp[0])
        dbe = self._jsonToDbe(tmparray[0])
        # After Insert
        # 2012.04.04 dbe = self.dbmgr._after_insert(dbe)
        # 2012.04.04 dbe._after_insert(self.dbmgr)
        return dbe

    def update(self, dbe):
        # Before Update
        # 2012.04.04 dbe._before_update(self.dbmgr)
        # 2012.04.04 dbe = self.dbmgr._before_update(dbe)
        # Update
        dbexml = self._dbeToJson(dbe)
        # self.semaforo.acquire(True)
        tmp = self._server.update(dbexml)
        # self.semaforo.release()
        tmparray = tmp[1]
        self.lastMessages = b64decode(tmp[0])
        if len(tmparray) > 0:
            dbe = self._jsonToDbe(tmparray[0])
            # After Update
            # 2012.04.04 dbe = self.dbmgr._after_update(dbe)
            # 2012.04.04 dbe._after_update(self.dbmgr)
        else:
            dbe = None
        return dbe

    def select(self, tablename, search_string):
        if self.verbose: print "JsonConnectionProvider.select: tablename=%s" % (tablename)
        if self.verbose: print "JsonConnectionProvider.select: search_string=%s" % (search_string)
        if self.verbose: print "JsonConnectionProvider.select: start search=%s" % (datetime.datetime.now())
        try:
            # self.semaforo.acquire(True)
            tmp = self._server.select(tablename, search_string)
            # self.semaforo.release()
            res = tmp[1]
            self.lastMessages = b64decode(tmp[0])
        except DBLayerException, e:
            raise DBLayerException("Problemi di comunicazione col server: %s" % e)
        if self.verbose: print "JsonConnectionProvider.select: start xml->dbe=%s" % (datetime.datetime.now())
        ret = []
        for r in res:
            tmpdbe = self._jsonToDbe(r)
            ret.append(tmpdbe)
        if self.verbose: print "JsonConnectionProvider.select: end time = %s" % (datetime.datetime.now())
        return ret

    def delete(self, dbe):
        # Delete
        dbexml = self._dbeToJson(dbe)
        # self.semaforo.acquire(True)
        tmp = self._server.delete(dbexml)
        # self.semaforo.release()
        tmparray = tmp[1]
        self.lastMessages = b64decode(tmp[0])
        if self.verbose: print "JsonConnectionProvider.delete: self.lastMessages = %s" % (self.lastMessages)
        dbe = self._jsonToDbe(tmparray[0])
        return dbe

    def search(self, dbe, uselike=1, orderby=None, ignore_deleted=True, full_object=True):
        casesensitive = 0
        if self.verbose: print "JsonConnectionProvider.search: start time = %s" % (datetime.datetime.now())
        tablename = dbe.getTableName()
        # Search
        dbexml = self._dbeToJson(dbe)
        if self.verbose: print "JsonConnectionProvider.search: dbexml = %s" % (dbexml)
        if self.verbose: print "JsonConnectionProvider.search: uselike = %s" % (uselike)
        # if self.verbose: print "JsonConnectionProvider.search: casesensitive = %s" % (casesensitive)
        if self.verbose: print "JsonConnectionProvider.search: orderby = %s" % (orderby)
        try:
            if orderby is None: orderby = ''
            # self.semaforo.acquire(True)
            msg, lista = self._server.search(dbexml, uselike, casesensitive, orderby, ignore_deleted, full_object)
            msg = b64decode(msg)
            # self.semaforo.release()
            if self.verbose: print "JsonConnectionProvider.search: msg=\n  ", "\n  ".join(("%s" % msg).split("\n"))
            self.lastMessages = msg
        except Exception, e:
            print "JsonConnectionProvider.search: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % (e)
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            print "JsonConnectionProvider.search: ECCEZIONE ############# Fine."
            raise DBLayerException("Problemi di comunicazione col server: %s" % e)
        ret = []
        for r in lista:
            tmpdbe = self._jsonToDbe(r)
            ret.append(tmpdbe)
        if self.verbose: print "JsonConnectionProvider.search: ret = %s" % (len(ret))
        if self.verbose: print "JsonConnectionProvider.search: end time = %s" % (datetime.datetime.now())
        return ret

    def copy(self, dbe):
        raise DBLayerException("copy: not yet implemented")

# ################### Remote Server ###################
    def ping(self):
        # self.semaforo.acquire(True)
        self.msg, ret = self._server.ping()
        # self.semaforo.release()
        if self.verbose:
            print "msg:", self.msg
            print "ret:", ret
        return ret[0]

    def login(self, user, pwd):
        # self.semaforo.acquire(True)
        self.msg, ret = self._server.login(user, pwd)
        # self.semaforo.release()
        if self.verbose:
            print "JsonConnectionProvider.login: msg=%s" % self.msg
            print "JsonConnectionProvider.login: ret=%s" % ret
        if len(ret) == 1:
            tmpdbe = self._jsonToDbe(ret[0])
            # The remote server clean the password value in the returned dbe :-) more safe
            tmpdbe.setValue('pwd', pwd)
            return tmpdbe
        return None

    def getLoggedUser(self):
        # self.semaforo.acquire(True)
        self.msg, ret = self._server.getLoggedUser()
        self.msg = b64decode(self.msg)
        # self.semaforo.release()
        if self.verbose:
            print "JsonConnectionProvider.getLoggedUser: msg=%s" % self.msg
            print "JsonConnectionProvider.getLoggedUser: ret=%s" % ret
        if len(ret) == 1:
            tmp = self._jsonToDbe(ret[0])
            if self.verbose: print "JsonConnectionProvider.getLoggedUser: tmp=%s" % tmp
            return tmp
        return None

    def getFormSchema(self, language='python', aClassname=''):
        # self.semaforo.acquire(True)
        myformschema, tmp = self._server.getFormSchema(language, aClassname)
        # self.semaforo.release()
        if self.verbose:
            print "myformschema:", myformschema
            print "tmp:", tmp
        return myformschema

    def getDBSchema(self, language='python', aClassname=''):
        # self.semaforo.acquire(True)
        myformschema, tmp = self._server.getDBSchema(language, aClassname)
        # self.semaforo.release()
        if self.verbose:
            print "myformschema:", myformschema
            print "tmp:", tmp
        return myformschema

    def objectById(self, myid, ignore_deleted=True):
        # self.semaforo.acquire(True)
        self.msg, ret = self._server.objectById(myid, ignore_deleted)
        self.msg = b64decode(self.msg)
        # self.semaforo.release()
        if self.verbose:
            print "msg:", self.msg
            print "ret:", ret
        if len(ret) == 1:
            return self._jsonToDbe(ret[0])
        return None

    def fullObjectById(self, myid, ignore_deleted=True):
        # self.semaforo.acquire(True)
        ret = []
        try:
            self.msg, ret = self._server.fullObjectById(myid, ignore_deleted)
            self.msg = b64decode(self.msg)
        except Exception, e:
            print "JsonConnectionProvider.fullObjectById: ECCEZIONE ############# Start."
            print "ECCEZIONE: %s" % (e)
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            print "JsonConnectionProvider.fullObjectById: ECCEZIONE ############# End."
        finally:
            # self.semaforo.release()
            pass
        if self.verbose:
            print "msg:", self.msg
            print "ret:", ret
        if len(ret) == 1:
            return self._jsonToDbe(ret[0])
        return None

    def objectByName(self, myid, ignore_deleted=True):
        # self.semaforo.acquire(True)
        ret = []
        tmp = self._server.objectByName(myid, ignore_deleted)
        if len(tmp) > 0:
            self.msg = b64decode(tmp[0])
        if len(tmp) > 1:
            ret = tmp[1:][0]
        # self.semaforo.release()
        if self.verbose:
            print "msg:", self.msg
            print "ret:", ret
        # 2012.05.07: start.
        return [self._jsonToDbe(x) for x in ret]
        # if len(ret)==1:
        #     return self._jsonToDbe(ret[0])
        # return None
        # 2012.05.07: end.

    def fullObjectByName(self, myid, ignore_deleted=True):
        # self.semaforo.acquire(True)
        ret = []
        try:
            tmp = self._server.objectByName(myid, ignore_deleted)
            if len(tmp) > 0:
                self.msg = b64decode(tmp[0])
            if len(tmp) > 1:
                ret = tmp[1:][0]
            # self.msg, ret = self._server.fullObjectByName(myid,ignore_deleted)
            # self.msg = b64decode(self.msg)
        except Exception, e:
            print "JsonConnectionProvider.fullObjectByName: ECCEZIONE ############# Inizio."
            print "ECCEZIONE: %s" % (e)
            print "".join(traceback.format_tb(sys.exc_info()[2]))
            print "JsonConnectionProvider.fullObjectByName: ECCEZIONE ############# Fine."
            raise DBLayerException("Communication troubles with the remote server: %s" % e)
        finally:
            # self.semaforo.release()
            pass
        if self.verbose:
            print "msg:", self.msg
            print "ret:", ret
        # 2012.05.07: start.
        return [self._jsonToDbe(x) for x in ret]
        # if len(ret)==1:
        #     return self._jsonToDbe(ret[0])
        # return None
        # 2012.05.07: end.

    def uploadFile(self, local_filename):
        # Remote destination directory
        # self.semaforo.acquire(True)
        messaggio, ret = self._server.GetRandomUploadDestinationDirectory()
        messaggio = b64decode(messaggio)
        # self.semaforo.release()
        if self.verbose:
            print "JsonConnectionProvider.uploadFile: messaggio = %s" % (messaggio)
            print "JsonConnectionProvider.uploadFile: ret = %s" % (ret)
        dest_dir = ret['dest_dir']
        if self.verbose:
            print "JsonConnectionProvider.uploadFile: dest_dir:", dest_dir
        # Short local filename
        filename_corto = local_filename[len(os.path.dirname(local_filename)):]
        if filename_corto.startswith('/'):
            filename_corto = filename_corto[1:]
        if self.verbose: print "JsonConnectionProvider.uploadFile: filename_corto: ", filename_corto
        # Load file
        myfile = file(local_filename).read()
        # Upload TODO split the file in chunks
        # self.semaforo.acquire(True)
        messaggio, ret = self._server.Upload(1, 1, filename_corto, u"%s" % Binary(myfile), dest_dir)
        messaggio = b64decode(messaggio)
        # self.semaforo.release()
        if self.verbose:
            print "JsonConnectionProvider.uploadFile: messaggio = %s" % (messaggio)
            print "JsonConnectionProvider.uploadFile: ret = %s" % (ret)
        filename = ret['filename']
        dest_dir = ret['dest_dir']
        if self.verbose:
            print "JsonConnectionProvider.uploadFile: filename: ", filename
            print "JsonConnectionProvider.uploadFile: dest_dir: ", dest_dir
        return "%s/%s" % (dest_dir, filename)
        # raise DBLayerException("JsonConnectionProvider.uploadFile: not implemented.")

    def downloadFile(self, remote_filename, local_path, view_thumbnail=False):
        uuid = remote_filename
        # self.semaforo.acquire(True)
        messaggio, ret = self._server.Download(uuid, view_thumbnail)
        messaggio = b64decode(messaggio)
        # self.semaforo.release()
        if self.verbose:
            print "JsonConnectionProvider.downloadFile: messaggio = %s" % (messaggio)
            tmp = {}
            for k in ret.keys():
                if k == 'contents': continue
                tmp[k] = ret[k]
            print "JsonConnectionProvider.downloadFile: ret (senza contents) = %s" % (tmp)
        mime = "%s" % ret['mime']
        filesize = ret['filesize']
        filename = "%s%s%s" % (local_path, os.path.sep, ret['filename'])
        if self.verbose:
            print "JsonConnectionProvider.downloadFile: mime = %s" % (mime)
            print "JsonConnectionProvider.downloadFile: filesize = %s" % (filesize)
            print "JsonConnectionProvider.downloadFile: filename = %s" % (filename)
        file(filename, 'wb').write(b64decode(ret['contents']))
        return filename
