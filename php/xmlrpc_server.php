<?php
/**
 * @copyright &copy; 2005-2016 by Roberto Rocco Angeloni <roberto@roccoangeloni.it>
 * @license http://opensource.org/licenses/lgpl-3.0.html GNU Lesser General Public License, version 3.0 (LGPLv3)
 * @version $Id: xmlrpc_server.php $
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
//error_reporting(E_ALL);
define("ROOT_FOLDER",     "./");
include_once ROOT_FOLDER . "config.php";
include_once ROOT_FOLDER . "db/dblayer.php";
include_once ROOT_FOLDER . "db/dbschema.php";
include_once ROOT_FOLDER . "formulator/formulator.php";
include_once ROOT_FOLDER . "formulator/formschema.php";
include_once ROOT_FOLDER . "xmlrpc/xmlrpc.inc";
include_once ROOT_FOLDER . "xmlrpc/xmlrpcs.inc";

define("MY_DEST_DIR",$GLOBALS[ 'root_directory' ]."/".$GLOBALS[ 'files_directory' ]);

session_start();
require_once(ROOT_FOLDER . "plugins.php");

//error_log("TEST LOG");

global $dbeFactory;

$dbmgr = array_key_exists('dbmgr',$_SESSION) ? $_SESSION['dbmgr'] : null;
if ($dbmgr===null || get_class($dbmgr)=='__PHP_Incomplete_Class') {
    $aFactory = new MyDBEFactory();
    $dbmgr = new ObjectMgr( $db_server, $db_user, $db_pwd, $db_db, $db_schema, $aFactory ); //$db_schema, $aFactory );
    $_SESSION['dbmgr'] = $dbmgr;
}
$dbmgr->setVerbose(false);

$formFactory = new MyFormFactory();

// Logged user
$utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;
if( $dbmgr->getDBEUser()===null ) $dbmgr->setDBEUser($utente);

function _isAuthorized() {
    global $xmlrpc_require_login;
    if($xmlrpc_require_login===false)
        return true;
    if($xmlrpc_require_login===true && array_key_exists('utente',$_SESSION) && $_SESSION['utente']->getTypeName()>'')
        return true;
    return false;
}


function _escape_string($s) {
    $ret = $s;
    return $ret;
}

function _arrayToXmlrpc(&$dict) {
  $tmpArray = array();
  $chiavi = array_keys($dict);
  foreach( array_keys($dict) as $k ) {
    $v = $dict[$k];
    if ($v!==null) {
      if (is_numeric($v)) {
          if ($v==strval(intval($v))) {
            $tmpArray[$k] = new xmlrpcval( $v, 'i4' );
          } elseif ($v==strval(floatval($v))) {
            $tmpArray[$k] = new xmlrpcval( $v, 'double' );
          }
      } else if ( is_bool($v) ) {
        $tmpArray[$k] = new xmlrpcval( $v, 'boolean' );
//        } else if( is_string($v) && $v!='' && $v!='0000-00-00 00:00:00' ) {
      } else if( is_string($v) && $v!='0000-00-00 00:00:00' ) {
          $tmpArray[$k] = new xmlrpcval( $v, 'base64' );
      } else if ( is_array($v) ) {
        $tmpArray[$k] = _arrayToXmlrpc($v);
      } else if( get_class($v)=='ForeignKey' || get_class($v)=='foreignkey' ) {
        $tmpArray[$k] = _fkToXmlrpc($v);
      }
    }
  }
  $ret = php_xmlrpc_encode( $tmpArray );
  return $ret;
}
function _xmlrpcToArray(&$dbexml) {
    global $dbmgr;
    $struttura = $dbexml; //->me['array'][0];
    $valori = array();
    if( array_key_exists('struct',$struttura->me) )
        foreach( $struttura->me['struct'] as $k=>$obj ) {
            $valori[$k] = $obj->scalarval();
        }
    elseif( array_key_exists('array',$struttura->me) )
        foreach( $struttura->me['array'] as $obj ) {
            $valori[] = $obj->scalarval();
        }
    
    return $valori;
}

function _fkToXmlrpc(&$fk) {
  $valori=array(
    '_typeName'=>'ForeignKey',
    '_typename'=>'ForeignKey',
    'colonna_fk'=>$fk->colonna_fk,
    'tabella_riferita'=>$fk->tabella_riferita,
    'colonna_riferita'=>$fk->colonna_riferita,
    );
  $valori = _arrayToXmlrpc($valori);
  $ret = php_xmlrpc_encode($valori);
  return $ret;
}
function _xmlrpcToFk(&$fkxml) {
  $valori=_xmlrpcToArray($fkxml);
  $ret = new ForeignKey($valori['colonna_fk'],$valori['tabella_riferita'],$valori['colonna_riferita']);
  return $ret;
}

function _dbeToXmlrpc(&$dbe) {
  $tmpArray = array();
  $tmpArray["_typeName"] = $dbe->getTypeName();
  $tmpArray["_typename"] = $dbe->getTypeName();
  $dict = $dbe->getValuesDictionary();
  $chiavi = array_keys($dict);
  foreach( array_keys($dict) as $k ) {
    $v = $dict[$k];
    if ($v!==null) {
        if (is_numeric($v)) {
            if ($v==strval(intval($v))) {
              $tmpArray[$k] = new xmlrpcval( $v, 'i4' );
            } elseif ($v==strval(floatval($v))) {
              $tmpArray[$k] = new xmlrpcval( $v, 'double' );
            }
        } else if ( is_bool($v) ) {
          $tmpArray[$k] = new xmlrpcval( $v, 'boolean' );
//        } else if( is_string($v) && $v!='' && $v!='0000-00-00 00:00:00' ) {
        } else if( is_string($v) && $v!='0000-00-00 00:00:00' ) {
          $tmpArray[$k] = new xmlrpcval( $v, 'base64' );
        }
    }
  }
  $ret = php_xmlrpc_encode( $tmpArray );
  return $ret;
}
function _xmlrpcToDbe(&$dbexml) {
    global $dbmgr;
    $myfactory = $dbmgr->getFactory();
    $valori = array();
    $aClassname = $dbexml->me['array'][0];

    $struttura = $dbexml->me['array'][1];
    foreach( $struttura->me['struct'] as $k=>$obj ) {
//echo("_xmlrpcToDbe: $k=".$obj->scalarval());
        $valori[$k] = $obj->scalarval();
    }
    $ret = $myfactory->getInstance( $aClassname->scalarval(),
                $aNames=NULL, $aValues=NULL, $aAttrs=$valori
                );
    return $ret;
}

$execDBEMethod_sig=array(array($xmlrpcString));
$execDBEMethod_doc='execDBEMethod()';
function execDBEMethod($m) {
    global $dbmgr;

    // Lettura parametri
    $dbexml = $m->getParam(0);
    $dbe = _xmlrpcToDbe($dbexml);
    $nomeMetodo = $m->getParam(1);
    $nomeMetodo = $nomeMetodo->scalarval();
    $argomenti = $m->getParam(2);
    $argomenti = _xmlrpcToArray($argomenti);

    // Eseguo
    ob_start();
    $dbmgr->setVerbose(false);
    $res = array();
    if(_isAuthorized()) {

        for($i=0; $i<count($argomenti); $i++)
            if(is_string($argomenti[$i]))
                $argomenti[$i]="\"".$argomenti[$i]."\"";
        $stringa = "\$res = \$dbe->$nomeMetodo(".implode(",",$argomenti).");";
        echo "xmlrpc_server.execDBEMethod: stringa: $stringa\n";
        eval($stringa);
    
    } else {
        echo "xmlrpc_server.execDBEMethod: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();

    // Ritorno i risultati
    if( is_array($res) ) {
        $res = _arrayToXmlrpc($res);
    }
    $retArray=$res;

    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                $retArray, //new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$getTypeList_sig=array(array($xmlrpcString));
$getTypeList_doc='getTypeList()';
function getTypeList($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    // Eseguo
    ob_start();
    $dbmgr->setVerbose(false);
    $lista=array();
    if(_isAuthorized()) {
        $myfactory = $dbmgr->getFactory();
        $lista[] = $myfactory->getRegisteredTypes();
    } else {
        echo "xmlrpc_server.getTypeList: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    // Ritorno i risultati
    $retArray=array();
    foreach($lista as $mydbe ) {
        $retArray[]=_arrayToXmlrpc($mydbe);
    }
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$select_sig=array(array($xmlrpcString));
$select_doc='select(tablename,searchString)';
function select($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    $tablename = $m->getParam(0);
    $searchString = $m->getParam(1);
    
    ob_start();
    $dbmgr->setVerbose(false);
    $listadbe=array();
    if(_isAuthorized()) {
        $myfactory = $dbmgr->getFactory();
        $classname = $myfactory->getInstanceByTableName( $tablename->scalarval() );
        $classname = $classname->getTypeName();
        $listadbe = $dbmgr->select( $classname, $tablename->scalarval(), $searchString->scalarval() );
    } else {
        echo "xmlrpc_server.select: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    foreach($listadbe as $mydbe ) {
        $retArray[]=_dbeToXmlrpc($mydbe);
    }
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$selectAsArray_sig=array(array($xmlrpcString));
$selectAsArray_doc='selectAsArray(tablename, searchString)';
function selectAsArray($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    $tablename = $m->getParam(0);
    $searchString = $m->getParam(1);
    
    ob_start();
    $dbmgr->setVerbose(false);
    $listadbe = array();
    if(_isAuthorized()) {
        
        $myfactory = $dbmgr->getFactory();
        $classname = get_class($myfactory->getInstanceByTableName( $tablename->scalarval() ));
        $listadbe = $dbmgr->select( $classname, $tablename->scalarval(), $searchString->scalarval() );
        
    } else {
        echo "xmlrpc_server.selectAsArray: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    foreach($listadbe as $mydbe ) {
        $retArray[]=_arrayToXmlrpc($mydbe->getValuesDictionary());
    }
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$getKeys_sig=array(array($xmlrpcString));
$getKeys_doc='getKeys(tablename)';
function getKeys($m) {
    global $dbmgr;
    global $xmlrpc_require_login;

    $tablename = $m->getParam(0);
    $tablename = $tablename->scalarval();

    ob_start();
    $dbmgr->setVerbose(false);
    $chiavi=array();
    if(_isAuthorized()) {
        //echo "tablename: $tablename\n";
        $chiavi = $dbmgr->getKeys( $tablename );
    } else {
        echo "xmlrpc_server.getKeys: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();

    $retArray=_arrayToXmlrpc($chiavi);
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                $retArray
            ), 'array'
        )
    );
}

$getForeignKeys_sig=array(array($xmlrpcString));
$getForeignKeys_doc='getForeignKeys(tablename)';
function getForeignKeys($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    $myfactory = $dbmgr->getFactory();
    
    $tablename = $m->getParam(0);
    $tablename = $tablename->scalarval();
    
    ob_start();
    $dbmgr->setVerbose(false);
    $chiavi=array();
    if(_isAuthorized()) {
        echo "tablename: $tablename\n";
        $chiavi = $dbmgr->getForeignKeys( $tablename );
    } else {
        echo "xmlrpc_server.getForeignKeys: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=_arrayToXmlrpc($chiavi);
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                $retArray
            ), 'array'
        )
    );
}
$getColumnSize_sig=array(array($xmlrpcString));
$getColumnSize_doc='getColumnSize(tablename)';
function getColumnSize($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    $myfactory = $dbmgr->getFactory();
    
    $tablename = $m->getParam(0);
    $tablename = $tablename->scalarval();
    
    ob_start();
    $dbmgr->setVerbose(false);
    $num_colonne=-1;
    if(_isAuthorized()) {
        //echo "tablename: $tablename\n";
        $num_colonne = $dbmgr->getColumnSize( $tablename );
        //echo "num_colonne: $num_colonne\n";
    } else {
        echo "xmlrpc_server.getColumnSize: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array($num_colonne);
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                _arrayToXmlrpc($retArray) //new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$getColumnName_sig=array(array($xmlrpcString));
$getColumnName_doc='getColumnName(tablename,num_column)';
function getColumnName($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    $myfactory = $dbmgr->getFactory();
    
    $tablename = $m->getParam(0);
    $tablename = $tablename->scalarval();
    $num_column = $m->getParam(1);
    $num_column = $num_column->scalarval();
    
    ob_start();
    $dbmgr->setVerbose(false);
    $tmp="";
    if(_isAuthorized()) {
        //echo "tablename: $tablename\n";
        $tmp = $dbmgr->getColumnName( $tablename, $num_column );
        //echo "Column name: $tmp\n";
    } else {
        echo "xmlrpc_server.getColumnName: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array($tmp);
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                _arrayToXmlrpc($retArray)
            ), 'array'
        )
    );
}

$getColumnsForTable_sig=array(array($xmlrpcString));
$getColumnsForTable_doc='getColumnsForTable(tablename)';
function getColumnsForTable($m) {
    global $dbmgr;
    global $xmlrpc_require_login;

    $tablename = $m->getParam(0);
    $tablename = $tablename->scalarval();

    ob_start();
    $dbmgr->setVerbose(false);
    $chiavi=array();
    if(_isAuthorized()) {
        //echo "tablename: $tablename\n";
        $chiavi = $dbmgr->getColumnsForTable( $tablename );
        //echo "chiavi: ";var_dump($chiavi);echo "\n";
    } else {
        echo "xmlrpc_server.getColumnsForTable: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();

    $retArray=_arrayToXmlrpc($chiavi);
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                $retArray
            ), 'array'
        )
    );
}

$insert_sig=array(array($xmlrpcString));
$insert_doc='insert(dbe)';
function insert($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    $dbe = null;
    if(_isAuthorized()) {
        $dbexml = $m->getParam(0);
        $dbe = _xmlrpcToDbe($dbexml);
        $dbe = $dbmgr->insert($dbe);
        $dbe->setValue('_typename',get_class($dbe));
    } else {
        echo "xmlrpc_server.insert: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
            ), 'array'
        )
    );
}

$update_sig=array(array($xmlrpcString));
$update_doc='update(dbe)';
function update($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    $dbe = null;
    if(_isAuthorized()) {
        $dbexml = $m->getParam(0);
        $dbe = _xmlrpcToDbe($dbexml);
        $dbe = $dbmgr->update($dbe);
        if($dbe!==null) {
            $dbe->setValue('_typename',get_class($dbe));
        }
    } else {
        echo "xmlrpc_server.update: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $dbe===null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
            ), 'array'
        )
    );
}

$delete_sig=array(array($xmlrpcString));
$delete_doc='delete(dbe)';
function delete($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    $dbe = null;
    if(_isAuthorized()) {
        $dbexml = $m->getParam(0);
        $dbe = _xmlrpcToDbe($dbexml);
        $dbe = $dbmgr->delete($dbe);
        $dbe->setValue('_typename',get_class($dbe));
    } else {
        echo "xmlrpc_server.delete: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
            ), 'array'
        )
    );
}

$search_sig=array(array($xmlrpcString));
$search_doc='search(dbe, uselike, orderby)';
function search($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    $utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;
    
    $listadbe = array();
    
    ob_start();
    // 2011.04.27: start.
    // 2011.04.27: opened for public objects
    if(true || _isAuthorized()) {
//    if(_isAuthorized()) {
    // 2011.04.27: end.
        $dbmgr->setVerbose(false);
        
        $dbexml = $m->getParam(0);
        $dbe = _xmlrpcToDbe($dbexml);
        $uselike = $m->getParam(1);
        $uselike = $uselike->scalarval();
        $caseSensitive = $m->getParam(2);
        $caseSensitive = $caseSensitive->scalarval();
        $orderby = $m->getParam(3);
        $orderby = $orderby->scalarval();
        if($orderby=='') $orderby=null;
        // 2012.03.05: start.
        $ignore_deleted = true;
        if($m->getNumParams()>4) { $ignore_deleted = $m->getParam(4); $ignore_deleted = $ignore_deleted->scalarval(); }
        $full_object = true;
        if($m->getNumParams()>5) { $full_object = $m->getParam(5); $full_object = $full_object->scalarval(); }
        // 2012.03.05: end.
        
        $listadbe = $dbmgr->search($dbe,$uselike,$caseSensitive,$orderby,$ignore_deleted,$full_object);
        $dbmgr->setVerbose(false);
    } else {
        echo "xmlrpc_server.search: Authentication required!\n";
    }
    $messaggi = ob_get_contents();
    ob_end_clean();
     error_log("xmlrpc_server::search: messaggi=$messaggi\n",3,"/tmp/phperrors");
    $retArray=array();
    foreach($listadbe as $mydbe ) {
        $retArray[]=_dbeToXmlrpc($mydbe);
    }
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi,'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

// ************* ObjectMgr: start.
$objectById_sig=array(array($xmlrpcString));
$objectById_doc='objectById(myid)';
function objectById($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    $dbe = null;
    if(_isAuthorized()) {
        $myid = $m->getParam(0);
        $myid = $myid->scalarval();
        $ignore_deleted = $m->getParam(1);
        $ignore_deleted = $ignore_deleted->scalarval();
        $dbe = $dbmgr->objectById($myid,$ignore_deleted);
        if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
    } else {
        echo "xmlrpc_server.objectById: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
            ), 'array'
        )
    );
}

$fullObjectById_sig=array(array($xmlrpcString));
$fullObjectById_doc='fullObjectById(myid)';
function fullObjectById($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    $dbe = null;
    if(_isAuthorized()) {
        $myid = $m->getParam(0);
        $myid = $myid->scalarval();
        $ignore_deleted = $m->getParam(1);
        $ignore_deleted = $ignore_deleted->scalarval();
        $dbe = $dbmgr->fullObjectById($myid,$ignore_deleted);
        if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
    } else {
        echo "xmlrpc_server.fullObjectById: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
            ), 'array'
        )
    );
}

$objectByName_sig=array(array($xmlrpcString));
$objectByName_doc='objectByName(myid)';
function objectByName($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    // 2012.05.07: start.
    $retArray = array();
//    $dbe = null;
    // 2012.05.07: end.
    if(_isAuthorized()) {
        $myid = $m->getParam(0);
        $myid = $myid->scalarval();
        $ignore_deleted = $m->getParam(1);
        $ignore_deleted = $ignore_deleted->scalarval();
        // 2012.05.07: start.
        $tmp = $dbmgr->objectByName($myid,$ignore_deleted);
        foreach($tmp as $dbe) {
            $dbe->setValue('_typename',get_class($dbe));
            $retArray[]=_dbeToXmlrpc($dbe);
        }
//        $dbe = $dbmgr->objectByName($myid,$ignore_deleted);
//        if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
        // 2012.05.07: end.
    } else {
        echo "xmlrpc_server.objectByName: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
    // 2012.05.07: start.
                new xmlrpcval( $retArray,'array' )
//                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
    // 2012.05.07: end.
            ), 'array'
        )
    );
}

$fullObjectByName_sig=array(array($xmlrpcString));
$fullObjectByName_doc='fullObjectByName(myid)';
function fullObjectByName($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    ob_start();
    $dbmgr->setVerbose(false);
    // 2012.05.07: start.
    $retArray = array();
//    $dbe = null;
    // 2012.05.07: end.
    if(_isAuthorized()) {
        $myid = $m->getParam(0);
        $myid = $myid->scalarval();
        $ignore_deleted = $m->getParam(1);
        $ignore_deleted = $ignore_deleted->scalarval();
        // 2012.05.07: start.
        $tmp = $dbmgr->fullObjectByName($myid,$ignore_deleted);
        foreach($tmp as $dbe) {
            $dbe->setValue('_typename',get_class($dbe));
            $retArray[]=_dbeToXmlrpc($dbe);
        }
        //$dbe = $dbmgr->fullObjectByName($myid,$ignore_deleted);
        //if($dbe!==null) $dbe->setValue('_typename',get_class($dbe));
        // 2012.05.07: end.
    } else {
        echo "xmlrpc_server.fullObjectByName: Authentication required!\n";
    }
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
    // 2012.05.07: start.
                new xmlrpcval( $retArray,'array' )
//                new xmlrpcval( $dbe==null ? array() : array(_dbeToXmlrpc($dbe)),'array' )
    // 2012.05.07: end.
            ), 'array'
        )
    );
}
// ************* ObjectMgr: end.


$login_sig=array(array($xmlrpcString));
$login_doc='login(login,pwd)';
function login($m) {
    global $dbmgr;
//     global $utente;
    
    ob_start();
    $dbmgr->setVerbose(false);
    
    $p=0;
    $login = $m->getParam($p++);
    $login = $login->scalarval();
    $pwd = $m->getParam($p++);
    $pwd = $pwd->scalarval();
    
    $valori = array( 'login'=>$login, 'pwd'=>$pwd, );
    $cerca = new DBEUser(null,null,null,$attrs=$valori,null) ;
    $ris = $dbmgr->search( $cerca, $uselike=0 );
    
    $__utente=null;
    
    if ( count($valori)==2 && $valori['login']>"" && $valori['pwd']>"" && count($ris)==1 ) {
        // User FOUND
        $__utente = $ris[0];
        $_SESSION['utente'] = $__utente;
        $dbmgr->setDBEUser($__utente);
        // 2012.04.30: start.
        $cerca = new DBEUserGroup();
        $cerca->readFKFrom($__utente);
        $lista=$dbmgr->search( $cerca, $uselike=0 );
        $lista_gruppi=array();
        foreach($lista as $g) { $lista_gruppi[]=$g->getValue('group_id'); }
        if(!in_array($__utente->getValue('group_id'), $lista_gruppi))
            $lista_gruppi[]=$__utente->getValue('group_id');
        $dbmgr->setUserGroupsList($lista_gruppi);
        // 2012.04.30: end.
    } else {
        // User NOT found
        $_SESSION['utente'] = null;
    }
    
    // Cleaning the pwd field :-) not good to show it back
    if($__utente!=null) $__utente->setValue('pwd',null);
    
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    if($__utente!=null) $retArray[]=_dbeToXmlrpc($__utente);
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$getLoggedUser_sig=array(array($xmlrpcString));
$getLoggedUser_doc='getLoggedUser()';
function getLoggedUser($m) {
    global $dbmgr;
//     global $utente;
    
    ob_start();
    $dbmgr->setVerbose(false);
    
    $__utente=$dbmgr->getDBEUser();
    
    // Cleaning the pwd field :-) not good to show it back
    if($__utente!==null) $__utente->setValue('pwd',null);
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    if($__utente!==null) $retArray[]=_dbeToXmlrpc($__utente);
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray,'array' )
            ), 'array'
        )
    );
}

$getDBSchema_sig=array(array($xmlrpcString));
$getDBSchema_doc='getDBSchema(language=python,classname=\'\')';
function getDBSchema($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    $p=0;
    $language = $m->getParam($p++);
    $language = $language->scalarval();
    $classname = $m->getParam($p++);
    $classname = $classname!==null ? $classname->scalarval() : '';
    
    ob_start();
    $ret='';
    //$dbmgr->setVerbose(false);
    if(_isAuthorized()) {
        if($language=='cpp') {
            _dbSchemaToCPP($classname);
        } else {
            _dbSchemaToPython($classname);
        }
    } else {
        echo "xmlrpc_server.getDBSchema: Authentication required!\n";
    }
    //$dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();

    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $ret, 'base64' ),
            ), 'array'
        )
    );
}

function _dbSchemaToPython($classname,$nome_schema="MySchema") {
    global $dbmgr;
    $myfactory = $dbmgr->getFactory();
    $mytypes = $myfactory->getRegisteredTypes();
    
    print "dbschema = {}\n";
    print "\n";
    foreach($mytypes as $k=>$v) {
        if($v=='DBEntity') continue;
        if($classname>'' && $classname!=$v) continue;
        $typeName = $v;
        $dbe = $dbmgr->getInstance($typeName); // 2011.04.04 eval("\$dbe = new $typeName();");
        $myparentclass = get_parent_class($dbe);
        $myparent = $dbmgr->getInstance($myparentclass); // 2011.04.04 eval("\$myparent= new $myparentclass();");
        $myparenttypename = $myparent->getTypeName();
        $myparentcolumns_keys = array_keys( $myparent->getColumns() );
        $myTableName = $dbe->getTableName();
        print "class $typeName($myparenttypename):\n";
        print "\t__columns={}\n";
        print "\tdef __init__(self, tablename=None, names=None, values=None, attrs=None, keys={'id':'number'} ):\n";
        print "\t\t$myparenttypename.__init__(self, tablename, names, values, attrs, keys )\n";
        print "\t\tif len($typeName.__columns.keys())==0:\n";
        print "\t\t\tparentcols=self.getColumns()\n";
        print "\t\t\tfor k in parentcols.keys():\n";
        print "\t\t\t\t$typeName.__columns[k]=parentcols[k]\n";
        foreach($dbe->getColumns() as $_col=>$_val) {
            if( in_array($_col,$myparentcolumns_keys) ) continue;
            if( count($_val)==0 ) continue;
            print "\t\t\t$typeName.__columns['$_col']=[\"".implode("\",\"",$_val)."\"]\n";
        }
//         print "\t\t\tpass\n";
        print "\t\tself._columns=$typeName.__columns\n";
        print "\tdef getTableName(self):\n";
        print "\t\treturn \"$myTableName\"\n";
        // Keys
        $chiavi = $dbe->getKeys();
        $tmpChiavi = array();
        foreach($chiavi as $nome_chiave=>$tipo_chiave) {
            $tmpChiavi[]="'$nome_chiave':'$tipo_chiave'";
        }
        print "\tdef getKeys(self):\n";
        print "\t\treturn { ".implode(", ",$tmpChiavi)." }\n";
        // FKs
        $fks = $dbe->getFK();
        $tmpfks=array();
        foreach($fks as $fk) {
            $tmpfks[]="ForeignKey(\"".$fk->colonna_fk."\",\"".$fk->tabella_riferita."\",\"".$fk->colonna_riferita."\")";
        }
        print "\tdef getFK(self):\n";
        print "\t\treturn [ ".implode(", ",$tmpfks)." ]\n";
        // order by
        if(count($dbe->getOrderBy())>0) {
            print "\tdef getOrderBy(self):\n";
            print "\t\treturn [ \"".implode("\", \"",$dbe->getOrderBy())."\" ]\n";
        }
        print "dbschema['$myTableName'] = $typeName\n";
        print "\n";
    }
}
function _dbSchemaToCpp($classname,$nome_schema="MySchema") {
    global $dbmgr;
    
    $nome_schema_maiuscolo = strtoupper($nome_schema);
    
    $myfactory = $dbmgr->getFactory();
    $mytypes = $myfactory->getRegisteredTypes();
    print "/**\n";
    print " * dbschema.h\n";
    print " */\n";
    print "\n";
    print "#ifndef {$nome_schema_maiuscolo}_H\n";
    print "#define {$nome_schema_maiuscolo}_H\n";
    print "\n";
    print "#include \"dblayer/dblayer.h\"\n";
    print "#include \"dblayer/dbentity.h\"\n";
    print "#include \"dblayer/dbfield.h\"\n";
    print "#include \"dblayer/dbmgr.h\"\n";
    print "using namespace DBLayer;\n";
    print "#include <string>\n";
    print "using namespace std;\n";
    print "\n";
    print "namespace $nome_schema {\n";
    print "\n";
    foreach($mytypes as $k=>$v) {
        if($v=='DBEntity') continue;
        if($classname>'' && $classname!=$v) continue;
        $typeName = $v;
        $dbe = $dbmgr->getInstance($typeName); // 2011.04.04 eval("\$dbe = new $typeName();");
        $myparentclass = get_parent_class($dbe);
        $myparent = $dbmgr->getInstance($myparentclass); // 2011.04.04 eval("\$myparent= new $myparentclass();");
        $myparenttypename = $myparent->getTypeName();
        print "  class $typeName : public $myparenttypename {\n";
        print "    public:\n";
        print "    $typeName();\n";
        print "      virtual ~$typeName();\n";
        print "      virtual string name();\n";
        if($dbe->getSchemaName()!==null) print "      virtual const string* getSchemaName();\n";
        print "      virtual string getTableName();\n";
        print "      virtual DBFieldVector* getKeys();\n";
        print "      virtual $typeName* createNewInstance();\n";
        print "    private:\n";
        print "      static const string nomiCampiChiave[];\n";
        $chiavi = $dbe->getKeys();
        $num_chiave=1;
        foreach($chiavi as $nome_chiave=>$tipo_chiave) {
            $tipoField="StringField";
            if($tipo_chiave=='int') $tipoField="IntegerField";
            print "      static $tipoField chiave$num_chiave; // $tipo_chiave\n";
            $num_chiave++;
        }
        print "      static DBFieldVector chiavi;\n";
        print "      static DBFieldVector ___init_keys();\n";
        print "  };\n";
        print "\n";
    }
    print "  string getSchema();\n";
    print "  void registerClasses(DBEFactory* dbeFactory);\n";
    print "}\n";
    print "\n";
    print "#endif\n";
    print "\n";
    
    print "\n";
    print "\n";
    
    print "/**\n";
    print " * dbschema.cpp\n";
    print " */\n";
    print "\n";
    print "#include \"dbschema.h\"\n";
    print "using namespace $nome_schema;\n";
    print "\n";
    print "#include <string>\n";
    print "using namespace std;\n";
    print "\n";
    $myRegisterClasses=array();
    foreach($mytypes as $k=>$v) {
        if($v=='DBEntity') continue;
        $typeName = $v;
        $dbe = $dbmgr->getInstance($typeName); // 2011.04.04 eval("\$dbe = new $typeName();");
        $myTableName = $dbe->getTableName();
        print "//*********************** $typeName: start.\n";
        $chiavi = $dbe->getKeys();
        $lista_nomi_chiavi=array_keys($chiavi);
        print "const string $typeName::nomiCampiChiave[] = { string(\"".implode("\"), string(\"",$lista_nomi_chiavi)."\") };\n";
        $num_chiave=0;
        $append_chiavi=array();
        foreach($chiavi as $nome_chiave=>$tipo_chiave) {
            $tipoField="StringField";
            if($tipo_chiave=='int') $tipoField="IntegerField";
            print "$tipoField $typeName::chiave".($num_chiave+1)."( (const string*)&$typeName::nomiCampiChiave[$num_chiave] );\n";
            $append_chiavi[] = "ret.push_back( &$typeName::chiave".($num_chiave+1)." );";
            $num_chiave++;
        }
        print "DBFieldVector $typeName::chiavi = $typeName::___init_keys();\n";
        print "DBFieldVector $typeName::___init_keys() {";
        print " DBFieldVector ret = DBFieldVector();";
        print " ".implode(" ",$append_chiavi);
//         print "\n printf(\"$typeName::___init_keys\\n\");";
        print " return ret; }\n";
        print "$typeName::$typeName() { this->tableName.clear(); }\n";
        print "$typeName::~$typeName() {}\n";
        print "string $typeName::name() { return \"$typeName\"; }\n";
        print "string $typeName::getTableName() { return \"$myTableName\"; }\n";
        if($dbe->getSchemaName()!==null)
            print "const string* $typeName::getSchemaName() { static const string __myschema=\""
                .($dbe->getSchemaName()!==null ? $dbe->getSchemaName() : $dbmgr->getSchema())
                ."\"; return &__myschema; }\n";
        print "DBFieldVector* $typeName::getKeys() { return &$typeName::chiavi; }\n";
        print "$typeName* $typeName::createNewInstance() { return new $typeName(); }\n";
        print "//*********************** $typeName: end.\n";
        print "\n";
        $myRegisterClasses[]="dbeFactory->registerClass(\"$myTableName\", new $typeName() );";
    }
    print "string $nome_schema::getSchema() { return \"".$dbmgr->getSchema()."\"; }\n";
    print "void $nome_schema::registerClasses(DBEFactory* dbeFactory) {\n";
    print "  ".implode("\n  ",$myRegisterClasses)."\n";
    print "}\n";
    print "\n";
    
    print "_dbSchemaToCPP: TODO - ForeignKeys e altro\n";
}

