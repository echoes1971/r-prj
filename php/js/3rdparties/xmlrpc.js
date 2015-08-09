/*
 * Copyright (c) 2008 David Crawshaw <david@zentus.com>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

/*
 * An XML-RPC library for JavaScript.
 *
 * The xmlrpc() function is the public entry point.
 */

/*
 * Execute an XML-RPC method and return the response to 'callback'.
 * Parameters are passed as JS Objects, and the callback function is
 * given a single JS Object representing the server's response.
 */
var xmlrpc = function(server, method, params, callback, callErr, callFinal) {
	if (callErr == null)
		callErr = alert;
	
	var request = window.XMLHttpRequest ? new XMLHttpRequest()
		: new ActiveXObject("MSXML2.XMLHTTP.3.0");
	request.open("POST", server, true);
	request.onreadystatechange = function() {
		if (request.readyState != 4)
			return; // TODO: callbacks?
		try {
			if (request.status != 200) {
				callErr("connection error " + request.status + " ("+server+")");
				return;
			}
			var ret = null;
			try {
				if (request.responseXML) {
// 					alert(request.responseText); // RRA
					ret = xmlrpc.parseResponse(request.responseXML);
				} else
					throw "bad xml: '" + request.responseText + "'";
			} catch (err) {
				err.message = "xmlrpc: " + err.message;
				callErr(err);
				throw err;
			}
			try {
				callback(ret);
			} catch (err) {
				err.message = "callback: " + err.message;
				callErr(err);
				throw err;
			}
		} finally {
			if (callFinal)
				callFinal();
		}
	};
	var sending = xmlrpc.writeCall(method, params);
// 	alert("sending: "+sending); // RRA
	request.send(sending);
};


var xmlrpcSync = function(server, method, params) {
	var request = window.XMLHttpRequest ? new XMLHttpRequest()
		: new ActiveXObject("MSXML2.XMLHTTP.3.0");
	request.open("POST", server, false);
/*	request.onreadystatechange = function() {
		if (request.readyState != 4)
			return; // TODO: callbacks?
		try {
			if (request.status != 200) {
				callErr("connection error " + request.status + " ("+server+")");
				return;
			}
			var ret = null;
			try {
				if (request.responseXML) {
					ret = xmlrpc.parseResponse(request.responseXML);
				} else
					throw "bad xml: '" + request.responseText + "'";
			} catch (err) {
				err.message = "xmlrpc: " + err.message;
				callErr(err);
				throw err;
			}
			try {
				callback(ret);
			} catch (err) {
				err.message = "callback: " + err.message;
				callErr(err);
				throw err;
			}
		} finally {
			if (callFinal)
				callFinal();
		}
	};*/
	var sending = xmlrpc.writeCall(method, params);
	request.send(sending);
	if(request.status != 200) return null;
	return request.responseXML ? xmlrpc.parseResponse(request.responseXML) : null;
};



xmlrpc.writeCall = function(method, params) {
	out = "<?xml version=\"1.0\"?>\n";
	out += "<methodCall>\n";
	out += "<methodName>"+ method + "</methodName>\n";
	
	if (params && params.length > 0) {
		out += "<params>\n";
		for (var i=0; i < params.length; i++) {
			out += "<param><value>";
			out += xmlrpc.writeParam(params[i]);
			out += "</value></param>";
		}
		out += "</params>\n";
	}
	
	out += "</methodCall>\n";
	return out;
};

xmlrpc.writeParam = function(param) {
	if (param == null)
		return "<nil />";
	switch (typeof(param)) {
		case "boolean":     return "<boolean>" + param + "</boolean>";
		case "string":
			param = param.replace(/</g, "&lt;");
			param = param.replace(/&/g, "&amp;");
			return "<string>" + param + "</string>";
		case "undefined":   return "<nil/>";
		case "number":
			return /\./.test(param) ?
				"<double"> + param + "</double>" :
				"<int>" + param + "</int>";
		case "object":
			if (param.constructor == Array) {
				out = "<array><data>\n";
				for (var i in param) {
					out += "<value>";
					out += xmlrpc.writeParam(param[i]);
					out += "</value>\n";
				}
				out += "</data></array>";
				return out;
			} else if (param.constructor == Date) {
				out = "<dateTime.iso8601>";
				out += param.getUTCFullYear();
				if (param.getUTCMonth() < 10)
					out += "0";
				out += param.getUTCMonth();
				if (param.getUTCDate() < 10)
					out += "0";
				out += param.getUTCDate() + "T";
				if (param.getUTCHours() < 10)
					out += "0";
				out += param.getUTCHours() + ":";
				if (param.getUTCMinutes() < 10)
					out += "0";
				out += param.getUTCMinutes() + ":";
				if (param.getUTCSeconds() < 10)
					out += "0";
				out += param.getUTCSeconds();
				out += "</dateTime.iso8601>";
				return out;
			} else { /* struct */
// 			alert("SUN CHI");
				out = "<struct>\n";
				for (var i in param) {
					out += "<member>";
					out += "<name>" + i + "</name>";
					out += "<value>" + xmlrpc.writeParam(param[i]) + "</value>";
					out += "</member>\n";
				}
				out += "</struct>\n";
				return out;
			}
	}
};

