<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: dblayer.php $
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

/** Mappa una foreign key di una tabella */
class ForeignKey {
    var $colonna_fk;
    var $tabella_riferita;
    var $colonna_riferita;
    /**
     * @param colonna_fk foreign key column
     * @param tabella_riferita referenced table
     * @param colonna_riferita referenced column
     */
    function ForeignKey($colonna_fk,$tabella_riferita,$colonna_riferita) {
        $this->colonna_fk=$colonna_fk;
        $this->tabella_riferita=$tabella_riferita;
        $this->colonna_riferita=$colonna_riferita;
    }
    function to_string() {
        return "FK(".$this->colonna_fk." => ".$this->tabella_riferita."[".$this->colonna_riferita."] )";
    }
}

class DBEntity {
    var $_typeName =  'DBEntity';
    var $_tableName;
    var $_dict;
    var $_keys;
    var $_fk;
    var $_columns;
    
    function DBEntity($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=array()) {
        if($tablename!=null) { $this->_tablename = $tablename; }
        if($attrs!=null) {
            $this->_dict = $attrs;
        } else if($names!=null && $values!=null) {
            $mydict = array();
            for($i=0; $i<count($names) ; $i++) {
                $mydict[$names[$i]] = $values[$i];
            }
            $this->_dict = $mydict;
        }
        if($keys!=null) { $this->_keys=$keys; }
        $this->_fk = array();
        $this->_columns = $columns;
    }
    /** 2010.07.26: ritorna il tipo della colonna */
    function getColumnType($column_name) {
        if(array_key_exists($column_name,$this->_columns)) {
            $cerca=$this->_columns[$column_name][0];
            if(strpos($cerca,'(')>0) {
                $_tmp=preg_split("/\(/",$cerca);
                $cerca=$_tmp[0];
            }
            switch($cerca) {
                case 'char':
                case 'varchar':
                case 'text':
                    return 'string';
                default:
                    return $cerca;
            }
        } else
            return null;
    }
    function getColumns() { return $this->_columns; }
    static function dbeType2dbType($dbetype) {
        $ret = $dbetype;
        switch($dbetype) {
            case 'int':
                $ret='int(11)';
                break;
            case 'uuid':
                $ret="varchar(16)";
                break;
            default:
                break;
        }
        return $ret;
    }
    static function dbType2dbeType($dbetype) {
        $ret = $dbetype;
        switch($dbetype) {
            case 'int(11)':
                $ret='int';
                break;
            case "varchar(16)":
                $ret='uuid';
                break;
            default:
                break;
        }
        return $ret;
    }
    static function dbConstraints2dbeConstraints($def) {
        $constraints = array();
        if($def['Null']=='NO') $constraints[]="not null";
        if($def['Default']!==null) {
            $apice="'";
            switch($def['Type']) {
                case 'int(11)':
                case 'float':
                    $apice='';
            }
            $constraints[] = "default $apice".$def['Default'].$apice;
        } elseif($def['Default']===null && $def['Null']=='YES') {
            $constraints[] = "default null";
        }
        $constraints = (count($constraints)>0 ? implode(" ",$constraints) : '');
        return $constraints;
    }
    static function dbColumnDefinition2dbeColumnDefinition($def) {
        $constraints = DBEntity::dbConstraints2dbeConstraints($def);
        return "'$col'=>array('".DBEntity::dbType2dbeType($def['Type'])."',"
                .($constraints>'' ? $constraints : '')
                ."),\n";
    }

    /** Ritorna il nome della classe */
    function getTypeName() { return $this->_typeName; }
    /** Ritorna lo schema di appartenenza della tabella */
    function getSchemaName() {    return null;    }
    /** Ritorna il nome della tabella */
    function getTableName() {    return $this->_tableName;    }
    /** Ritorna il nome delle colonne per la orderby predefinita */
    function getOrderBy() { return array_keys($this->getKeys()); }
    function getOrderByString() { return implode(",",$this->getOrderBy()); }
    /** Ritorna un dizionario { 'nome_colonna':'tipo_colonna' } */
    function getKeys() {    return $this->_keys;    }
    /**    Ritorna un array di <b>ForeignKey</b>    */
    function getFK() {    return $this->_fk; }
    /** se esiste, ritorna un array con le FK verso la data tabella */
    function getFKForTable($tablename) {
        $ret = array();
        foreach($this->getFK() as $myfk) {
            if($myfk->tabella_riferita==$tablename) {
                $ret[] = $myfk;
            }
        }
        return $ret;
    }
    /** se esiste, ritorna la prima definizione della FK per la colonna in argomento */
    function getFKDefinition($fk_column_name) {
        $ret = null;
        $fks = $this->getFK();
        foreach($fks as $myfk) {
            if($myfk->colonna_fk==$fk_column_name) {
                $ret = $myfk;
                break;
            }
        }
        return $ret;
    }
    /**    Reads the content of the referenced columns in the referenced table mapped in the given dbe    */
    function readFKFrom($dbe) {
        $fks = $this->getFKForTable($dbe->getTableName());
        foreach ($fks as $f) {
            $v = $dbe->getValue($f->colonna_riferita);
            if(is_integer($v)) { $v = int($v); }
            if($v) {
                $this->setValue($f->colonna_fk, $v);
            }
        }
    }
    /**    Writes the content of the referenced columns in the referenced table mapped in the given dbe    */
    function writeFKTo($masterdbe) {
        $fks = $this->getFKForTable($masterdbe->getTableName());
        foreach($fks as $f) {
            $v = $this->getValue($f->colonna_fk);
            if($v!==null && $v!=='') {
                $masterdbe->setValue($f->colonna_riferita, $v);
            }
        }
        return $masterdbe;
    }