$getFormSchema_sig=array(array($xmlrpcString));
$getFormSchema_doc='getFormSchema(language=python,classname=\'\')';
function getFormSchema($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    
    global $formFactory;
    
    $p=0;
    $language = $m->getParam($p++);
    $language = $language->scalarval();
    $classname = $m->getParam($p++);
    $classname = $classname!==null ? $classname->scalarval() : '';
    
    ob_start();
    $ret='';
    if(_isAuthorized()) {
        if($language=='cpp') {
            print "_formSchemaToCPP: TODO\n";
        } else {
            _formSchemaToPython($classname);
        }
    } else {
        echo "xmlrpc_server.getFormSchema: Authentication required!\n";
    }
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $ret, 'base64' ),
            ), 'array'
        )
    );
}

function _formSchemaToPython($classname='') {
    global $formFactory;
    
    print "formschema_type_list={}\n";
    print "\n";
    $nomi = $formFactory->getAllClassnames();
    foreach($nomi as $nome) {
        if($classname>'' && $classname!=$nome) continue;
        if($nome=='default') continue;
        $form = $formFactory->getInstance( $nome );
        $parent = get_parent_class($form);
        $myparent = $formFactory->getInstance($parent,'',''); // 2011.04.04 eval("\$myparent= new $parent('','','');");
        $nomi_campi_parent = $myparent->getFieldNames();
        print "class $nome($parent):\n";
        print "\tdef __init__(self,nome='',azione='',metodo=\"POST\",enctype='',dbmgr=None):\n";
        print "\t\t$parent.__init__(self,nome,azione,metodo,enctype,dbmgr)\n";
        $nomi_campi = $form->getFieldNames();
        $numero_campi = 0;
        foreach($nomi_campi as $mynomecampo) {
            if( in_array( $mynomecampo, $nomi_campi_parent ) ) continue;
            $numero_campi++;
            $field = $form->getField($mynomecampo);
            if(is_a($field,"FKField")) {
                print "\t\t_mydbe=self.getDBE(dbmgr)\n";
                break;
            }
        }
        if($numero_campi>0) print "\t\t# Fields\n";
        foreach($form->getGroupNames() as $nomegruppo) {
            $ordine=-1;
            foreach($form->getGroup($nomegruppo) as $nomefield) {
                $field = $form->getField($nomefield);
                if( in_array( $field->aNomeCampo, $nomi_campi_parent ) ) continue;
                if(is_a($field,"FTextArea")) {
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."'"
                        .",'".$field->_title."'"
                        .",'".$field->_description."'"
                        .",".$field->_size.""
                        .",'".$field->aValore."'"
                        .",'".$field->aClasseCss."'"
                        .",".($field->width>0?$field->width:0).""
                        .",".($field->height>0?$field->height:0).""
                        .",".($field->basicFormatting?'True':'False')
                        .") )\n";
                } elseif(is_a($field,"FNumber")) {
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."',"
                        ."'".$field->_title."',"
                        ."'".$field->_description."',"
                        ."'".$field->aValore."',"
                        ."'".$field->aClasseCss."'"
                        .") )\n";
//                 } elseif(is_a($field,"FDateTimeReadOnly")) {
//                     print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
//                     print "\t\t\t".get_class($field)."('"
//                         ."TODO"
//                         .") )\n";
                } elseif(is_a($field,"FDateTime")) {
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."'"
                        .",'".$field->_title."'"
                        .",'".$field->_description."'"
                        .",'".$field->aValore."'"
                        .",'".$field->aClasseCss."'"
                        .",".($field->aVisualizzaData?'True':'False')
                        .",".($field->aVisualizzaOra?'True':'False')
                        .") )\n";
                } elseif(is_a($field,"FList")) {
                    $listaValori=array();
                    foreach($field->listaValori as $k=>$v) {
                        $listaValori[]=" '$k':'$v'";
                    }
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."'"
                        .",'".$field->_title."'"
                        .",'".$field->_description."'"
                        .",".($field->_size>0?$field->_size:0)
                                .",".$field->_length
                        .",'".$field->aValore."'"
                        .",'".$field->aClasseCss."'"
                        .",{".implode(",",$listaValori)."}"
                        .",".($field->altezza>0?$field->altezza:0)
                        .",".($field->multiselezione?'True':'False')
                        .") )\n";
                } elseif(is_a($field,"FKField")) {
                    print "\t\t_tmpFK=None\n";
                    print "\t\tif not _mydbe is None:\n";
                    print "\t\t\t_tmpFK=".($field->myFK!=null ?
                            "_mydbe.getFKDefinition('".$field->myFK->colonna_fk."')"
                            :"_mydbe.getFKDefinition('".$field->aNomeCampo."')"
                            )."\n";
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."'"
                        .",'".$field->_title."'"
                        .",'".$field->_description."'"
                        .",'".$field->aValore."'"
                        .",'".$field->aClasseCss."'"
                        .",_mydbe"
                        .",_tmpFK"
                        //.",'".$field->viewmode."'"
                        //.",'".$field->destform."'"
                        .",[".(count($field->description_columns)>0 ?
                                    "'".implode("','",$field->description_columns)."'"
                                    : ""
                                ). "]"
                        .",'".$field->destform."'"
                        .",'".$field->viewmode."'"
                        .",'".$field->description_glue."'"
                        .",".($field->altezza>0?$field->altezza:0)
                        .",".($field->multiselezione?'True':'False')
                        .") )\n";
//                 } elseif(is_a($field,"FKObjectField")) {
//                     print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
//                     print "\t\t\t".get_class($field)."('"
//                         ."TODO"
//                         .") )\n";
                } else {
                    print "\t\tself.addField('$nomegruppo',".($ordine).",'".$field->aNomeCampo."', \\\n";
                    print "\t\t\t".get_class($field)."("
                        ."'".$field->aNomeCampo."',"
                        ."'".$field->_title."',"
                        ."'".$field->_description."',"
                        .($field->_size>0?$field->_size:0).","
                        .($field->_length>0?$field->_length:0).","
                        ."'".$field->aValore."',"
                        ."'".$field->aClasseCss."'"
                        .") )\n";
                }
                #$ordine++;
            }
        }
        if( is_subclass_of($form,"FMasterDetail") ) {
            $num_dettagli = $form->getDetailFormsCount();
            if($num_dettagli>0) print "\t\t# Details\n";
            for($i=0; $i<$num_dettagli; $i++) {
                $mydetail = $form->getDetail($i);
                print "\t\tself.addDetail('".get_class($mydetail)."')\n";
            }
        }
        if( is_a($form,"FAssociation") ) {
            $_myFromForm = $form->getFromForm();
            $_myToForm = $form->getToForm();
            if($_myFromForm!==null) {
                print "\tdef getFromForm(self):\n";
                print "\t\treturn ".get_class()."()\n";
            }
            if($_myToForm!==null) {
                print "\tdef getToForm(self):\n";
                print "\t\treturn ".get_class($form->getToForm())."()\n";
            }
        }
        if( $form->getDetailIcon()>'' ) {
            print "\tdef getDetailIcon(self):\n";
            print "\t\treturn \"".$form->getDetailIcon()."\"\n";
        }
        if( $form->getDetailTitle()>'' ) {
            print "\tdef getDetailTitle(self):\n";
            print "\t\treturn '".$form->getDetailTitle()."'\n";
        }
        if( count($form->getDetailColumnNames())>0 ) {
            print "\tdef getDetailColumnNames(self):\n";
            print "\t\treturn ['".implode("','",$form->getDetailColumnNames())."']\n";
        }
        if( count($form->getDetailReadOnlyColumnNames())>0 ) {
            print "\tdef getDetailReadOnlyColumnNames(self):\n";
            print "\t\treturn ['".implode("','",$form->getDetailReadOnlyColumnNames())."']\n";
        }
        if($form->getFilterForm()!=null) {
            print "\tdef getFilterForm(self):\n";
            print "\t\treturn ".get_class($form->getFilterForm())."()\n";
        }
        if( count($form->getFilterFields())>0 ) {
            print "\tdef getFilterFields(self):\n";
            print "\t\treturn ['".implode("','",$form->getFilterFields())."']\n";
        }
        if( $form->getListTitle()>'' ) {
            print "\tdef getListTitle(self):\n";
            print "\t\treturn '".$form->getListTitle()."'\n";
        }
        if( count(array_keys($form->getDecodeGroupNames()))>0
                && count(array_keys($form->getDecodeGroupNames()))>count(array_keys($myparent->getDecodeGroupNames()))
                )
        {
            print "\tdef getDecodeGroupNames(self):\n";
            print "\t\tret = $parent.getDecodeGroupNames(self)\n";
            foreach($form->getDecodeGroupNames() as $__k=>$__v) {
                if( in_array( $__k, array_keys( $myparent->getDecodeGroupNames() ) ) ) continue;
                print "\t\tret['$__k']='$__v'\n";
            }
            print "\t\treturn ret\n";
        }
        if( count($form->getListColumnNames())>0 ) {
            print "\tdef getListColumnNames(self):\n";
            print "\t\treturn ['".implode("','",$form->getListColumnNames())."']\n";
        }
        if( count($form->getListEditableColumnNames())>0 ) {
            print "\tdef getListEditableColumnNames(self):\n";
            print "\t\treturn ['".implode("','",$form->getListEditableColumnNames())."']\n";
        }
        if($form->getPagePrefix()>'') {
            print "\tdef getPagePrefix(self):\n";
            print "\t\treturn '".$form->getPagePrefix()."'\n";
        }
        if($form->getDBE()!=null) {
            print "\tdef getDBE(self,dbmgr=None):\n";
            print "\t\tif not dbmgr is None:\n";
            print "\t\t\treturn dbmgr.getClazzByTypeName(\"".get_class($form->getDBE())."\")()\n";
            print "\t\ttry:\n";
            print "\t\t\treturn ".get_class($form->getDBE())."()\n";
            print "\t\texcept Exception,e:\n";
            print "\t\t\treturn None\n";
        }
        if( $form->getCodice()>'' ) {
            print "\tdef getCodice(self):\n";
            print "\t\treturn \"".$form->getCodice()."\"\n";
        }
        print "\tdef getShortDescription(self,dbmgr=None):\n";
        print "\t\traise Exception(\"TODO\")\n";
/*        if( $form->getController()>'' ) {
            print "\tdef getController(self):\n";
            print "\t\treturn \"".$form->getController()."\"\n";
        }*/
        if( count(array_keys($form->getActions()))>0 ) {
            print "\tdef getActions(self):\n";
            print "\t\treturn { ";
            foreach($form->getActions() as $__k=>$__v) {
                print "'$__k': ['".implode("','",$__v)."'], ";
            }
            print " }\n";
        }
        print "formschema_type_list[\"$nome\"]=$nome\n";
        print "\n";
    }
}

