#!/usr/bin/python
# -*- coding: utf-8 -*-
"""
./test_dbconnector.py sqlitedb
./test_dbconnector.py sqlitedb test.eml test2.jpg
./test_dbconnector.py sqlitedb test.jpg test2.jpg test.eml
./test_dbconnector.py xmlrpc test.jpg test2.jpg test.eml
./test_dbconnector.py json test.jpg test2.jpg test.eml


python -m cProfile ./test_dbconnector.py sqlitedb
"""

import os,sys,traceback, unittest
sys.path.insert(0,"..")

from rprj.apps import dbschema

class TestDBConnector(unittest.TestCase):
    def setUp(self):
        self.TEST_CONNECTOR="json_wsgi" #mongodb sqlitedb xmlrpc
        if len(sys.argv)>1:
            self.TEST_CONNECTOR = sys.argv[1]
        self.local_filename='test_dbconnector.py'
        self.local_filename2='test_explorer.py'
        self.email_filename="test.eml"
        if len(sys.argv)>2:
            self.local_filename=sys.argv[2]
        if len(sys.argv)>3:
            self.local_filename2=sys.argv[3]
        if len(sys.argv)>4:
            self.email_filename=sys.argv[4]
    
        dbschema.dbeFactory.verbose = False
        self.myconn = None
        if self.TEST_CONNECTOR=='mydb':
            from rprj.dblayer.mydb import MYConnectionProvider
            self.myconn = MYConnectionProvider( 'localhost', 'rproject','root','', True )
        elif self.TEST_CONNECTOR=='pgdb':
            from rprj.dblayer.pgdb import PGConnectionProvider
            self.myconn = PGConnectionProvider( '127.0.0.1', 'rproject','roberto','', True )
        elif self.TEST_CONNECTOR=='sqlitedb':
            from rprj.dblayer.sqlitedb import SQLiteConnectionProvider
            self.RPRJ_LOCAL_DB = "%s%s.config%srprj%srprj.db" % ( os.path.expanduser("~"),os.path.sep,os.path.sep,os.path.sep )
            self.myconn = SQLiteConnectionProvider( '', './test.db','','', True )
            #myconn = SQLiteConnectionProvider( '', '/home/roberto/.config/rprj/rprj.db','','', True )
        elif self.TEST_CONNECTOR=='winodbc':
            from rprj.dblayer.winodbc import WinOdbcConnectionProvider
            self.myconn = WinOdbcConnectionProvider( 'localhost', 'rproject','adm','adm', True )
        elif self.TEST_CONNECTOR=='xmlrpc':
            from rprj.dblayer.xmlrpc import XmlrpcConnectionProvider
            if sys.platform=='darwin':
                self.myconn = XmlrpcConnectionProvider( 'http://localhost/~robertoroccoangeloni/rproject/xmlrpc_server.php', '','','', False )
            else:
                self.myconn = XmlrpcConnectionProvider( 'http://localhost/~roberto/rproject/xmlrpc_server.php', '','','', False )
        elif self.TEST_CONNECTOR=='json':
            from rprj.dblayer.jsonconn import JsonConnectionProvider
            if sys.platform=='darwin':
                self.myconn = JsonConnectionProvider( 'json://localhost/~robertoroccoangeloni/rproject/jsonserver.php', '','','', True )
            else:
                self.myconn = JsonConnectionProvider( 'json://localhost/~roberto/rproject/jsonserver.php', '','','', False )
        elif self.TEST_CONNECTOR=='json_wsgi':
            from rprj.dblayer.jsonconn import JsonConnectionProvider
            self.myconn = JsonConnectionProvider( 'json://localhost:1771', '','','', True )
        elif self.TEST_CONNECTOR=='mongodb':
            from rprj.dblayer.mongo import MongoConnectionProvider
            self.myconn = MongoConnectionProvider( 'localhost', 'rproject','adm','adm', True )
        self.dbmgr = dbschema.ObjectMgr(self.myconn, False, 'test')
        self.dbmgr.setDBEFactory(dbschema.dbeFactory)
        
        self.dbmgr.connect()
    def tearDown(self):
        print "======================= Remove DB ======================="
        self.dbmgr._verbose=False
        self.dbmgr.removeDB()
        #self.dbmgr.disconnect()
        pass
    
    def test_connect(self):
        print "======================= Connect ======================="
        self.dbmgr.connect()
        print "ping:",self.dbmgr.ping()
        self.dbmgr.removeDB()
        self.assertEqual(self.dbmgr.ping(), "pong")
    def test_login(self):
        self.dbmgr._verbose=True
        self.dbmgr.login('roberto','echoestrade')
        self.dbmgr._verbose=False
        print "isLoggedIn:",self.dbmgr.isLoggedIn()
        print "User:",self.dbmgr.getDBEUser()
        print "Groups:",self.dbmgr.getUserGroupsList()
        self.assertTrue(self.dbmgr.isLoggedIn())
    def test_crud(self):
        print "======================= TEST CRUD ======================="
        self.dbmgr.login('roberto','echoestrade')
        print "======================= Insert ======================="
        nuova = self.dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':u"prova folder à"})
        self.dbefolder = self.dbmgr.insert(nuova)
        print "dbefolder:",self.dbefolder
        nuova = self.dbmgr.getClazzByTypeName('DBENote')(attrs={'name':"prova nota"})
        nuova.readFKFrom(self.dbefolder)
        self.dbe = self.dbmgr.insert(nuova)
        print "dbe:",self.dbe
        self.assertTrue(not self.dbe.isNew() and not self.dbefolder.isNew())
        print "======================= ObjectMgr ======================="
        #self.myconn.verbose=True
        print "      ObjectById:",self.dbmgr.objectById( self.dbefolder.getValue('id') )
        print "  fullObjectById:",self.dbmgr.fullObjectById( self.dbefolder.getValue('id') )
        print "    ObjectByName:",self.dbefolder.getValue('name'),"->",self.dbmgr.objectByName( self.dbefolder.getValue('name') )
        print "fullObjectByName:",self.dbefolder.getValue('name'),"->",self.dbmgr.fullObjectByName( self.dbefolder.getValue('name') )
        self.myconn.verbose=False
        print "======================= Update ======================="
        self.dbe.setValue("name","prova update nota")
        self.dbe = self.dbmgr.update(self.dbe)
        print "dbe:",self.dbe
        print "======================= Search ======================="
        cerca = self.dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
        lista = self.dbmgr.search(cerca)
        print "lista:",len(lista)
        for l in lista:
            print "-> %s" % ( l )
        print "======================= Delete ======================="
        return
        cerca = self.dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':'prova%folder'})
        lista = self.dbmgr.search(cerca,ignore_deleted=False)
        for l in lista:
            print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
            #l.setValue('name', u"%s"%l.getValue('name').replace("?","") )
            self.dbmgr.delete(l)
            if l.isDeleted():
                l = self.dbmgr.delete(l)
                #print "\tdeleted",l.getValue('id'),l.getValue('name')
        cerca = self.dbmgr.getClazzByTypeName('DBENote')(attrs={'name':'prova%nota'})
        lista = self.dbmgr.search(cerca,ignore_deleted=False)
        for l in lista:
            print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
            l = self.dbmgr.delete(l)
            if l.isDeleted():
                l = self.dbmgr.delete(l)
                #print "\tdeleted",l.getValue('id'),l.getValue('name')
        # Final search
        cerca = self.dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
        lista = self.dbmgr.search(cerca,ignore_deleted=False)
        print "lista:",len(lista)
        for l in lista:
            print "-> %s" % ( l )
    def xxxtest_files(self):
        return
        self.dbmgr.login('roberto','cippalippa')
        print "======================= Insert ======================="
        nuova = self.dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':u"prova folder à"})
        self.dbefolder = self.dbmgr.insert(nuova)
        print "dbefolder:",self.dbefolder
        print "======================= Upload ======================="
        self.dbefile = None
        self.dbmgr._verbose=True
        try:
            filename = self.dbmgr.uploadFile( self.local_filename )
            self.dbefile = self.dbmgr.getClazzByTypeName('DBEFile')(attrs={'filename':filename})
            self.dbefile = self.dbmgr.insert(self.dbefile)
            #print myconn.lastMessages
            print "dbefile:",self.dbefile
        except Exception,e:
            print "--> Upload NOT available: %s" % e
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            raise e
        self.dbmgr._verbose=False
        # Email
        self.dbemail = None
        try:
            self.dbemail = self.dbmgr.getClazzByTypeName('DBEMail')(attrs={'filename':self.email_filename})
            #self.dbmgr._verbose=True
            self.dbemail = self.dbmgr.insert(self.dbemail)
            self.dbmgr._verbose=False
            #print myconn.lastMessages
            print "dbemail:",self.dbemail.getValue('id'),self.dbemail.getValue('name')
            print "\t",self.dbemail.getValue('msgdate'),self.dbemail.getValue('subject')
        except Exception,e:
            print "--> Upload NOT available: %s" % e
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            raise e
        print "======================= Change folder ======================="
        self.dbefile.setValue('father_id',self.dbefolder.getValue('id'))
        #self.dbmgr._verbose=True
        self.dbefile = self.dbmgr.update(self.dbefile)
        self.dbmgr._verbose=False
        #print myconn.lastMessages
        print "dbefile:",self.dbefile
        if self.dbefile.getValue('checksum').find('not found')>=0:
            raise Exception("Error in: Change folder: %s"%self.dbefile.getValue('checksum'))
        print "======================= Download ======================="
        #self.dbmgr._verbose=True
        try:
            self.local_filename = self.dbmgr.downloadFile( self.dbefile.getValue('id'), "./download" )
            print "local_filename:",self.local_filename
        except Exception,e:
            print "--> Download NOT available: %s" % e
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            raise e
        self.dbmgr._verbose=False
        print "======================= Update ======================="
        try:
            self.dbefile.setValue('filename',self.local_filename2)
            self.dbefile = self.dbmgr.update(self.dbefile)
            #print myconn.lastMessages
            print "dbefile:",self.dbefile
        except Exception,e:
            print "--> Upload NOT available: %s" % e
            print "".join( traceback.format_tb(sys.exc_info()[2]) )
            raise e
        print "======================= Search ======================="
        cerca = self.dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
        lista = self.dbmgr.search(cerca)
        print "lista:",len(lista)
        for l in lista:
            print "-> %s" % ( l )
        print "======================= Delete ======================="
        cerca = self.dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':'prova%folder'})
        lista = self.dbmgr.search(cerca,ignore_deleted=False)
        for l in lista:
            print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
            #l.setValue('name', u"%s"%l.getValue('name').replace("?","") )
            self.dbmgr.delete(l)
            if l.isDeleted():
                l = self.dbmgr.delete(l)
                #print "\tdeleted",l.getValue('id'),l.getValue('name')
        if not self.dbefile is None:
            self.dbmgr.delete(self.dbefile)
            #self.dbmgr._verbose=True
            cerca = self.dbmgr.getClazzByTypeName('DBEFile')(attrs={'filename':self.dbefile.getValue('filename')})
            lista = self.dbmgr.search(cerca,ignore_deleted=False)
            #self.dbmgr._verbose=False
            for l in lista:
                print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
                l = self.dbmgr.delete(l)
                if l.isDeleted():
                    l = self.dbmgr.delete(l)
                    print "\tdeleted",l.getValue('id'),l.getValue('name')
                    #print myconn.lastMessages
            self.dbmgr._verbose=False
        if not self.dbemail is None:
            self.dbmgr.delete(self.dbemail)
            #self.dbmgr._verbose=True
            cerca = self.dbmgr.getClazzByTypeName('DBEMail')(attrs={'filename':self.dbemail.getValue('filename')})
            lista = self.dbmgr.search(cerca,ignore_deleted=False)
            #self.dbmgr._verbose=False
            for l in lista:
                print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
                l = self.dbmgr.delete(l)
                if l.isDeleted():
                    l = self.dbmgr.delete(l)
                    print "\tdeleted",l.getValue('id'),l.getValue('name')
                    #print myconn.lastMessages
            self.dbmgr._verbose=False
        # Final search
        cerca = self.dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
        lista = self.dbmgr.search(cerca,ignore_deleted=False)
        print "lista:",len(lista)
        for l in lista:
            print "-> %s" % ( l )

