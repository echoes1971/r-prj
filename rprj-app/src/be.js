// Client
import { DBMgr, JSONDBConnection } from './db/dblayer'

/**
 * Back End Proxy
 * 
 * This class is a frontend for the UI for all the calls in the Backend.
 * 
 */
class BackEndProxy {
    constructor(endpoint = 'http://localhost:8080/jsonserver.php') {
        this.endpoint = endpoint

        this.con = new JSONDBConnection(endpoint, true);
        this.dbmgr = new DBMgr(this.con);
    }

    do_nothing_callback() {
        // console.log("SUNCHI");
    }

    // ******************** DBConnection

    ping(on_ping_callback = null) {
        this.con.ping(on_ping_callback);
    }

    connect(a_callback = null) {
        this.con.connect(a_callback);
    }
    isConnected() { return this.con.isConnected(); }
    disconnect(a_callback = null) {
        this.con.disconnect(a_callback);
    }

    login(user,pwd,a_callback) {
        this.con.login(user,pwd,a_callback);
    }
    getDBEUserFromConnection() {
        return this.con._dbe_user;
    }
    logout(a_callback) {
        this.con.logout(a_callback);
    }

    getLoggedUser(a_callback) {
        console.log("BackEndProxy.getLoggedUser: start.");
        this.con.getLoggedUser(a_callback);
        console.log("BackEndProxy.getLoggedUser: end.");
    }

    execute(tablename,sql_string,a_callback) {
        this.con.execute(tablename,sql_string,a_callback);
    }

    select(dbename,tablename,sql_string,a_callback) {
        this.con.Select(dbename,tablename,sql_string,a_callback);
    }

    // search(dbe, uselike, caseSensitive, orderBy, a_callback) {
    //     this.con.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
    // }

    objectById(oid, ignore_deleted, a_callback) {
        console.log("BackEndProxy.objectById: start.");
        this.con.objectById(oid, ignore_deleted, a_callback);
        console.log("BackEndProxy.objectById: end.");
    }
    fullObjectById(oid, ignore_deleted, a_callback) {
        console.log("BackEndProxy.fullObjectById: start.");
        this.con.fullObjectById(oid, ignore_deleted, a_callback);
        console.log("BackEndProxy.fullObjectById: end.");
    }


    getAllFormClassnames(a_callback) {
        this.con.getAllFormClassnames(a_callback);
    }
    getFormInstance(aclassname,a_callback) {
        if(aclassname===null || aclassname===undefined || aclassname.length===0) return;
        this.con.getFormInstance(aclassname,a_callback);
    }


    // ******************** DBConnection

    // getLoggedUser() {
    //     return this.dbmgr.getLoggedUser(this.do_nothing_callback);
    // }

    search(dbe, uselike, caseSensitive, orderBy, a_callback) {
        // this.con.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
        this.dbmgr.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
    }

}

export { BackEndProxy };
