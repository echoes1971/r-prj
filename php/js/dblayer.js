/**
 * @copyright &copy; 2011 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dblayer.js $
 * @package rproject
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


/**
 * Class: ResultSet
 */
function ResultSet() {
	this.columnName = new Array();
	this.columnType = new Array();
	this.columnSize = new Array();
	this.righe = new Array();
	
	this.getNumColumns = function() { return this.columnName.length; }
	this.getNumRows = function() { return this.columnName.length>0 ? (this.righe.length / this.columnName.length) : 0; }
	
	this.getValue = function(row,col) { return this.righe[ row * this.columnName.length + col ]; }
	this.isNull = function(row,col) { return this.righe[ row * this.columnName.length + col ]==null; }
	this.getColumnName = function(col) { return this.columnName[col]; }
	this.getColumnIndex = function(columnName) { var ret=-1; for(var i=0; ret<0 && i<this.columnName.length; i++) { if(this.columnName[i]==columnName) ret=i; } return ret; }
	this.getColumnType = function(col) { return this.columnType[col]; }
	/*virtual string getValue(int row, string* columnName);
	virtual int getLength(int row, int column);
	virtual string (int i);
	virtual int getColumnSize(int i);
	virtual string getErrorMessage();
	bool hasErrors();
	virtual string getStatus();*/
	this.to_string = function() {
		var ret="ResultSet(\n";
		ret+=" meta=[";
		for(var i=0; i<this.columnName.length; i++) { ret += this.columnName[i]+": "+this.columnType[i]+","; }
		ret+="]\n";
		var num_righe=this.getNumRows();
		for(var r=0; r<num_righe; r++) {
			ret += " r["+r+"]=(";
			for(var col=0; col<this.columnName.length; col++) { ret+= this.getValue(r,col)+","; }
			ret +=")\n";
		}
		return ret+")";
	};
}


/**
 * Class: ForeignKey
 */
function ForeignKey(colonna_fk, tabella_riferita, colonna_riferita) {
	this.colonna_fk=colonna_fk;
	this.tabella_riferita=tabella_riferita;
	this.colonna_riferita=colonna_riferita;
}

/**
 * Class: DBEntity
 */
function DBEntity(dbename,tablename) {
	this.dbename = dbename || "DBEntity";
	this.tablename = tablename>'' ? tablename : '';
	this.keys={}; // nome=>tipo
	this._fks=new Array(); // Array of FKs
	this.dict={}; // Values
	
	this.getDBEName = function() { return this.dbename; }
	this.getTableName = function() { return this.tablename; }
	
	this.fromRS = function(rs,row) {
		var typenameIndex = rs.getColumnIndex('_typename');
		if(typenameIndex>0) this.dbename=rs.getValue(row,typenameIndex);
		for(var col=0; col<rs.getNumColumns(); col++) {
			var colName = rs.getColumnName(col);
			if(colName[0]=='_') continue;
			this.dict[colName]=rs.getValue(row,col);
		}
	};
	
	this.getValue = function(k) { try{ return this.dict[k]; }catch(e){ return null; } }
	this.setValue = function(k,v) { this.dict[k]=v; }
	this.getValues = function() { return this.dict; }
	this.setValues = function(_dict) { this.dict=_dict; }
	
	this.getKeys = function() { return this.keys; }
	this.getKeyNames = function() { var ret=Array(); for(var i in this.keys) ret.push(i); return ret; }
	
	this.getFK = function() { return this._fks; }
	this.getFKForTable = function(tablename) { var ret=new Array(); for(var i=0; i<this._fks.length; i++) { if(this._fks[i].tabella_riferita==tablename) ret.push(this._fks[i]); } return ret; }
	this.readFKFrom = function(dbe) {
		var fks = this.getFKForTable( dbe.getTableName() );
		for(var i=0; i<fks.length; i++) {
			var v = dbe.getValue( fks[i].colonna_riferita );
			if(v==null) continue;
			this.setValue(fks[i].colonna_riferita,v);
		}
	}
	this.writeFKTo = function(dbemaster) {
		var fks = this.getFKForTable( dbemaster.getTableName() );
		for(var i=0; i<fks.length; i++) {
			var v = this.getValue( fks[i].colonna_fk );
			if(v==null) continue;
			dbemaster.setValue(fks[i].colonna_fk,v);
		}
	}
	this.isFK = function(field_name, tabella_riferita) {
		var fks = this.getFK();
		if(tabella_riferita.length>0) fks = this.getFKForTable(tabella_riferita);
		var found=false;
		for(var i=0; !found && i<fks.length; i++) { found = ( fks[i].colonna_fk == field_name ); }
		return found;
	}
	this.cleanKeyFields = function() {
		var keyNames = this.getKeyNames();
		for(var i=0; i<keyNames.length; i++) this.setValue(keyNames[i],null);
	}
	this.isNew = function() {
		var not_empty=0;
		var keyNames = this.getKeyNames();
		for(var i=0; i<keyNames.length; i++) if(this.getValue(keyNames[i])!=null) not_empty++;
		return not_empty<keyNames.length;
	}
	
	this.to_string = function() {
		var ret = this.dbename+"(";
		for(var i in this.dict) {
			ret+=i+":"+this.dict[i]+",";
		}
		return ret+")";
	};
	
	// **** Virtuals ****
	this.createNewInstance = function() { return new DBEntity(this.dbename,this.tablename); }
	
	this._before_insert = function(dbmgr) {}
	this._after_insert = function(dbmgr) {}
	this._before_update = function(dbmgr) {}
	this._after_update = function(dbmgr) {}
	this._before_delete = function(dbmgr) {}
	this._after_delete = function(dbmgr) {}
	this._before_copy = function(dbmgr) {}
	this._after_copy = function(dbmgr) {}
}

