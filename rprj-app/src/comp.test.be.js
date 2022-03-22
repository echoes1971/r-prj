import React from 'react';

import { BackEndProxy } from './be';

class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            endpoint: props.endpoint,
            connected: "--",
            date: new Date(),
            user: "--",
            usr: '',
            pwd: '',
            server_response_0: '--',
            server_response_1: '--',
            dbename: 'DBEFolder',
            tablename: 'folders',
            sqlstring: 'select *\n  from rprj_folders'
        }

        this.be = new BackEndProxy(this.state.endpoint);

        // Bindings
        this.endpoint_handleChange = this.endpoint_handleChange.bind(this);

        this.default_server_callback = this.default_server_callback.bind(this);
        this.default_handleChange = this.default_handleChange.bind(this);
        this.default_handleSubmit = this.default_handleSubmit.bind(this);

        this.on_login_callback = this.on_login_callback.bind(this);
        this.btnLogin = this.btnLogin.bind(this);

        this.on_ping_callback = this.on_ping_callback.bind(this);
        this.on_btn_ping_callback = this.on_btn_ping_callback.bind(this);
        this.btnPingServer = this.btnPingServer.bind(this);

        this.on_fetchuser_callback = this.on_fetchuser_callback.bind(this);
        this.btnLoggedUser = this.btnLoggedUser.bind(this);
        this.btnLogout = this.btnLogout.bind(this);

        this.on_execute_callback = this.on_execute_callback.bind(this);
        this.btnExecute = this.btnExecute.bind(this);

        this.on_select_callback = this.on_select_callback.bind(this);
        this.btnSelect = this.btnSelect.bind(this);
    }

    componentDidMount() {
        this.timerID = setInterval(
            () => this.tick_time(),
            1000
        );

        this.tick_ping();
        this.pingID = setInterval(
            // () => this.be.ping(this.on_ping_callback),
            () => this.tick_ping(),
            60 * 1000                   // Better 60 seconds?
        );
    }

    componentWillUnmount() {
        clearInterval(this.timerID);
        clearInterval(this.pingID);
    }

    tick_time() {
        this.setState({
            date: new Date()
        })
    }

    tick_ping() {
        this.be.ping(this.on_ping_callback);
    }

    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const tmp = {}
        tmp[event.target.name] = event.target.value
        this.setState(tmp);
    }
    default_server_callback(jsonObj) {
        // console.log("TestBE.on_btn_ping_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: "" + jsonObj[1]
        })
        // console.log("TestBE.on_btn_ping_callback: end.");
    }

    endpoint_handleChange(event) {
        this.be = new BackEndProxy(event.target.value);
        this.setState({endpoint: event.target.value});
    }

    on_login_callback(jsonObj) {
        // console.log("TestBE.on_btn_ping_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        const tmpUser = this.be.getDBEUserFromConnection();
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: JSON.stringify(jsonObj[1]),
            user: tmpUser ? tmpUser.getValue('fullname') + " " + tmpUser.to_string() : ''
        })
        // console.log("TestBE.on_btn_ping_callback: end.");
    }
    on_fetchuser_callback(jsonObj) {
        console.log("TestBE.on_fetchuser_callback: start.");
        console.log(jsonObj)
        console.log(this.be.isConnected())
        const tmpUser = this.be.getDBEUserFromConnection();
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: JSON.stringify(jsonObj[1]),
            user: tmpUser ? tmpUser.getValue('fullname') + " " + tmpUser.to_string() : ''
        })
        console.log("TestBE.on_fetchuser_callback: end.");
    }

    on_ping_callback(jsonObj) {
        this.setState({
            connected: this.be.isConnected() ? "Online" : "Offline"
        })
    }
    on_btn_ping_callback(jsonObj) {
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: jsonObj[1] })
    }
    btnPingServer() {
        this.be.ping(this.on_btn_ping_callback);
    }

    btnLogin() {
        var user = this.state.usr;
        var pwd = this.state.pwd;
        this.be.login(user,pwd,this.on_login_callback);
    }

    btnLoggedUser() {
        this.be.getLoggedUser(this.on_fetchuser_callback);
    }

    btnLogout() {
        this.be.logout(this.default_server_callback);
    }

    on_execute_callback(jsonObj, dictlist) {
        console.log("TestBE.on_execute_callback: start.");
        var tmp = [];
        for(var i=0; dictlist!==null && i<dictlist.length; i++) {
            tmp.push(JSON.stringify(dictlist[i]));
        }
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: tmp.join("\n")
        })
        console.log("TestBE.on_execute_callback: end.");
    }
    btnExecute() {
        var tablename = this.state.tablename;
        var sqlstring = this.state.sqlstring;
        this.be.execute(tablename,sqlstring,this.on_execute_callback);
    }

    on_select_callback(jsonObj, dbelist) {
        console.log("TestBE.on_select_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        var tmp = [];
        for(var i=0; dbelist!==null && i<dbelist.length; i++) {
            tmp.push(dbelist[i].to_string());
        }
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: tmp.join("\r\n")
        })
        console.log("TestBE.on_select_callback: end.");
    }
    btnSelect() {
        var dbename = this.state.dbename;
        var tablename = this.state.tablename;
        var sqlstring = this.state.sqlstring;
        this.be.select(dbename,tablename,sqlstring,this.on_select_callback);
    }

    render() {
        return (
            <div class="component">
                <div class="row">
                    <div class="col"><h2>TestBE</h2></div>
                </div>
                <div class="row">
                    <div class="col-2 text-start fw-bold">
                        <select name="endpoint" value={this.state.endpoint} onChange={this.endpoint_handleChange}>
                            <option value="http://localhost:8080/jsonserver.php">Local</option>
                            <option value="https://www.roccoangeloni.it/rproject/jsonserver.php">RRA</option>
                            <option value="https://echocloud.doesntexist.com/jsonserver.php">Echo Cloud</option>
                            <option value="https://www.africa-film.com/jsonserver.php">Africa Film</option>
                        </select>
                        &nbsp;{this.state.connected}
                    </div>
                    <div class="col text-middle">{this.prova}</div>
                    <div class="col-2 text-end">{this.state.date.toLocaleTimeString()}</div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.default_handleSubmit}>
                            <button onClick={this.btnPingServer}>PING</button>
                        </form>
                    </div>
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.default_handleSubmit}>
                            Username: <input id="usr" name="usr" value={this.state.usr} onChange={this.default_handleChange} /> <br />
                            Password: <input id="pwd" name="pwd" type="password" value={this.state.pwd} onChange={this.default_handleChange} /> <br />
                            <button onClick={this.btnLogin}>Login</button>
                        </form>
                    </div>
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.default_handleSubmit}>
                            Get Logged User <button onClick={this.btnLoggedUser}>Fetch</button>
                        </form>
                    </div>
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.default_handleSubmit}>
                            <button onClick={this.btnLogout}>Logout</button>
                        </form>
                    </div>
                </div>
                <div class="row test-start">
                    <div class="col">User: {this.state.user}</div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col text-start align-top">
                        <form onSubmit={this.default_handleSubmit}>
                            <div class="container">
                                <div class="row">
                                    <div class="col-1 fw-bold text-end">DBE</div>
                                    <div class="col text-start">
                                        <input id="tablename" name="tablename" value={this.state.dbename} onChange={this.default_handleChange} />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-1 fw-bold text-end">Table</div>
                                    <div class="col text-start">
                                        <input id="tablename" name="tablename" value={this.state.tablename} onChange={this.default_handleChange} />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-1 fw-bold text-end">SQL</div>
                                    <div class="col text-start">
                                        <textarea id="sqlstring" name="sqlstring" value={this.state.sqlstring} onChange={this.default_handleChange} />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col text-start">
                                        <button onClick={this.btnExecute}>Select as Array</button>
                                        <button onClick={this.btnSelect}>Select</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="component border rounded">
                            <div class="row">
                                <div class="col-1">Message:</div>
                                <div class="col text-start"><pre>{this.state.server_response_0}</pre></div>
                            </div>
                            <div class="row">
                                <div class="col-1">Response:</div>
                                <div class="col text-start">{this.state.server_response_1}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
            </div>
        );
    }
}

export default TestBE;