$getSchemaName_sig=array(array($xmlrpcString));
$getSchemaName_doc='getSchemaName()';
function getSchemaName($m) {
    global $dbmgr;
    ob_start();
    echo $dbmgr->getSchema();
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    $retArray[] = new xmlrpcval( $dbmgr->getSchema(), 'base64' );
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray, 'array' )
            ), 'array'
        )
    );
}

$ping_sig=array(array($xmlrpcString));
$ping_doc='ping()';
function ping($m) {
    ob_start();
    echo "pong()\n";
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    $retArray=array();
    $retArray[] = new xmlrpcval( "pong", 'base64' );
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $retArray, 'array' )
            ), 'array'
        )
    );
}

$GetRandomUploadDestinationDirectory_sig=array(array($xmlrpcString));
$GetRandomUploadDestinationDirectory_doc="GetRandomUploadDestinationDirectoryXmlrpc()->directory sul server riservata agli upload";
/**
 * @param database
 * @param username
 */
function GetRandomUploadDestinationDirectoryXmlrpc($m) {
    // 0.1 Ritorno
    $messaggi = "OK";
    $ret=array();

    ob_start();
    
    // Eseguo
    if(_isAuthorized()) {
        $ret['dest_dir'] = "tmp_".rand(0,9);
//         $ret['dest_dir'] = MY_DEST_DIR."/".rand(0,9);
    } else {
        $messaggi = "KO";
        echo "xmlrpc_server.GetRandomUploadDestinationDirectoryXmlrpc: Authentication required!\n";
    }
    
    $debugMsg = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $debugMsg, 'base64' ),
                _arrayToXmlrpc($ret)
            ), 'array'
        )
    );
}