    function isPrimaryKey($field_name) {
        return in_array($field_name, array_keys($this->getKeys()));
    }

    /**
     *    
     */
    function isFK($field_name) {
        // TEST
        $myfks = $this->getFK();
        $trovato = false;
        if(is_array($myfks))
            foreach($myfks as $myfk) {
                if($field_name==$myfk->colonna_fk) {
                    $trovato=true;
                    break;
                }
            }
        return $trovato;
    }

    /** Per entita' specializzate (ie mapping di una tabella) fare l'overload di questo metodo facendogli ritornare i nomi delle colonne.    */
    function getNames() {
        if($this->_dict!=null) {
            return array_keys($this->_dict);
        } else {
            return array();
        }
    }
    /** Ritorna un dizionario nome: valore della DBE */
    function getValuesDictionary() {
        $ret=array();
        foreach($this->_dict as $k=>$v) {
            $tipo_v=$this->getColumnType($k);
            switch($tipo_v) {
                case 'uuid':
                    $ret[$k]=self::uuid2hex($v);
                    break;
                default:
                    $ret[$k]=$v;
                    break;
            }
        }
        return $ret;
    }
    function setValuesDictionary($aDict) {
        $this->_dict = $aDict;
    }
    
    function getKeysDictionary() {
        $ret=array();
        $chiavi = array_keys($this->getKeys());
        foreach($chiavi as $chiave) {
            $ret[$chiave] = $this->getValue($chiave);
        }
        return $ret;
    }
    
    /**    Elimino tutti i campi chiave presenti nella DBE
        Metodo di appoggio per la <b>copy</b> del DBMgr    */
    function cleanKeyFields() {
        // TEST
        $chiavi = array_keys($this->getKeys());
        foreach($chiavi as $k) {
            $this->setValue($k, null);
        }
    }
    
    /** Controlle se tutte le chiavi sono a null */
    function isNew() {
        $chiavi = array_keys($this->getKeys());
        foreach($chiavi as $k) {
            if($this->getValue($k)!==null) return false;
        }
        return true;
    }
    
    static function uuid2hex($str) {
        if($str===null) return $str;
        $str_len = strlen($str);
        if($str_len<4) return $str;
        if(substr($str,0,4)=='uuid')
            return $str;
        $hex = "";
        $i = 0;
        do {
            $hex .= dechex(ord($str{$i}));
            $i++;
        } while ($i<$str_len);
        return 'uuid'.$hex;
    }
    static function hex2uuid($a_str) {
        if(substr($a_str,0,4)!='uuid')
            return $a_str;
        $str=substr($a_str,4);
        $bin = "";
        $i = 0;
        $str_len = strlen($str);
        do {
            $bin .= chr(hexdec($str{$i}.$str{($i + 1)}));
            $i += 2;
        } while ($i < $str_len);
        return $bin;
    }
    
    function getValue($chiave) {
        if(is_array($this->_dict) && array_key_exists($chiave,$this->_dict)) {
            $tipo_v=$this->getColumnType($chiave);
            switch($tipo_v) {
                case 'uuid':
                    return self::uuid2hex($this->_dict[ $chiave ]);
                    break;
                default:
                    return $this->_dict[ $chiave ];
                    break;
            }
        } else {
            return null;
        }
    }
    function setValue($chiave,$valore) {
        $tipo_v=$this->getColumnType($chiave);
        switch($tipo_v) {
            case 'uuid':
                $this->_dict[ $chiave ] = self::hex2uuid($valore);
                break;
            default:
                $this->_dict[ $chiave ] = $valore;
                break;
        }
    }
    
    function to_string() {
        $ret = $this->_typeName;    $ret .="(";
        if($this->_dict!=null) {
            foreach ($this->_dict as $chiave => $valore) {
                $ret .= "$chiave: $valore; ";
            }
        }
        $ret .= ")";
        return $ret;
    }
    
    function _before_insert(&$dbmgr) {}
    function _after_insert(&$dbmgr) {}
    function _before_update(&$dbmgr) {}
    function _after_update(&$dbmgr) {}
    function _before_delete(&$dbmgr) {}
    function _after_delete(&$dbmgr) {}

