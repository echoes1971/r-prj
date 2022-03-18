// Client
import { JSONDBConnection } from './db/dblayer'

class BackEndProxy {
    constructor() {
        this.endpoint = 'http://localhost:8080/jsonserver.php'

        this.con = new JSONDBConnection('http://localhost:8080/jsonserver.php', true);
    }



    ping(on_ping_callback = null) {
        this.con.ping(on_ping_callback);
    }
}

export { BackEndProxy };
