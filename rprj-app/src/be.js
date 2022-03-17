// Client
import { JSONDBConnection } from './db/dblayer'

class BackEndProxy {
    constructor() {
        this.endpoint = 'http://localhost:8080/jsonserver.php'

        this.con = new JSONDBConnection('http://localhost:8080/jsonserver.php', true)
    }



    ping(on_ping_callback = null) {
        this.con.ping(on_ping_callback);
/*         // create a new XMLHttpRequest
        var xhr = new XMLHttpRequest()

        var default_callback = (myxhr) => {
            // update the state of the component with the result here
            console.log(xhr.responseText)
            const jsonObj = JSON.parse(xhr.responseText)
            console.log(jsonObj)
            console.log('== Msg =======================================')
            console.log(atob(jsonObj[0]))
            console.log('== BODY ======================================')
            console.log(jsonObj[1])
            console.log('==============================================')
            a_callback(xhr)
        };
        // get a callback when the server responds
        xhr.addEventListener('load', default_callback)
        // xhr.addEventListener('load', a_callback ? a_callback(&xhr) : default_callback)
        // open the request with the verb and the url
        xhr.open('POST', this.endpoint)

        var mydata = { method: "ping", params: []}

        // send the request
        xhr.send(JSON.stringify(mydata))
 */    }
}

export { BackEndProxy };