    /**    Ritorna una stringa chiave1=valore1&...&chiaven=valoren */
    function getCGIKeysCondition($prefix="field_") {
        $mychiavi = is_array($this->getKeys()) ? array_keys($this->getKeys()) : array();
        $clausole = array();    $clausole_index=0;
        for($i=0; $i<count($mychiavi) ; $i++) {
            $k = $mychiavi[ $i ];
            $v = $this->getValue($k);
            $clausole[ $clausole_index++ ] = $prefix.$k."=".urlencode($v);
        }
        return join($clausole, "&");
    }
    /**    Ritorna una stringa con l'hash (sha1) dei <b>valori</b> della chiave */
    function getKeyAsHash() {
        $mychiavi = is_array($this->getKeys()) ? array_keys($this->getKeys()) : array();
        $tmp = "";
        for($i=0; $i<count($mychiavi) ; $i++) {
            $tmp .= $this->getValue($mychiavi[ $i ]);
        }
        return sha1($tmp);
    }
    
//     def readCGIKeysCondition(self, request):
//         """Legge le condizioni inserite nel CGI per le chiavi di questa DBE"""
//         chiavi = self.getKeys()
//         for k in chiavi:
//             v = None
//             v = request.form[k]
//             if v: setattr(self, k, v)
//         pass
//
    /**
     *    Data una dbe_master ed una classe di detail, costruisce le clausole CGI
     *    per le foreign keys della figlia.
    */
    function getFKCGIConditionFromMaster(&$dbe_master) {
        $ret = array();
        $fks = $this->getFKForTable($dbe_master->getTableName());
        foreach($fks as $f) {
            $v = $dbe_master->getValue($f->colonna_riferita);
            if($v!==null && $v!=='' && $v!==0) {
                $ret[] = "field_" . $f->colonna_fk . "=" . urlencode($v);
            }
        }
        return join($ret, "&");
    }
}

/**
 * Modella le associazioni N-M
 *
 * La prima FK stabilisce la tabella FROM
 * La seconda FK stabilisce la tabella TO
 */
class DBAssociation extends DBEntity {
    function DBAssociation($tablename=null, $names=null, $values=null, $attrs=null, $keys=null, $columns=array()) {
        $this->DBEntity($tablename, $names, $values, $attrs, $keys, $columns);
    }
    
    function getFromTableName() { return $this->_fk[0]->tabella_riferita; }
    function getToTableName() { return $this->_fk[1]->tabella_riferita; }
}

/**
 * Returns the correct class for the given tablename
 */
class DBEFactory {
    var $verbose;
    var $classname2type;
    var $tablename2type;
    function DBEFactory($verbose = 0) {
        $this->verbose=$verbose;
        $this->classname2type=array("default"=>"DBEntity",);
        $this->tablename2type=array("default"=>"DBEntity",);
    }
    
    function register($aClassName) {
        eval("\$istanza = new $aClassName();");
        $this->classname2type[$aClassName]=$aClassName;
        $this->tablename2type[ $istanza->getTableName() ]=$aClassName;
    }
    
    function getRegisteredTypes() {
        return $this->tablename2type;
    }
    
    function getAllClassnames() {
        return array_keys($this->classname2type);
    }
    
    function getInstance($aClassname, $aNames=null, $aValues=null, $aAttrs=null) {
        if(array_key_exists($aClassname,$this->classname2type)) {
            eval("\$ret = new ".$this->classname2type[$aClassname]."(null, \$aNames, \$aValues , \$aAttrs, null);");
            return $ret;
        } else {
            return new DBEntity(null, $aNames, $aValues , $aAttrs, null);
        }
    }
    
    function getInstanceByTableName($aTableName, $aNames=null, $aValues=null, $aAttrs=null) {
        if(array_key_exists($aTableName,$this->tablename2type)) {
            eval("\$ret = new ".$this->tablename2type[$aTableName]."(null, \$aNames, \$aValues , \$aAttrs, null);");
            return $ret;
        } else
            return new DBEntity($aTableName, $aNames, $aValues , null, null);
    }
}

class DBConnectionProvider {
    protected $conn;
    protected $_server;
    protected $_user;
    protected $_pwd;
    protected $_dbname;
    protected $_schema;
    protected $_verbose;
    
    function __construct($server, $user, $pwd, $dbname, $schema, $verbose=false) {
        $this->_server = $server; $this->_user = $user;
        $this->_pwd = $pwd; $this->_dbname = $dbname;
        $this->_schema = $schema;
        $this->conn = null;
        $this->_verbose = $verbose;
    }
    
    public function __destruct() {}
    
    function setVerbose($b) { $this->_verbose = $b; }
    
    function getColumnsForTable($tablename) { return -1; }
    function getColumnName($tablename, $num_column) { return -1; }
    function getColumnSize($tablename) { return -1; }
    function getKeys($tablename) { return array("TODO"); }
    function getForeignKeys($tablename) { return array("TODO"); }
    
    function db_query($_query) { return false; }
    function db_error() { return ''; }
    function db_fetch_array($_p) { return false; }
    function db_escape_string($_p) { return $_p; }
    function db_free_result(&$r) { return false; }
    function db_num_rows($r) { return -1; }
    function _description2names($_desc) { return array(); }
    
    function connect() { return; }
    function disconnect() { return; }
    function isConnected() { return false; }
}
class MYConnectionProvider extends DBConnectionProvider {
    function __construct($server, $user, $pwd, $dbname, $schema, $verbose=false) {
        parent::__construct($server, $user, $pwd, $dbname, $schema);
    }