function _filtraFilename($s) {
    return str_replace("'","_",$s);
}

$Upload_sig=array(array($xmlrpcString));
$Upload_doc="Upload(num_porzione_corrente, num_porzioni_totali, filename, bytes, mydestdir)->{}";
/**
 * @param database
 * @param username
 * @param ListaTitoli output
 * @param ListaUrl output
 */
function UploadXmlrpc($m) {
    global $database;
    global $username;
    // 0.1 Ritorno
    $messaggi = "OK";
    $ret=array();
    
    // 0.2 Lettura parametri
    $par=0;
    $num_porzione_corrente = $m->getParam($par++);
    $num_porzione_corrente = $num_porzione_corrente->scalarval();
    $num_porzioni_totali = $m->getParam($par++);
    $num_porzioni_totali = $num_porzioni_totali->scalarval();
    
    $filename = $m->getParam($par++);
    $filename = _filtraFilename( $filename->scalarval() );
    $binario = $m->getParam($par++);
    $binario = $binario->scalarval();
    
    $mydestdir = $m->getParam($par++);
    $mydestdir = $mydestdir->scalarval();
    
    ob_start();
    
    // Eseguo
    if(_isAuthorized()) {
        if(!file_exists(MY_DEST_DIR."/".$mydestdir)) mkdir(MY_DEST_DIR."/".$mydestdir, 0777 );
        //print "Scrivo: $mydestdir/$filename\n";
        
        $modo = $num_porzione_corrente==0 ? "w" : "a";
        
        $fp=fopen(MY_DEST_DIR."/"."$mydestdir/$filename", $modo); // Apro in modo 'append'
        fwrite($fp,$binario);
        fclose($fp);
        
        $ret['filename'] = $filename;
        $ret['dest_dir'] = $mydestdir;
    } else {
        $messaggi = "KO";
        echo "xmlrpc_server.UploadXmlrpc: Authentication required!\n";
    }
    
    $debugMsg = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $debugMsg, 'base64' ),
                _arrayToXmlrpc($ret)
            ), 'array'
        )
    );
}

