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

    getLoggedUser(a_callback) {
        this.con.getLoggedUser(a_callback);
    }
    
}

export { BackEndProxy };