    function getColumnsForTable($tablename) {
        $ret=array();
        ob_start();
        $result = mysql_query("SHOW COLUMNS FROM $tablename",$this->conn);
        $messaggi = ob_get_contents();
        ob_end_clean();
        if($this->_verbose) { echo "MYConnectionProvider.getColumnsForTable: $messaggi<br />\n"; }
        if($result===false) {
            if($this->_verbose) echo 'Could not run query: ' . mysql_error();
            return $ret;
        }
        if(mysql_num_rows($result) > 0) {
            $colonna=1;
            while ($row = mysql_fetch_assoc($result)) {
                $ret[ $row["Field"] ]=$row;
            }
        }
        if($this->_verbose) echo "MYConnectionProvider.getColumnsForTable: mysql_num_rows(result)=".$ret."<br/>\n";
        return $ret;
    }
    function getColumnName($tablename, $num_column) {
        $ret="";
        $result = mysql_query("SHOW COLUMNS FROM $tablename",$this->conn);
        if($result===false) {
            echo 'MYConnectionProvider.getColumnName: Could not run query ' . mysql_error();
            return $ret;
        }
        if(mysql_num_rows($result) > 0) {
            $colonna=1;
            while ($row = mysql_fetch_assoc($result)) {
                if($colonna==$num_column) {
                    $ret=$row["Field"];
                    break;
                }
                $colonna++;
            }
        }
        echo "MYConnectionProvider.getColumnName: mysql_num_rows(result): ".$ret."\n";
        return $ret;
    }
    function getColumnSize($tablename) {
        $ret=-1;
        $result = mysql_query("SHOW COLUMNS FROM $tablename",$this->conn);
        if($result===false) {
            echo 'MYConnectionProvider.getColumnSize: Could not run query: ' . mysql_error();
            return $ret;
        }
        $ret = mysql_num_rows($result);
        echo "MYConnectionProvider.getColumnSize: mysql_num_rows(result): ".$ret."\n";
        return $ret;
    }
    function getKeys($tablename) {
        $ret=array();
        $result = mysql_query("SHOW COLUMNS FROM $tablename",$this->conn);
        if($result===false) {
            echo 'MYConnectionProvider.getKeys: Could not run query: ' . mysql_error();
            return $ret;
        }
        if(mysql_num_rows($result) > 0) {
            $colonna=1;
            while ($row = mysql_fetch_assoc($result)) {
                if($row["Key"]=="PRI")
                    $ret[]=$colonna;
                $colonna++;
            }
        }
        return $ret;
    }
    function getForeignKeys($tablename) {
        $ret=array();
        $result = mysql_query("SHOW COLUMNS FROM $tablename",$this->conn);
        if($result===false) {
            echo 'MYConnectionProvider.getForeignKeys: Could not run query: ' . mysql_error();
            return $ret;
        }
        if(mysql_num_rows($result) > 0) {
            $colonna=0;
            while ($row = mysql_fetch_assoc($result)) {
                if($row["Key"]=="MUL")
                    $ret[]=$colonna;
                $colonna++;
            }
        }
        return $ret;
    }
    function db_query($_query) { return mysql_query($_query,$this->conn); }
    function db_error() { return mysql_error(); }
    function db_fetch_array($_p) { return mysql_fetch_array($_p); }
    //function db_escape_string($_p) { return str_replace("\\\\\\\"","\"", str_replace("\\\\\\'","''",mysql_real_escape_string($_p))); }
    function db_escape_string($_p) {
        return str_replace("\\\\\\\"","\"", str_replace("\\\\\\'","''",$_p));
    }
    function db_free_result(&$r) { return mysql_free_result($r); }
    function db_num_rows($r) { return mysql_num_rows($r); }
    function _description2names($_desc) {
        $ret = array();
        $num_fields = $_desc===false ? 0 : mysql_num_fields($_desc);
        for($i=0; $i<$num_fields; $i++) {
            $_tmp = mysql_field_name($_desc, $i);
            $ret[ $i ] = $_tmp;
        }
        return $ret;
    }
    function connect() {
        if($this->isConnected()) return;
        ob_start();
        $this->conn = mysql_connect($this->_server, $this->_user, $this->_pwd);
        mysql_select_db($this->_dbname, $this->conn);
        $messaggi = ob_get_contents();
        ob_end_clean();
        if($this->_verbose) {
            echo "MYConnectionProvider.connect: ".$this->_server.":".$this->_user.":".$this->_pwd.":".$this->_dbname." $messaggi<br />\n";
            echo "MYConnectionProvider.connect: ".$this->conn."<br />\n";
        }
    }
    function disconnect() {
        if($this->isConnected()) {
            mysql_close($this->conn);
        }
        $this->conn=null;
    }
    function isConnected() {
        $ret = $this->conn!==null
                && $this->conn!==false
                && !is_int($this->conn)
                && mysql_ping($this->conn)
                ;
        //echo "isConnected:$ret<br/>";
        return $ret;
    }
}
/**
 * TODO almost all
 */