$Download_sig=array(array($xmlrpcString));
$Download_doc="Download(uuid,view_thumb)->{}";
/**
 * @param database
 * @param username
 * @param ListaTitoli output
 * @param ListaUrl output
 */
function DownloadXmlrpc($m) {
    global $dbmgr;
    global $xmlrpc_require_login;
    $utente = array_key_exists('utente',$_SESSION) ? $_SESSION['utente'] : null;
    // 0.1 Ritorno
    $messaggi = "OK";
    $ret=array();
    // 0.2 Lettura parametri
    $par=0;
    $uuid = $m->getParam($par++);
    $uuid = $uuid->scalarval();
    $view_thumb = $m->getParam($par++);
    $view_thumb = $view_thumb->scalarval();
    
    ob_start();
    // Eseguo
    if(_isAuthorized()) {
        $cerca = new DBEFile();
        $cerca->setValue('id',$uuid);
        $lista = $dbmgr->search( $cerca, 0, null );
        $mydbe = $lista[0];
        
        $can_read=false;
        $_myuser=$dbmgr->getDBEUser();
        if( $mydbe->canRead() ) {
            $can_read=true;
        } elseif( $mydbe->canRead('G') && $dbmgr->hasGroup($mydbe->getGroupId()) ) {
            $can_read=true;
        } elseif( $mydbe->canRead('U') && $dbmgr->getDBEUser()!=null && $mydbe->getOwnerId()==$_myuser->getValue('id') ) {
            $can_read=true;
        }
        if($can_read) {
            $dest_path = $mydbe->generaObjectPath();
            $dest_dir=realpath($GLOBALS['root_directory'].'/'.$mydbe->dest_directory);
            if($dest_path>'') $dest_dir.="/$dest_path";
            $filename = "$dest_dir/".($view_thumb ? $mydbe->getThumbnailFilename() : $mydbe->getValue('filename') );
            //echo "xmlrpc_server.DownloadXmlrpc: filename=$filename\n";

            $tmp_nome = array_splice( explode("_",$mydbe->getValue('filename')) , 2);
            $nome = implode("_",$tmp_nome);
            
            $ret['mime']=mime_content_type($filename);
            $ret['filesize']=filesize($filename);
            $ret['filename']=$nome;

            $handle = fopen($filename, "rb");
            $ret['contents'] = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            $messaggi = "KO";
            echo "xmlrpc_server.DownloadXmlrpc: User not authorized!\n";
        }
    } else {
        $messaggi = "KO";
        echo "xmlrpc_server.DownloadXmlrpc: Authentication required!\n";
    }
    
    $debugMsg = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                new xmlrpcval( $debugMsg, 'base64' ),
                _arrayToXmlrpc($ret),
            ), 'array'
        )
    );
}