xmlrpc.parseResponse = function(dom) {
	var methResp = dom.childNodes[dom.childNodes.length - 1];
	if (methResp.nodeName != "methodResponse")
		throw "malformed <methodResponse>, got " + methResp.nodeName;
	
	var i=0;
	var params = methResp.childNodes[i++];
	if (params.nodeName == "fault")  {
		var fault = xmlrpc.parse(params.childNodes[0]);
		throw fault["faultString"];
	}
	while(params.nodeName == "#text") params = methResp.childNodes[i++];
	if (params.nodeName == "fault")
	  throw "Fault: "+params.childNodes[0].data;
	if (params.nodeName != "params")
		throw "malformed <params>, got <" + params.nodeName + ">";
	
	i=0;
	var param = params.childNodes[i++];
	while(param.nodeName == "#text") param = params.childNodes[i++];
	if (param.nodeName != "param")
		throw "malformed <param>, got <" + param.nodeName + ">";
	
	i=0;
	var value = param.childNodes[i++];
	while(value.nodeName == "#text") value = param.childNodes[i++];
	if (value.nodeName != "value")
		throw "malformed <value>, got <" + value.nodeName + ">";
	
	return xmlrpc.parse(value);
};

xmlrpc.parse = function(value) {
	if (value.nodeName != "value")
		throw "parser: expected <value> got <"+value.nodeName+"> "+value.childNodes.length;
// 	alert("value.childs: "+value.childNodes.length); // RRA

	var type = value.childNodes[0];
	if (type == null)
		throw "parser: expected <value> to have a child";
// 	alert("type:"+type.nodeName); // RRA
	switch (type.nodeName) {
		case "boolean":
			return type.childNodes[0].data == "1" ? true : false;
		case "i4":
		case "int":
			return parseInt(type.childNodes[0].data);
		case "double":
			return parseFloat(type.childNodes[0].data);
		case "#text": // Apache XML-RPC 2 doesn't wrap strings with <string>
			return type.data;
		case "string":
			return type.childNodes[0].data;
		case "array":
			var conta=0;
			var data = type.childNodes[conta++];
			while(data.nodeName=="#text") data = type.childNodes[conta++];
			var res = new Array(data.childNodes.length);
			var j=0;
			for (var i=0; i < data.childNodes.length; i++) {
				try {
					res[j] = xmlrpc.parse(data.childNodes[i]);
// 					alert("res["+j+"]: '"+res[j]+"'");
					j++;
				} catch(e) {
// 					alert(e);
				}
			}
			var ret = new Array(j);
			for(var i=0;i<j;i++) ret[i]=res[i];
// 			alert("ret: "+ret);
			return ret;
		case "struct":
			var members = type.childNodes;
			var res = {};
			for (var i=0; i < members.length; i++) {
				try {
				if(members[i].nodeName!='member') continue;
				var name = members[i].childNodes[0].childNodes[0].data;
				var value = xmlrpc.parse(members[i].childNodes[2]);
// 				var name = members[i].childNodes[0].childNodes[0].data;
// 				var value = xmlrpc.parse(members[i].childNodes[1]);
				res[name] = value;
// 				alert("res["+name+"]="+value);
				} catch(e) {
// 					alert("Eccezione - members["+i+"].nodeName="+members[i].nodeName + "\n" + e);
				}
			}
// 			alert("res: "+res);
			return res;
		case "dateTime.iso8601":
			var s = type.childNodes[0].data;
			var d = new Date();
			d.setUTCFullYear(s.substr(0, 4));
			d.setUTCMonth(parseInt(s.substr(4, 2)) - 1);
			d.setUTCDate(s.substr(6, 2));
			d.setUTCHours(s.substr(9, 2));
			d.setUTCMinutes(s.substr(12, 2));
			d.setUTCSeconds(s.substr(15, 2));
			return d;
		case "base64":
			return Base64.decode(type.childNodes[0].data);
		default:
			throw "parser: expected type, got <"+type.nodeName+">";
	}
}

var Base64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		input = Base64._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
		}
		return output;
	},
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = Base64._utf8_decode(output);
		return output;
	},
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}