def main():
	TEST_CONNECTOR="sqlitedb"
	if len(sys.argv)>1:
		TEST_CONNECTOR = sys.argv[1]
	local_filename='test_dbconnector.py'
	local_filename2='test_explorer.py'
	email_filename="test.eml"
	if len(sys.argv)>2:
		local_filename=sys.argv[2]
	if len(sys.argv)>3:
		local_filename2=sys.argv[3]
	if len(sys.argv)>4:
		email_filename=sys.argv[4]

	dbschema.dbeFactory.verbose = False
	myconn = None
	if TEST_CONNECTOR=='mydb':
		from rprj.dblayer.mydb import MYConnectionProvider
		myconn = MYConnectionProvider( 'localhost', 'rproject','root','', True )
	elif TEST_CONNECTOR=='pgdb':
		from rprj.dblayer.pgdb import PGConnectionProvider
		myconn = PGConnectionProvider( '127.0.0.1', 'rproject','roberto','', True )
	elif TEST_CONNECTOR=='sqlitedb':
		from rprj.dblayer.sqlitedb import SQLiteConnectionProvider
		RPRJ_LOCAL_DB = "%s%s.config%srprj%srprj.db" % ( os.path.expanduser("~"),os.path.sep,os.path.sep,os.path.sep )
		myconn = SQLiteConnectionProvider( '', './test.db','','', True )
		#myconn = SQLiteConnectionProvider( '', '/home/roberto/.config/rprj/rprj.db','','', True )
	elif TEST_CONNECTOR=='winodbc':
		from rprj.dblayer.winodbc import WinOdbcConnectionProvider
		myconn = WinOdbcConnectionProvider( 'localhost', 'rproject','adm','adm', True )
	elif TEST_CONNECTOR=='xmlrpc':
		from rprj.dblayer.xmlrpc import XmlrpcConnectionProvider
		if sys.platform=='darwin':
			myconn = XmlrpcConnectionProvider( 'http://localhost/~robertoroccoangeloni/rproject/xmlrpc_server.php', '','','', False )
		else:
			myconn = XmlrpcConnectionProvider( 'http://localhost/~roberto/rproject/xmlrpc_server.php', '','','', False )
	elif TEST_CONNECTOR=='json':
		from rprj.dblayer.jsonconn import JsonConnectionProvider
		if sys.platform=='darwin':
			myconn = JsonConnectionProvider( 'json://localhost/~robertoroccoangeloni/rproject/jsonserver.php', '','','', True )
		else:
			myconn = JsonConnectionProvider( 'json://localhost/~roberto/rproject/jsonserver.php', '','','', False )
	
	dbmgr = dbschema.ObjectMgr(myconn, False, 'test')
	dbmgr.setDBEFactory(dbschema.dbeFactory)
	
	print "======================= Connect ======================="
	dbmgr.connect()
	print "ping:",dbmgr.ping()
	dbmgr.removeDB()
	
	print "======================= isLoggedIn ======================="
	print "isLoggedIn:",dbmgr.isLoggedIn()
	
	print "======================= Login & InitDB ======================="
	dbmgr._verbose=True
	dbmgr.login('roberto','cippalippa')
	dbmgr._verbose=False
	
	print "isLoggedIn:",dbmgr.isLoggedIn()
	print "User:",dbmgr.getDBEUser()
	print "Groups:",dbmgr.getUserGroupsList()
	if not dbmgr.isLoggedIn():
		print "Exiting: not logged in"
		exit(1)
	try:
		print "======================= Insert ======================="
		nuova = dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':u"prova folder à"})
		dbefolder = dbmgr.insert(nuova)
		print "dbefolder:",dbefolder
		nuova = dbmgr.getClazzByTypeName('DBENote')(attrs={'name':"prova nota"})
		nuova.readFKFrom(dbefolder)
		dbe = dbmgr.insert(nuova)
		print "dbe:",dbe
		
		print "======================= ObjectMgr ======================="
		#myconn.verbose=True
		print "      ObjectById:",dbmgr.objectById( dbefolder.getValue('id') )
		print "  fullObjectById:",dbmgr.fullObjectById( dbefolder.getValue('id') )
		print "    ObjectByName:",dbefolder.getValue('name'),"->",dbmgr.objectByName( dbefolder.getValue('name') )
		print "fullObjectByName:",dbefolder.getValue('name'),"->",dbmgr.fullObjectByName( dbefolder.getValue('name') )
		myconn.verbose=False
		
		print "======================= Upload ======================="
		dbefile = None
		dbmgr._verbose=True
		try:
			filename = dbmgr.uploadFile( local_filename )
			dbefile = dbmgr.getClazzByTypeName('DBEFile')(attrs={'filename':filename})
			dbefile = dbmgr.insert(dbefile)
			#print myconn.lastMessages
			print "dbefile:",dbefile
		except Exception,e:
			print "--> Upload NOT available: %s" % e
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			raise e
		dbmgr._verbose=False
		
		dbemail = None
		try:
			dbemail = dbmgr.getClazzByTypeName('DBEMail')(attrs={'filename':email_filename})
			#dbmgr._verbose=True
			dbemail = dbmgr.insert(dbemail)
			dbmgr._verbose=False
			#print myconn.lastMessages
			print "dbemail:",dbemail.getValue('id'),dbemail.getValue('name')
			print "\t",dbemail.getValue('msgdate'),dbemail.getValue('subject')
		except Exception,e:
			print "--> Upload NOT available: %s" % e
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			raise e
		
		print "======================= Change folder ======================="
		dbefile.setValue('father_id',dbefolder.getValue('id'))
		#dbmgr._verbose=True
		dbefile = dbmgr.update(dbefile)
		dbmgr._verbose=False
		#print myconn.lastMessages
		print "dbefile:",dbefile
		if dbefile.getValue('checksum').find('not found')>=0:
			raise Exception("Error in: Change folder: %s"%dbefile.getValue('checksum'))
		print "======================= Download ======================="
		#dbmgr._verbose=True
		try:
			local_filename = dbmgr.downloadFile( dbefile.getValue('id'), "./download" )
			print "local_filename:",local_filename
		except Exception,e:
			print "--> Download NOT available: %s" % e
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			raise e
		dbmgr._verbose=False
		
		print "======================= Update ======================="
		dbe.setValue("name","prova update nota")
		dbe = dbmgr.update(dbe)
		print "dbe:",dbe
		try:
			dbefile.setValue('filename',local_filename2)
			dbefile = dbmgr.update(dbefile)
			#print myconn.lastMessages
			print "dbefile:",dbefile
		except Exception,e:
			print "--> Upload NOT available: %s" % e
			print "".join( traceback.format_tb(sys.exc_info()[2]) )
			raise e
		
		print "======================= Search ======================="
		cerca = dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
		lista = dbmgr.search(cerca)
		print "lista:",len(lista)
		for l in lista:
			print "-> %s" % ( l )
		
		print "======================= Delete ======================="
		#dbmgr._verbose=True
		#dbefolder = dbmgr.delete(dbefolder)
		#print "dbefolder:",dbefolder
		#dbe = dbmgr.delete(dbe)
		#print "dbe:",dbe

		# More than one instance?
		cerca = dbmgr.getClazzByTypeName('DBEFolder')(attrs={'name':'prova%folder'})
		lista = dbmgr.search(cerca,ignore_deleted=False)
		for l in lista:
			print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
			#l.setValue('name', u"%s"%l.getValue('name').replace("?","") )
			dbmgr.delete(l)
			if l.isDeleted():
				l = dbmgr.delete(l)
				#print "\tdeleted",l.getValue('id'),l.getValue('name')
		
		cerca = dbmgr.getClazzByTypeName('DBENote')(attrs={'name':'prova%nota'})
		lista = dbmgr.search(cerca,ignore_deleted=False)
		for l in lista:
			print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
			l = dbmgr.delete(l)
			if l.isDeleted():
				l = dbmgr.delete(l)
				#print "\tdeleted",l.getValue('id'),l.getValue('name')
		
		if not dbefile is None:
			dbmgr.delete(dbefile)
			#dbmgr._verbose=True
			cerca = dbmgr.getClazzByTypeName('DBEFile')(attrs={'filename':dbefile.getValue('filename')})
			#cerca = dbmgr.getClazzByTypeName('DBEFile')(attrs={'name':dbefile.getValue('name')})
			lista = dbmgr.search(cerca,ignore_deleted=False)
			#dbmgr._verbose=False
			for l in lista:
				print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
				l = dbmgr.delete(l)
				if l.isDeleted():
					l = dbmgr.delete(l)
					print "\tdeleted",l.getValue('id'),l.getValue('name')
					#print myconn.lastMessages
			dbmgr._verbose=False
			#dbefile3 = dbmgr.delete(dbefile)
		if not dbemail is None:
			dbmgr.delete(dbemail)
			#dbmgr._verbose=True
			cerca = dbmgr.getClazzByTypeName('DBEMail')(attrs={'filename':dbemail.getValue('filename')})
			lista = dbmgr.search(cerca,ignore_deleted=False)
			#dbmgr._verbose=False
			for l in lista:
				print "Deleting:",l.getValue('id'),l.getValue('name'),l.isDeleted()
				l = dbmgr.delete(l)
				if l.isDeleted():
					l = dbmgr.delete(l)
					print "\tdeleted",l.getValue('id'),l.getValue('name')
					#print myconn.lastMessages
			dbmgr._verbose=False
		
		cerca = dbmgr.getClazzByTypeName('DBEObject')(attrs={'name':"prova"})
		lista = dbmgr.search(cerca,ignore_deleted=False)
		print "lista:",len(lista)
		for l in lista:
			print "-> %s" % ( l )
	except Exception,e1:
		print "--> ERROR: %s" % e1
		print "".join( traceback.format_tb(sys.exc_info()[2]) )
	print "======================= RemoveDB ======================="
	dbmgr._verbose=False
	dbmgr.removeDB()

if __name__=='__main__':
    #main()
    unittest.main()