$CheckFileChanged_sig=array(array($xmlrpcString));
$CheckFileChanged_doc="CheckFileChanged(file_id, user_id, checksum)->{}";
function CheckFileChanged($m) {
    global $dbmgr;
    
    ob_start();
    $dbmgr->setVerbose(false);
    
    $p=0;
    $file_id = $m->getParam($p++);
    $file_id = $file_id->scalarval();
    $checksum = $m->getParam($p++);
    $checksum = $checksum->scalarval();
    
    $retArray=array();
    $__file=null;
    if ( $file_id>'' && $checksum>'' ) {
        //echo "file_id: $file_id - checksum: $checksum\n";
        $valori = array( 'id'=>$file_id, );
        $cerca = new DBEFile(NULL,NULL,NULL,$attrs=$valori,NULL) ;
        $ris = $dbmgr->search( $cerca, $uselike=0 );
        
        // File trovato
        if(count($ris)==1)  {
            $__file = $ris[0];
            $retArray[]=$__file->getValue('checksum')!=$checksum;
            $retArray[]=$__file->getValue('checksum')!=$checksum?"Changed":"Not changed";
            $retArray[]=$__file->getValue('checksum');
        } else {
            // File NON trovato
            $retArray[]=false;
            $retArray[]="Not found";
            $retArray[]=$checksum;
        }
    } else {
        // Parametri errati
        $retArray[]=false;
        $retArray[]="Wrong parameters";
        $retArray[]=$checksum;
    }
    
    $dbmgr->setVerbose(false);
    $messaggi = ob_get_contents();
    ob_end_clean();
    
    return new xmlrpcresp(
        new xmlrpcval(
            array(
                new xmlrpcval( $messaggi, 'base64' ),
                _arrayToXmlrpc($retArray)
            ), 'array'
        )
    );
}