class PGConnectionProvider extends DBConnectionProvider {
//     function __construct($server, $user, $pwd, $dbname, $schema, $verbose=false) {
//         parent::__construct($server, $user, $pwd, $dbname, $schema);
//     }
    function db_query($_query) { return pg_query($this->conn, $_query); }
    function db_error() { return "Errore DB"; /* pg_error(); */ }
    function db_fetch_array($_p) { return pg_fetch_array($_p); }
    function db_escape_string($_p) { return pg_escape_string($_p); }
    function db_free_result(&$r) { return pg_free_result($r); }
}
class SQLiteConnectionProvider extends DBConnectionProvider {
    function __construct($server, $user, $pwd, $dbname, $schema, $verbose=false) {
        parent::__construct($server, $user, $pwd, $dbname, $schema);
    }
    function getColumnsForTable($tablename) { return -1; }
    function getColumnName($tablename, $num_column) { return -1; }
    function getColumnSize($tablename) { return -1; }
    function getKeys($tablename) { return array("TODO"); }
    function getForeignKeys($tablename) { return array("TODO"); }
    
    function db_query($_query) { return sqlite_query($this->conn,$_query); }
    function db_error() { return sqlite_error_string(sqlite_last_error()); }
    function db_fetch_array($_p) { return sqlite_fetch_array($_p); }
    function db_escape_string($_p) { return sqlite_escape_string($_p); }
    function db_free_result(&$r) { return false; }
    function db_num_rows($r) { return sqlite_num_rows($r); }
    function _description2names($_desc) {
        $ret = array();
        $num_fields = $_desc===false ? 0 : sqlite_num_fields($_desc);
        for($i=0; $i<$num_fields; $i++) {
            $_tmp = sqlite_field_name($_desc, $i);
            $ret[ $i ] = $_tmp;
        }
        return $ret;
    }
    
    function connect() {
        if($this->isConnected()) return;
//         ob_start();
// $handle = new SQLite3($this->_dbname);
// $dbh = new PDO('sqlite:'.$this->_dbname);
        $this->conn = sqlite3::open($this->_dbname);
        $this->conn = sqlite_open($this->_dbname);
//         $messaggi = ob_get_contents();
//         ob_end_clean();
        if($this->_verbose) { echo "SQLiteConnectionProvider.connect: ".$this->_server.":".$this->_user.":".$this->_pwd.":".$this->_dbname." $messaggi<br />\n"; }
    }
    function disconnect() { sqlite_close($this->conn); $this->conn=null; return; }
    function isConnected() { return $this->conn!==null; }
}

/**
 * (Super)Classe generica per il DB.
 * Gestisce la connessione ed implementa il wrapping delle funzionalità di accesso ai DB: query, fetch_array, escape, etc.
 */
class DBMgr {
    protected $_verbose = false;
    
    protected $conn; // @deprecated
    protected $connProvider;
    protected $_server;
    protected $_user;
    protected $_pwd;
    protected $_dbname;
    protected $_schema;
    protected $_factory;
    
    /** Logged user */
    public $dbeuser;
    /** Logged user's groups */
    public $user_groups_list;
    
    /**
     * DBMgr
     * @param server
     * @param user
     * @param pwd
     * @param dbname
     * @param schema
     * @param aDBEFactory
     * @param dbeuser utente loggato
     * @param user_groups_list
     */
    function DBMgr($server, $user, $pwd, $dbname, $schema, $aDBEFactory=null,$dbeuser=null, $user_groups_list=array()) {
        $this->_server = $server; $this->_user = $user;
        $this->_pwd = $pwd; $this->_dbname = $dbname;
        $this->_schema = $schema;
        $this->conn = null;
        $this->connProvider = null;
        $myeval = "\$this->connProvider = new ".$GLOBALS['db_connection_provider']."(\$server, \$user, \$pwd, \$dbname, \$schema);\n";
//         user_error("myeval: $myeval");
        eval($myeval);
        $this->_factory = $aDBEFactory;
        
        $this->dbeuser = $dbeuser;
        $this->user_groups_list = $user_groups_list;
    }
    
    function setConnection($_conn) { $this->conn = $_conn; }
    function getConnection() { return $this->conn; }
    function setConnectionProvider($_conn) { $this->connProvider = $_conn; }
    
    /** sed -i '' 's/_verbose=\(.*\);/setVerbose(\1);/g' *.php*/
    function setVerbose($b) { $this->_verbose = $b; $this->connProvider->setVerbose($b); }
    
    /** Ritorna il DBE Factory    */
    function getFactory() { return $this->_factory; }
    function getInstance($aClassname, $aNames=null, $aValues=null, $aAttrs=null) { return $this->_factory->getInstance($aClassname,$aNames,$aValues,$aAttrs); }
    function getInstanceByTableName($aTableName, $aNames=null, $aValues=null, $aAttrs=null) { return $this->_factory->getInstanceByTableName($aTableName,$aNames,$aValues,$aAttrs); }
    
    function getDBEUser() { return $this->dbeuser; }
    function setDBEUser($dbeuser) { $this->dbeuser = $dbeuser; }
    
