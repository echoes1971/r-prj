
import os
import sys
import traceback
from wsgiref.simple_server import make_server

if __name__=='__main__':
    sys.path.insert(0,"../..")

#from rprj.apps import dbschema #, formschema
#from rprj.dblayer import DBMgr, createConnection
from rprj.net.jsonlib import JSONServer

#RPRJ_CONFIG = "%s%s.config%srprj%srprj.cfg" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
#RPRJ_LOCAL_DB = "%s%s.config%srprj%srprj.db" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
#RPRJ_PLUGINS = "%s%s.config%srprj%splugins" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )

RPRJ_CONFIG_TEST = "%s%s.config%srprj%stest.cfg" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )
RPRJ_LOCAL_DB_TEST = "sqlite:%s%s.config%srprj%stest.db" % ( os.path.expanduser("~"), os.path.sep, os.path.sep, os.path.sep )

s=JSONServer(config_file=RPRJ_CONFIG_TEST, db_url=RPRJ_LOCAL_DB_TEST)

def application(environ, start_response):
    # Environ
    response_body = ['%s: %s' % (key, value) for key, value in sorted(environ.items())]
    response_body = '\n'.join(response_body)
    # POST
    if environ['REQUEST_METHOD']=='POST':
        request_body_size = 0
        if environ.has_key('CONTENT_LENGTH'):
            request_body_size = int(environ.get('CONTENT_LENGTH', 0))
        request_body = environ['wsgi.input'].read(request_body_size)
        response_body = s.handle_request(request_body)
        #for k,v in environ.items():
        #    print "env: %s=%s" % (k,v)
        print "request_body:", request_body
        print "response_body:", response_body
    elif environ['REQUEST_METHOD']=='GET':
        response_body="{'ERROR':' handle of GET not yet implemented!!!' }"
    status = '200 OK'
    response_headers = [('Content-Type', 'text/plain'),
                  ('Content-Length', str(len(response_body)))]
    start_response(status, response_headers)
   
    return [response_body]


if __name__=='__main__':
    #print sys.argv
    host = 'localhost'
    port = 1771
    if len(sys.argv)>1:
        try:
            port = int(sys.argv[1])
        except Exception,e:
            pass
    httpd = make_server(host, port, application)
    print "======================================================================"
    print "= WGSI Server: %s:%s" % (host,port)
    print "======================================================================"
    httpd.serve_forever()

