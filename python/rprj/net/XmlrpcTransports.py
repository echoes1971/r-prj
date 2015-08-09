# -*- coding: utf-8 -*-
#
# @copyright &copy; 2012 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
# @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
# @version $Id: XmlrpcTransports.py $
# @package net
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

#
#   Xml-Rpc Proxy
#
#   A subclass of the transport class to use behind a proxy
#
#   See: http://techweb.rfa.org/staff/bill/eXist/authpyxmlrpclib.py
#
#   Usage:
#       myProxyedTransport = HttplibTransport( <myProxyName>, <myProxyPort )
#       server = Server( <URL>, transport=myProxyedTransport )
#
# --------------------------------------------------------------------


# Import per HttplibTransport
#from xmlrpclib import *
from xmlrpclib import *
import sys
import base64

# Import per BasicAuthTransport
#import string, xmlrpclib, httplib

import string, httplib

from base64 import encodestring


__version__ = "1.0.0"


class HttplibTransport(Transport):
	'''Handles an HTTP transaction to an XML-RPC server via httplib
	(httplib includes proxy-server support)
	(C) 2003 by Roberto Rocco Angeloni'''

	def __init__(self,proxy,port):
            self.__proxy = proxy
            self.__port = port

	def request(self, host, handler, request_body, verbose=0):
            from httplib import HTTPConnection
            con = HTTPConnection( self.__proxy, self.__port )
            con.connect()
            if verbose==1 or self.verbose==1:
                print "Server:\thttp://"+host+handler
                print "Sending:\t%s" % request_body
            con.request( "POST", "http://"+host+handler, request_body )
            f = con.getresponse()
            self.verbose = verbose
            #print f.read()
            return ( self.parse_response( f ) )



class BasicAuthTransport(Transport):
    def __init__(self, username=None, password=None):
        self.username=username
        self.password=password
        self.verbose=None

    def request(self, host, handler, request_body, verbose):
        # issue XML-RPC request

        h = httplib.HTTP(host)
        h.putrequest("POST", handler)

        # required by HTTP/1.1
        h.putheader("Host", host)

        # required by XML-RPC
        h.putheader("User-Agent", self.user_agent)
        h.putheader("Content-Type", "text/xml")
        h.putheader("Content-Length", str(len(request_body)))

        # basic auth
        if self.username is not None and self.password is not None:
            h.putheader("AUTHORIZATION", "Basic %s" % string.replace(
                    base64.encodestring("%s:%s" % (self.username, self.password)),
                    "\012", ""))
        h.endheaders()

        if request_body:
            h.send(request_body)

        errcode, errmsg, headers = h.getreply()

        if errcode == 401:
            raise AuthenticationException('Authorization Required')

        if errcode != 200:
            #raise xmlrpclib.ProtocolError(
            raise ProtocolError(
                host + handler,
                errcode, errmsg,
                headers
                )

        return self.parse_response(h.getfile())

class CookieTransportSessionExpiredException(Exception):
	pass
