// Client
import { JSONDBConnection } from './db/dblayer'

class BackEndProxy {
    constructor(endpoint = 'http://localhost:8080/jsonserver.php') {
        this.endpoint = endpoint

        this.con = new JSONDBConnection(endpoint, true);
    }



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
        this.con.getLoggedUser(a_callback);
    }

    execute(tablename,sql_string,a_callback) {
        this.con.execute(tablename,sql_string,a_callback);
    }

    select(dbename,tablename,sql_string,a_callback) {
        this.con.Select(dbename,tablename,sql_string,a_callback);
    }

    search(dbe, uselike, caseSensitive, orderBy, a_callback) {
        this.con.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
    }


    getAllFormClassnames(a_callback) {
        this.con.getAllFormClassnames(a_callback);
    }
    getFormInstance(aclassname,a_callback) {
        if(aclassname===null || aclassname===undefined || aclassname.length==0) return;
        this.con.getFormInstance(aclassname,a_callback);
    }


}

export { BackEndProxy };