    function getUserGroupsList() { return $this->user_groups_list; }
    function setUserGroupsList($user_groups_list) { $this->user_groups_list = $user_groups_list; }
    /** The logged user has the requested group_id ? */
    function hasGroup($group_id) { return in_array(DBEntity::uuid2hex($group_id),$this->user_groups_list); }
    function addGroup($group_id) { if(!in_array($group_id,$this->user_groups_list)) $this->user_groups_list[]=$group_id; }
    
    function getColumnsForTable($tablename) { $this->connect(); return $this->connProvider->getColumnsForTable($tablename); }
    function getColumnName($tablename, $num_column) { $this->connect(); return $this->connProvider->getColumnName($tablename, $num_column); }
    function getColumnSize($tablename) { $this->connect(); return $this->connProvider->getColumnSize($tablename); }
    function getKeys($tablename) { $this->connect(); return $this->connProvider->getKeys($tablename); }
    function getForeignKeys($tablename) { $this->connect(); return $this->connProvider->getForeignKeys($tablename); }

    function db_query($_query) { return $this->connProvider->db_query($_query); }
    function db_error() { return $this->connProvider->db_error(); }
    function db_fetch_array($_p) { return $this->connProvider->db_fetch_array($_p); }
    function db_escape_string($_p) { return $this->connProvider->db_escape_string($_p); }
    function db_free_result(&$r) { return $this->connProvider->db_free_result($r); }

    /**    Converte la description del resultset fornita dalle api DB in un array di nomi    */
    private function _description2names($_desc) { return $this->connProvider->_description2names($_desc); }

    function connect() { $this->connProvider->connect(); }
    function disconnect() { return $this->connProvider->disconnect(); }
    function isConnected() { return $this->connProvider->isConnected(); }
    
    /** Installed db version. */
    function db_version() {
        ob_start();
        $cerca = new DBEDBVersion();
        $ris = $this->search($cerca,1);
        $messaggi = ob_get_contents();
        ob_end_clean();
        if($this->_verbose) { echo "DBMgr.db_version: $messaggi<br />\n"; }
        return count($ris)==1 ? $ris[0]->version() : 0;
    }
    
    function _buildKeysCondition($dbe) {
        $ret = array();
        $chiavi = $dbe->getKeys();
        foreach($chiavi as $chiave => $tipo) {
            $valore = $dbe->getValue($chiave);
            $tipo_v = $dbe->getColumnType($chiave);
            if($tipo_v===null) $tipo_v=gettype($valore);
            if($tipo=='uuid' || $tipo_v=='uuid') {
                $ret[ count($ret) ] = "$chiave='".DBEntity::hex2uuid($valore)."'";
            } elseif($tipo=='number') {
                $ret[ count($ret) ] = "$chiave=$valore";
            } else {
                $ret[ count($ret) ] = "$chiave='$valore'";
            }
        }
        return join(' AND ', $ret);
    }
    
    /** RRA: in mysql non esistono schemi, questo e' un'escamotage per emulare l'esistenza di schemi all'interno dello stesso DB */
    function buildTableName($dbe) {
        return $this->_buildTableName($dbe);
    }
    function _buildTableName($dbe) {
        $tablename='';
        $__schema = $dbe->getSchemaName();
        if($__schema===null) $__schema = $this->_schema;
        if($__schema!==null) {
            // FIXME use the new dbconnectionprovider to do this
/*            if($this->tipo_dbms=='PG') {
                $tablename = $dbe->getTableName();
            } else if($this->tipo_dbms=='MYSQL') {
*/                // RRA: in mysql non esistono schemi, questo e' un'escamotage per emulare l'esistenza di schemi
                //            all'interno dello stesso DB
                $tablename = $__schema . ($__schema>''?'_':'') . $dbe->getTableName();
//            }
        }
        return $tablename;
    }
    function getSchema() { return $this->_schema; }

    function _buildInsertString($dbe) {
        $nomicampi = $dbe->getNames();
        $tmpnomi = array();
        $tmpvalori = array();
        foreach($nomicampi as $nomeCampo) {
            $v = $dbe->getValue($nomeCampo);
            if($v===null) { continue; }
            $tmpnomi[ count($tmpnomi) ] = $nomeCampo;
            $tipo_v = $dbe->getColumnType($nomeCampo);
            if($tipo_v===null) $tipo_v=gettype($v);
            if($tipo_v=='string' || $tipo_v=='datetime' || $tipo_v=='date' || $tipo_v=='time') {
                if($tipo_v=='datetime' && strlen($v)==8) $v="0000-00-00 ".$v;
                $tmpvalori[ count($tmpvalori) ] = "'".$this->db_escape_string($v)."'";
            } elseif($tipo_v=='uuid') {
                $tmpvalori[ count($tmpvalori) ] = "'".DBEntity::hex2uuid($v)."'";
            } elseif($tipo_v=='int') {
                $tmpvalori[ count($tmpvalori) ] = intval($v);
            } else {
                $tmpvalori[ count($tmpvalori) ] = "$v";
            }
        }
        $mytablename = $this->_buildTableName($dbe);
        $mynomi = join(", ", $tmpnomi);
        $myvalori = join(", ", $tmpvalori);
        $query = "insert into $mytablename ( $mynomi ) values ( $myvalori )";
        return $query;
    }
    