/**
 * Class: DBConnection
 */
function DBConnection(connectionString,verbose) {
	this.connected=false;
	this.synchronous=false; // TODO gestire
	this.verbose=verbose;
	this.connectionString=connectionString;
	this.errorMessage='';
	this._rs_user=null;
	this._dbe_user=null;
	
	this.rs = null; // ResultSet di ritorno da una query
	this.dbe = null; // dbe returned by an insert or update
	this.dbelist = null; // list of dbe returned by a select or search
	
	this.getErrorMessage = function() { return this.errorMessage; };
	this.hasErrors = function() { return this.errorMessage>''; };
	
	this.setVerbose = function(b) { this.verbose=b; };
	this.isVerbose = function() { return this.verbose; };
	
	this.connect = function(on_connect_callback) {
		var myobj = this;
		var xmethod = 'ping';
		var params = [];
		var callback = function(ret) { myobj.connected=true; };
		var callErr = function(ret) { myobj.connected=false; alert('DBConnection Error: '+ret); };
		var callFinal = function() { if(on_connect_callback!=null) on_connect_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return false; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return true;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	};
	this.disconnect = function(on_disconnect_callback) { this.connected=false; if(on_disconnect_callback!=null) on_disconnect_callback(); };
	this.isConnected = function() { return this.connected; };
	this.reconnect = function() { this.disconnect(); this.connect(); };
	
	this.obj2resultset = function(obj) {
		if(obj.length!=2) {
			this.errorMessage=obj[0];
			return null;
		}
		var rs = new ResultSet();
		// Nomi e tipi
		var header = obj[1][0];
		for(var i in header) { rs.columnName.push(i); rs.columnType.push(typeof(header[i])); }
		// Righe
		var lista=obj[1];
		for(var r=0; r<lista.length; r++) {
			for(var col=0; col<rs.columnName.length; col++) { rs.righe.push( lista[r][rs.columnName[col]] ); }
		}
		return rs;
	}
	
	this.login = function(user,pwd,on_login_callback) {
		var myobj = this;
		var xmethod = 'login';
		var params = [user,pwd];
// 		var callback = function(ret) { myobj._rs_user=myobj.obj2resultset(ret); };
		var callback = function(ret) {
			myobj._rs_user=myobj.obj2resultset(ret);
			myobj._dbe_user = new DBEntity("DBEUser","users");
			myobj._dbe_user.fromRS(myobj._rs_user,0);
		};
		var callErr = function(ret) { myobj._rs_user=null; myobj._dbe_user=null; alert('Login Error: '+ret); };
		var callFinal = function() { if(on_login_callback!=null) on_login_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj._dbe_user;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	};
	this.getLoggedUser = function(on_my_callback) {
		var myobj = this;
		var xmethod = 'getLoggedUser';
		var params = []; //[user,pwd];
// 		var callback = function(ret) { myobj._rs_user=myobj.obj2resultset(ret); };
		var callback = function(ret) {
			myobj._rs_user=myobj.obj2resultset(ret);
			myobj._dbe_user = new DBEntity("DBEUser","users");
			myobj._dbe_user.fromRS(myobj._rs_user,0);
		};
		var callErr = function(ret) { myobj._rs_user=null; myobj._dbe_user=null; alert('getLoggedUser Error: '+ret); };
		var callFinal = function() { if(on_my_callback!=null) on_my_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
// 			alert(ret);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj._dbe_user;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	};
	
	this.execute = function(tablename,sql_string,on_execute_callback) {
		var myobj = this;
		var xmethod = 'selectAsArray';
		var params = [tablename,sql_string];
		var callback = function(ret) { myobj.rs=myobj.obj2resultset(ret); };
		var callErr = function(ret) { myobj.rs=null; alert('Execute Error: '+ret); };
		var callFinal = function() { if(on_execute_callback!=null) on_execute_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj.rs;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	};
	
	// **************** Proxy Connections: start. *********************
	// The proxy connections are used by DBMgr to execute the following methods
	this.isProxy = function() { return true; }
	this.Insert = function(dbe, on_insert_callback) {
		var myobj = this;
		var xmethod = 'insert';
		var params = [ new Array( dbe.dbename,dbe.getValues() ), ];
		var callback = function(ret) {
			myobj.rs=myobj.obj2resultset(ret);
			myobj.dbe = new DBEntity(dbe.dbename,dbe.tablename);
			myobj.dbe.fromRS(myobj.rs,0);
		};
		var callErr = function(ret) { myobj.rs=null; myobj.dbe=null; alert('Insert Error: '+ret); };
		var callFinal = function() { if(on_insert_callback!=null) on_insert_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) {
// 				callErr(ret);
				this.rs=null; this.dbe=null; alert('Insert Error: '+ret);
				return null;
			}; // FIXME farlo meglio
// 			callback(ret);
			this.rs=myobj.obj2resultset(ret);
			this.dbe = new DBEntity(dbe.dbename,dbe.tablename);
			this.dbe.fromRS(myobj.rs,0);
			callFinal();
			return this.dbe;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	}
	this.Update = function(dbe, on_finish_callback) {
		var myobj = this;
		var xmethod = 'update';
		var params = [ new Array( dbe.dbename,dbe.getValues() ), ];
		var callback = function(ret) {
			myobj.rs=myobj.obj2resultset(ret);
			myobj.dbe = new DBEntity("DBEntity","tablename");
			myobj.dbe.fromRS(myobj.rs,0);
		};
		var callErr = function(ret) { myobj.rs=null; myobj.dbe=null; alert('Update Error: '+ret); };
		var callFinal = function() { if(on_finish_callback!=null) on_finish_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj.dbe;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	}
	this.Delete = function(dbe, on_finish_callback) {
		var myobj = this;
		myobj.rs=null;
		myobj.dbe=null;
		var xmethod = 'delete';
		var params = [ new Array( dbe.dbename,dbe.getValues() ), ];
		var callback = function(ret) {
			myobj.rs=myobj.obj2resultset(ret);
			if(myobj.rs==null) {
				myobj.dbe = null;
			} else {
				myobj.dbe = new DBEntity("DBEntity","tablename");
				myobj.dbe.fromRS(myobj.rs,0);
			}
		};
		var callErr = function(ret) { myobj.rs=null; myobj.dbe=null; alert('Delete Error: '+ret); };
		var callFinal = function() { if(on_finish_callback!=null) on_finish_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj.dbe;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	}
	this.Select = function(dbename,tablename,searchString, on_finish_callback) {
// 		var dbename=dbe.dbename;
// 		var tablename=dbe.tablename;
		var myobj = this;
		myobj.rs=null;
		myobj.dbelist=new Array();
		var xmethod = 'select';
		var params = [ tablename,searchString ];
		var callback = function(ret) {
			myobj.rs=myobj.obj2resultset(ret);
			if(myobj.rs==null) {
				myobj.dbelist = null;
			} else {
				for(var r=0; r<myobj.rs.getNumRows(); r++) {
					dbe = new DBEntity(dbename,tablename);
					dbe.fromRS(myobj.rs,r);
					myobj.dbelist.push(dbe);
				}
			}
		};
		var callErr = function(ret) { myobj.rs=null; myobj.dbelist=null; alert('Select Error: '+ret); };
		var callFinal = function() { if(on_finish_callback!=null) on_finish_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj.dbelist;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	}
	this.Search = function( dbe, uselike, caseSensitive, orderBy, on_finish_callback) {
		var dbename=dbe.dbename;
		var tablename=dbe.tablename;
		var myobj = this;
		myobj.rs=null;
		myobj.dbelist=new Array();
		var xmethod = 'search';
		var params = [ new Array( dbe.dbename, dbe.getValues() ), uselike, caseSensitive, orderBy ];
		var callback = function(ret) {
/*			alert("ret.length: "+ret.length+"\n"
					+ret[0]+"\n"
					+ret[1]);*/
			myobj.rs=myobj.obj2resultset(ret);
// 			alert("myobj.rs: "+myobj.rs.getNumRows());
			if(myobj.rs==null) {
				myobj.dbelist = null;
			} else {
				for(var r=0; r<myobj.rs.getNumRows(); r++) {
					var dbe = new DBEntity(dbename,tablename);
					dbe.fromRS(myobj.rs,r);
					myobj.dbelist.push(dbe);
				}
			}
		};
		var callErr = function(ret) { myobj.rs=null; myobj.dbelist=null; alert('Select Error: '+ret); };
		var callFinal = function() { if(on_finish_callback!=null) on_finish_callback(); };
		if(this.synchronous) {
			var ret = xmlrpcSync(this.connectionString,xmethod,params);
			if(ret==null) { callErr(ret); return null; }; // FIXME farlo meglio
			callback(ret);
			callFinal();
			return myobj.dbelist;
		} else
			var server = xmlrpc(this.connectionString,xmethod,params,callback,callErr,callFinal);
	}
	// **************** Proxy Connections: end. *********************
	
	/* TODO ?
	virtual bool flush(); // Force the write buffer to be written (or at least try)
	virtual string* escapeString(string* s);
	virtual string escapeString(string s);
	virtual string quoteDate(string s);

	// Ritorna il numero di colonne di una tabella
	virtual int getColumnSize(string* relname);
	// @param column 1..n
	virtual string getColumnName(string* relname, int column);
	// Ritorna il numero delle colonne chiave della tabella  [1..n]
	virtual IntegerVector getKeys(string* relname);
	// Ritorna il numero delle colonne chiave esterne della tabella [1..n]
	virtual IntegerVector getForeignKeys(string* relname);
	// Ritorna i nomi delle colonne chiave della tabella
	StringVector getKeysNames(string* relname);
	
	virtual string getFormSchema(string language="python");
	virtual string getDBSchema(string language="python");
	*/
}

/**
 * Class: DBMgr
 */
function DBMgr(_connection, verbose) {
	this.con=_connection;
	this.verbose=verbose;
	this.dbeFactory=null;
	this._schema="";
	
	this.getErrorMessage = function() { return this.con.getErrorMessage(); };
	this.hasErrors = function() { return this.con.hasErrors(); };
	
	this.setSynchronous = function(b) { this.con.synchronous=b; };
	this.isSynchronous = function() { return this.con.synchronous; };
	
	this.connect = function(on_connect_callback) {
// 		var test_cb = function() { alert("SUN CHI"); };
		this.con.connect(); //test_cb);
		if(on_connect_callback!=null) on_connect_callback();
	};
	this.disconnect = function(on_my_callback) { this.con.disconnect(); if(on_my_callback!=null) on_my_callback(); };
	this.isConnected = function() { return this.con.isConnected(); };
	this.reconnect = function() { this.con.reconnect(); };
	
	this.login = function(user,pwd,on_my_callback) {
		this.con.login(user,pwd);
		if(on_my_callback!=null) on_my_callback();
		return this.con._dbe_user;
	};
	this.getLoggedUser = function(on_my_callback) {
		if(this.con._dbe_user==null) this.con.getLoggedUser();
		if(on_my_callback!=null) on_my_callback();
		return this.con._dbe_user;
	};
	
	this.execute = function(tablename,sql_string,on_my_callback) {
		this.con.execute(tablename,sql_string);
		if(on_my_callback!=null) on_my_callback();
		return this.con.rs;
	};
	
	// **************** Proxy Connections: start. *********************
	this.Insert = function(dbe, on_my_callback) {
		this.con.Insert(dbe);
		if(on_my_callback!=null) on_my_callback();
		return this.con.dbe;
	}
	this.Update = function(dbe, on_my_callback) {
		this.con.Update(dbe);
		if(on_my_callback!=null) on_my_callback();
		return this.con.dbe;
	}
	this.Delete = function(dbe, on_my_callback) {
		this.con.Delete(dbe);
		if(on_my_callback!=null) on_my_callback();
		return this.con.dbe;
	}
	this.Select = function(dbename,tablename,searchString, on_my_callback) {
		this.con.Select(dbename,tablename,searchString);
		if(on_my_callback!=null) on_my_callback();
		return this.con.dbelist;
	}
	this.Search = function( dbe, uselike, caseSensitive, orderBy, on_my_callback) {
		this.con.Search(dbe,uselike,caseSensitive,orderBy);
		if(on_my_callback!=null) on_my_callback();
		return this.con.dbelist;
	}
	// **************** Proxy Connections: end. *********************
	
}