$s=new xmlrpc_server( array(
            "select" =>
                array("function" => "select",
//                             "signature" => $select_sig,
                        "docstring" => $select_doc
                    ),
            "getKeys" =>
                array("function" => "getKeys",
//                             "signature" => $getKeys_sig,
                        "docstring" => $getKeys_doc
                    ),
            "getForeignKeys" =>
                array("function" => "getForeignKeys",
//                             "signature" => $getKeys_sig,
                        "docstring" => $getForeignKeys_doc
                    ),
            "getColumnSize" =>
                array("function" => "getColumnSize",
//                             "signature" => $getKeys_sig,
                        "docstring" => $getColumnSize_doc
                    ),
            "getColumnName" =>
                array("function" => "getColumnName",
//                             "signature" => $getKeys_sig,
                        "docstring" => $getColumnName_doc
                    ),
            "getColumnsForTable" =>
                array("function" => "getColumnsForTable",
//                          "signature" => $getKeys_sig,
                        "docstring" => $getColumnsForTable_doc
                    ),
            "selectAsArray" =>
                array("function" => "selectAsArray",
//                             "signature" => $selectAsArray_sig,
                        "docstring" => $selectAsArray_doc
                    ),
            "insert" =>
                array("function" => "insert",
                        "docstring" => $insert_doc
                    ),
            "update" =>
                array("function" => "update",
                        "docstring" => $update_doc
                    ),
            "delete" =>
                array("function" => "delete",
                        "docstring" => $delete_doc
                    ),
            "search" =>
                array("function" => "search",
                        "docstring" => $search_doc
                    ),
            "execDBEMethod" =>
                array("function" => "execDBEMethod",
                    "docstring" => $execDBEMethod_doc
                ),
            "getTypeList" =>
                array("function" => "getTypeList",
                        "docstring" => $getTypeList_doc
                    ),
            
            "login" =>
                array("function" => "login",
                        "docstring" => $login_doc
                    ),
            "getLoggedUser" =>
                array("function" => "getLoggedUser",
                        "docstring" => $getLoggedUser_doc
                    ),
            
            "ping" =>
                array("function" => "ping",
                        "docstring" => $ping_doc
                    ),
            
            "GetRandomUploadDestinationDirectoryXmlrpc" =>
                array("function" => "GetRandomUploadDestinationDirectoryXmlrpc",
                        "docstring" => $GetRandomUploadDestinationDirectory_doc
                    ),
            "UploadXmlrpc" =>
                array("function" => "UploadXmlrpc",
                        "docstring" => $Upload_doc
                    ),
            "DownloadXmlrpc" =>
                array("function" => "DownloadXmlrpc",
                        "docstring" => $Download_doc
                    ),
            "CheckFileChanged" =>
                array("function" => "CheckFileChanged",
                        "docstring" => $CheckFileChanged_doc
                    ),
            
            "getFormSchema" =>
                array("function" => "getFormSchema",
                        "docstring" => $getFormSchema_doc
                    ),
            "getDBSchema" =>
                array("function" => "getDBSchema",
                        "docstring" => $getDBSchema_doc
                    ),
            "getSchemaName" =>
                array("function" => "getSchemaName",
                        "docstring" => $getSchemaName_doc
                    ),
            "objectById" =>
                array("function" => "objectById",
                        "docstring" => $objectById_doc
                    ),
            "fullObjectById" =>
                array("function" => "fullObjectById",
                        "docstring" => $fullObjectById_doc
                    ),
            "objectByName" =>
                array("function" => "objectByName",
                        "docstring" => $objectByName_doc
                    ),
            "fullObjectByName" =>
                array("function" => "fullObjectByName",
                        "docstring" => $fullObjectByName_doc
                    ),
        )
    );

?>