    function _buildUpdateString($dbe) {
        $nomicampi = $dbe->getNames();
        $chiavi = $dbe->getKeys();
        $setstring = array();
        foreach($nomicampi as $nomeCampo) {
            $v = $dbe->getValue($nomeCampo);
            $campoEChiave = array_search($nomeCampo, $chiavi);
            if($campoEChiave==null || $campoEChiave==false) {
                if($v===null) {
                    //$setstring[ count($setstring) ] = "$nomeCampo IS null";
                } else {
                    $tipo_v = $dbe->getColumnType($nomeCampo);
                    if($tipo_v===null) $tipo_v=gettype($v);
                    if($tipo_v=='string' || $tipo_v=='datetime' || $tipo_v=='date' || $tipo_v=='time') {
                        if($tipo_v=='datetime' && strlen($v)==8) $v="0000-00-00 ".$v;
                        $setstring[ count($setstring) ] = "$nomeCampo='".$this->db_escape_string($v)."'";
                    } elseif($tipo_v=='uuid') {
                        $setstring[ count($setstring) ] = "$nomeCampo='".DBEntity::hex2uuid($v)."'";
                    } else {
                        $setstring[ count($setstring) ] = "$nomeCampo=$v";
                    }
                }
            }
        }
        $mytablename = $this->_buildTableName($dbe);
        $myvalori = join(", ", $setstring);
        $condizioneChiavi = $this->_buildKeysCondition($dbe);
        $query = "update $mytablename set $myvalori where $condizioneChiavi";
        return $query;
    }
    
    /**
     * SE un valore e' array => Costriusce una sequenza di OR
     * SE un nome inizia con 'from_' => scrive una clausola >=
     * SE un nome inizia con 'to_' => scrive una clausola <=
     * @TODO gestire caseSensitive!!!!!
     */
    function _buildSelectString($dbe, $uselike=1,$caseSensitive=false) {
        $nomicampi = $dbe->getNames();
        $clausole=array();
        $len_clausole = 0;
        foreach($nomicampi as $n) {
            $v = $dbe->getValue($n);
            $is_from = substr($n,0,5)=='from_';
            $is_to = substr($n,0,3)=='to_';
            #if not v: continue
            if($v===null) {
                continue;
            }
            $tipo_v = $dbe->getColumnType($n);
            if($tipo_v===null) $tipo_v=gettype($v);
            if($tipo_v=='int' && $v==='') continue;
            // FIXME ristrutturare questo IF
            if($tipo_v=='string' || $tipo_v=='datetime' || $tipo_v=='date' || $tipo_v=='time') {
                if($v!=='') {
                    if($is_from) {
                        $clausole[ $len_clausole++ ] =  substr($n,5).">='".$this->db_escape_string($v)."'" ;
                    } else if($is_to) {
                        $clausole[ $len_clausole++ ] =  substr($n,3)."<='".$this->db_escape_string($v)."'" ;
                    } else if(!$dbe->isFK($n) && $uselike==1) {
                        $clausole[ $len_clausole++ ] = "$n like '%%".$this->db_escape_string($v)."%%'" ;
                    } else {
                        $clausole[ $len_clausole++ ] =  "$n='".$this->db_escape_string($v)."'" ;
                    }
                }
            } elseif($tipo_v=='uuid') {
                $uuid_v = DBEntity::hex2uuid($v);
                if($uuid_v!=='') {
                    $clausole[ $len_clausole++ ]="$n='$uuid_v'";
                }
            } elseif(is_array($v)) {
                $subclause=array();
                foreach($v as $v1) {
                    if($tipo_v=='string') {
                        if($uselike==1) {
                            $subclause[] = "$n like '%%".$this->db_escape_string($v1)."%%'" ;
                        } else {
                            $subclause[] =  "$n='".$this->db_escape_string($v1)."'" ;
                        }
                    } else {
                        $subclause[] =  "$n=$v1" ;
                    }
                }
                $clausole[ $len_clausole++ ] = " ( " . implode($subclause," OR ") . " ) ";
            } else {
                    if($is_from) {
                        $clausole[ $len_clausole++ ] =  substr($n,5).">=$v" ;
                    } else if($is_to) {
                        $clausole[ $len_clausole++ ] =  substr($n,3)."<=$v" ;
                    } else
                        $clausole[ $len_clausole++ ] =  "$n=$v" ;
            }
        }
        $mytablename = $this->_buildTableName($dbe);
        if(count($clausole)>0) {
            $myclausole = join(" and ", $clausole);
            $query = "select * from $mytablename where $myclausole";
        } else {
            $query = "select * from $mytablename";
        }
        return $query;
    }
    
    function _buildDeleteString($dbe) {
        $mytablename = $this->_buildTableName($dbe);
        $clausole = $this->_buildKeysCondition($dbe);
        $query = "delete from $mytablename where $clausole";
        return $query;
    }
    