class CookieTransport(Transport):
	def __init__(self, SESSION_ID_STRING='PHPSESSID'):
		Transport.__init__(self)
		self.mycookies=None
		self.mysessid=None
		self.SESSION_ID_STRING = SESSION_ID_STRING
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
	def single_request(self, host, handler, request_body, verbose=1):
		h = self.make_connection(host)
		if verbose:
			h.set_debuglevel(1)
		#h.accept_gzip_encoding=False
		#print "CookieTransport.request: request_body=%s"%(request_body)
		#print "CookieTransport.request: handler=%s"%(handler)
		try:
			self.send_request(h, handler, request_body)
			self.send_host(h, host)
			if not self.mysessid is None:
				h.putheader("Cookie", "%s=%s" % (self.SESSION_ID_STRING,self.mysessid) )
				#h.putheader("Cookie", "path=/")
			self.send_user_agent(h)
			self.send_content(h, request_body)
			#print "CookieTransport.request: h=%s"%(dir(h))
			resp = h.getresponse(buffering=True)
			errcode = resp.status
			errmessage = resp.reason
			headers = resp.getheaders()
			self.mycookies = self.parseCookies( resp.getheader('set-cookie') )
			if self.mysessid is None:
				if self.mycookies.has_key(self.SESSION_ID_STRING):
					self.mysessid = self.mycookies[self.SESSION_ID_STRING]
			if errcode == 200:
				self.verbose = verbose
				#try:
					#sock = h._conn.sock
				#except AttributeError:
					#sock = None
				#print "CookieTransport.request: resp=%s"%(dir(resp))
				try:
					return self.parse_response(resp)
				except Exception,e:
					print "CookieTransport.single_request: request_body=%s"%request_body
					print "CookieTransport.single_request: resp=%s" % resp.read()
					raise e
		except Fault:
			#print "CookieTransport.single_request: resp=%s" % resp.read()
			raise
		except Exception:
			# All unexpected errors leave connection in
			# a strange state, so we clear it.
			#print "CookieTransport.single_request: resp=%s" % (h.getresponse())
			self.close()
			raise
		#discard any response data and raise exception
		if (resp.getheader("content-length", 0)):
			resp.read()
		raise ProtocolError(
			host + handler,
			resp.status, resp.reason,
			resp.msg,
			)

		self.verbose=verbose

		try:
			sock = h._conn.sock
		except AttributeError:
			sock = None
		# 2011.07.08: inizio.
		return self.parse_response(resp)
		#return self._parse_response(h.getfile(), sock)
		# 2011.07.08: fine.

class CookieTransport26(Transport):
	"""Cookie Transport for Python 2.6"""
	def __init__(self, SESSION_ID_STRING='PHPSESSID'):
		Transport.__init__(self)
		self.mycookies=None
		self.mysessid=None
		self.SESSION_ID_STRING = SESSION_ID_STRING
	
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
	
	def request(self, host, handler, request_body, verbose=0):
		# issue XML-RPC request
		h = self.make_connection(host)
		if verbose:
			h.set_debuglevel(1)
		
		self.send_request(h, handler, request_body)
		self.send_host(h, host)
		if not self.mysessid is None:
			h.putheader("Cookie", "%s=%s" % (self.SESSION_ID_STRING,self.mysessid) )
			#print "CookieTransport.request: Cookie: %s=%s" % (self.SESSION_ID_STRING,self.mysessid)
		self.send_user_agent(h)
		self.send_content(h, request_body)
		
		errcode, errmsg, headers = h.getreply()
		#print "CookieTransport.request: headers=%s (%s)" % (headers, type(headers) )
		#print "CookieTransport.request: dir(headers)=%s" % ( dir(headers) )
		#print "CookieTransport.request: set-cookie=",headers.getheader('set-cookie')
		
		self.mycookies = self.parseCookies( headers.getheader('set-cookie') )
		if self.mysessid is None:
			#self.mycookies = self.parseCookies( headers.getheader('set-cookie') )
			if self.mycookies.has_key(self.SESSION_ID_STRING):
				self.mysessid = self.mycookies[self.SESSION_ID_STRING]
				#print "CookieTransport.request: self.mysessid=%s" % ( self.mysessid )
		#elif self.mycookies.has_key(self.SESSION_ID_STRING) and self.mysessid != self.mycookies[self.SESSION_ID_STRING]:
		#	self.mysessid = None
		#	raise CookieTransportSessionExpiredException("Session Expired")
		
		if errcode != 200:
			print h.getfile().read()
			raise ProtocolError(
				host + handler,
				errcode, errmsg,
				headers
				)
		
		self.verbose = verbose
		
		try:
			sock = h._conn.sock
		except AttributeError:
			sock = None
		
		return self._parse_response(h.getfile(), sock)

_major,_minor,_subversion,_stringa,_altro = sys.version_info
if _major==2 and _minor==6:
	CookieTransport = CookieTransport26

class SafeCookieTransport(CookieTransport):
	def make_connection(self, host):
		if self._connection and host == self._connection[0]:
			return self._connection[1]
		# create a HTTPS connection object from a host descriptor
		# host may be a string, or a (host, x509-dict) tuple
		try:
			HTTPS = httplib.HTTPSConnection
		except AttributeError:
			raise NotImplementedError(
				"your version of httplib doesn't support HTTPS"
				)
		else:
			chost, self._extra_headers, x509 = self.get_host_info(host)
			self._connection = host, HTTPS(chost, None, **(x509 or {}))
			return self._connection[1]