    /** Returns a list of DBE according to the searchString */
    function _select($classname, $tablename, $searchString) {
        if($this->_verbose) { print "DBMgr._select: classname=$classname<br />\n"; }
        $this->connect();
        $result = $this->db_query($searchString);
        $numres = $result===false ? 0 : $this->connProvider->db_num_rows($result);
         $names = $this->_description2names($result);
        if($this->_verbose) { print "DBMgr._select: found $numres rows.<br />\n"; }
        $ret = array();
         for ($i=0; $i<$numres; $i++) {
             $_array = $this->db_fetch_array($result);
            $_tmp = $this->_factory->getInstance($classname, $names, $_array);
            if($this->_verbose) print "DBMgr._select: tmp=" . $_tmp->to_string() . "<br />\n";
            $ret[ $i ] = $_tmp;
        }
        if($result!==false) $this->db_free_result($result);
        return $ret;
    }
    function select($classname, $tablename, $searchString) {
        return $this->_select($classname, $tablename, $searchString);
    }
    
    function insert($dbe) {
        $this->connect();
        $dbe->_before_insert($this);
        $sqlString = $this->_buildInsertString($dbe);
        if($this->_verbose) { print "DBMgr.insert: sqlString = $sqlString<br />\n"; }
        $result = $this->db_query($sqlString);
        if(!$result) {
            return null;
        } else {
            $dbe->_after_insert($this);
            return $dbe;
        }
    }
    
    function update($dbe) {
        $this->connect();
        $dbe->_before_update($this);
        $sqlString = $this->_buildUpdateString($dbe);
        if($this->_verbose) { print "DBMgr.update: sqlString = $sqlString<br />\n"; }
        $result = $this->db_query($sqlString);
        $dbe->_after_update($this);
        return $dbe;
    }
    
    function delete($dbe) {
        $this->connect();
        $dbe->_before_delete($this);
        $sqlString = $this->_buildDeleteString($dbe);
        if($this->_verbose) {
            print "DBMgr.delete: sqlString = $sqlString<br />\n";
        }
        $result = $this->db_query($sqlString);
        $dbe->_after_delete($this);
        return $dbe;
    }
    
    /** Provides Search methods */
    function search($dbe, $uselike=1, $caseSensitive=false, $orderby=null) {
        if($this->_verbose) {
            $dbe_string = $dbe->to_string();
            print "DBMgr.search: dbe=$dbe_string <br />\n";
        }
        $query = $this->_buildSelectString($dbe,$uselike,$caseSensitive);
        if($orderby !=null and $orderby!='') {
            $query .= " ORDER BY $orderby ";
        }
        if($this->_verbose) { print "DBMgr.search: query=$query <br />\n"; }
        $classname = $dbe->getTypeName()>"" ? $dbe->getTypeName() : get_class($dbe);
        $tmpret = $this->select($classname, $this->_buildTableName($dbe), $query);
        return $tmpret;
    }
    
    function getNextId(&$dbe) {
        $nomeTabella = $this->_schema . "_seq_id";
        $tmp = $this->_select(get_class($dbe), $nomeTabella, "select id from $nomeTabella where name=''");
        $myid = count($tmp)>0 ? $tmp[0]->getValue('id') + 1 : 1;
        if($this->_verbose) { print "DBMgr.getNextId: nomeTabella=$nomeTabella<br />\n"; }
        if($this->_verbose) { print "DBMgr.getNextId: count(tmp)=".count($tmp)."<br />\n"; }
        if(count($tmp)==0)
            $this->db_query("insert into $nomeTabella (id,name) values($myid,'') ");
        else
            $this->db_query("update $nomeTabella set id=$myid where name='' ");
        return $myid;
    }
    /**
     * SE esiste uniqid, la usa per generare l'uuid,
     * altrimenti usa la vecchia getNextId
     * @date 2010.07.27
     */
    function getNextUuid(&$dbe, $prefix="",$length=16) {
        if(strlen($prefix)==0)
            $prefix=$_SERVER['SERVER_NAME'];
        $ret="$prefix.";
        if(function_exists("uniqid")) {
            $ret.=uniqid();
        } else {
            $ret.=$this->getNextId($dbe);
        }
        if(strlen($ret)>$length)
            $ret=substr($ret,-1*$length);
        elseif(strlen($ret)<$length)
            $ret = sprintf("%-16s", $ret);
        return $ret;
    }
    
    /**
     * Date le chiavi di una dbe, controlla se esiste gia una entry nel DB
     * @date 2007.07.27
     */
    function exists($dbe) {
        $ret=false;
        $myclausole = $this->_buildKeysCondition($dbe);
        if(strlen($myclausole)==0)
            return $ret;
        $mytablename = $this->_buildTableName($dbe); // 2009.12.15 $dbe->getTableName();
        $query = "select count(*) as numero from $mytablename where $myclausole";
        $classname = $dbe->getTypeName()>"" ? $dbe->getTypeName() : get_class($dbe);
        $tmp=$this->select($classname, $mytablename, $query);
        $ret = $tmp[0]->getValue('numero')>0;
        return $ret;
    }
}

?>