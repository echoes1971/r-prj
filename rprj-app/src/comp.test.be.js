import React from 'react';

import { RLocalStorage } from './comp.ls';
import { BackEndProxy } from './be';
import { DBEntity } from './db/dblayer';
import { FormExplorer } from './comp.test.formexplorer';
import { ServerResponse } from './comp.test.serverresponse';


class Ping extends React.Component {
    constructor(props) {
        super(props);

        this.handleSubmit = this.handleSubmit.bind(this);
        this.btnPingServer = this.btnPingServer.bind(this);
    }

    handleSubmit(event) {
        event.preventDefault();
    }

    btnPingServer() {
        this.props.onPingServer();
    }

    render () {
        return (
            <form onSubmit={this.handleSubmit}>
                <button class="btn btn-secondary" onClick={this.btnPingServer}>PING</button>
            </form>
        );
    }
}

function default_handleSubmit(event) {
    event.preventDefault();
}

class SearchForm extends React.Component {
    constructor(props) {
        super(props);

        this.prefix = "SearchForm" + "_";

        this.state = {
            dbename: props.dbename,
            tablename: props.tablename,
            fieldname: props.fieldname,
            fieldvalue: props.fieldvalue,
            uselike: true,
            caseSensitive: false,
            orderBy: props.fieldname
        }

        this.handleChange = this.handleChange.bind(this);
        this.btnSearch = this.btnSearch.bind(this);
    }

    componentDidMount() {
        // Local Storage
        this.ls = new RLocalStorage("TestBE.SearchForm");
        const mystate = this.ls.getMyState();
        this.setState(mystate);
    }

    handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name.replace(this.prefix,"");

        this.ls.setValue(name,value);
        this.setState({[name]: value});

        // Propagate upward if needed
        this.props.onChange(event);
    }

    btnSearch() {
        this.props.onSearch(this.state.dbename,this.state.tablename,this.state.fieldname,this.state.fieldvalue,this.state.uselike,this.state.caseSensitive,this.state.orderBy);
    }

    render() {
        return (
            <form onSubmit={default_handleSubmit}>
                <div class="container">
                    <div class="row">
                        <div class="col fw-bold text-end">DBE</div>
                        <div class="col text-start">
                            <input id={this.prefix + "dbename"} name={this.prefix + "dbename"} value={this.state.dbename} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col fw-bold text-end">Table</div>
                        <div class="col text-start">
                            <input id={this.prefix + "tablename"} name={this.prefix + "tablename"} value={this.state.tablename} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col fw-bold text-end">Field</div>
                        <div class="col text-start">
                            <input id={this.prefix + "fieldname"} name={this.prefix + "fieldname"} value={this.state.fieldname} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col fw-bold text-end">Order by</div>
                        <div class="col text-start">
                            <input id={this.prefix + "orderBy"} name={this.prefix + "orderBy"} value={this.state.orderBy} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col fw-bold text-end">Use like</div>
                        <div class="col text-start">
                            <input id={this.prefix + "uselike"} name={this.prefix + "uselike"} type="checkbox" checked={this.state.uselike} onChange={this.handleChange} />
                        </div>
                        <div class="col fw-bold text-end">Case sensitive</div>
                        <div class="col text-start">
                            <input id={this.prefix + "caseSensitive"} name={this.prefix + "caseSensitive"} type="checkbox" checked={this.state.caseSensitive} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col fw-bold text-end">Value</div>
                        <div class="col text-start">
                            <input id={this.prefix + "fieldvalue"} name={this.prefix + "fieldvalue"} value={this.state.fieldvalue} onChange={this.handleChange} />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-secondary" onClick={this.btnSearch}>Search</button>
                        </div>
                    </div>
                </div>
            </form>
        );
    }
}




class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            endpoint: props.endpoint,
            dark_theme: props.dark_theme,
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
            ,dbeid: '-10'
            ,dbe_name: ''
            ,ignore_deleted: false
        }

        this.be = new BackEndProxy(this.state.endpoint);

        this.endpoints = props.endpoints;
        // this.endpoints = [ ["http://localhost:8080/jsonserver.php","Local"],
        //         ["https://www.roccoangeloni.it/rproject/jsonserver.php","RRA"],
        //         ["https://echocloud.doesntexist.com/jsonserver.php","Echo Cloud"],
        //         ["https://www.africa-film.com/jsonserver.php","Africa Film"]
        //     ];

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

        this.onSearch_callback = this.onSearch_callback.bind(this);
        this.onSearchChange = this.onSearchChange.bind(this);
        this.onSearch = this.onSearch.bind(this);

        this.onObjByID_callback = this.onObjByID_callback.bind(this);
        this.btnObjByID = this.btnObjByID.bind(this);
        this.btnObjByName = this.btnObjByName.bind(this);
        this.onFullObjByID_callback = this.onFullObjByID_callback.bind(this);
        this.btnFullObjByID = this.btnFullObjByID.bind(this);
        this.btnFullObjByName = this.btnFullObjByName.bind(this);
    }

    componentDidMount() {
        // Local Storage
        this.ls = new RLocalStorage("TestBE");
        const mystate = this.ls.getMyState();
        this.setState(mystate);


        // this.timerID = setInterval(
        //     () => this.tick_time(),
        //     1000
        // );

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
    componentDidUpdate(prevProps, prevState) {
        if(this.props.dark_theme !== prevProps.dark_theme) {
            this.setState({dark_theme: this.props.dark_theme})
        }
    }

    tick_time() {
        this.setState({ date: new Date() })
    }

    tick_ping() {
        this.be.ping(this.on_ping_callback);
    }

    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.ls.setValue(name,value);
        this.setState({[name]: value});
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
        this.be.ping(this.on_ping_callback);
        this.setState({endpoint: event.target.value});
    }

    on_login_callback(jsonObj) {
        const tmpUser = this.be.getDBEUserFromConnection();
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: JSON.stringify(jsonObj[1]),
            user: tmpUser ? tmpUser.getValue('fullname') + " " + tmpUser.to_string() : ''
        })
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


    onSearch_callback(server_messages, dbelist) {
        console.log("TestBE.onSearch_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        var tmp = [];
        for(var i=0; dbelist!==null && i<dbelist.length; i++) {
            tmp.push(dbelist[i].to_string());
        }
        this.setState({
            server_response_0: server_messages,
            server_response_1: tmp.join("\r\n")
        })
        console.log("TestBE.onSearch_callback: end.");
    }
    onSearchChange(event) {
        console.log(event);
    }
    onSearch(dbename,tablename,fieldname,fieldvalue,uselike,caseSensitive,orderBy) {
        console.log("onSearch("+dbename+","+tablename+","+fieldname+","+fieldvalue+","+uselike+","+caseSensitive+","+orderBy+")");
        var search = new DBEntity(dbename,tablename);
        search.setValue(fieldname,fieldvalue);
        console.log("search="+search.to_string());

        this.be.search(search, uselike, caseSensitive, orderBy, this.on_select_callback);
    }

    onObjByID_callback(jsonObj,myobj) {
        console.log("TestBE.onObjByID_callback: start.");
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: myobj!==null ? myobj.to_string() : '--' // JSON.stringify(jsonObj[1])
        })
        console.log("TestBE.onObjByID_callback: end.");
    }
    btnObjByID() {
        this.be.objectById(this.state.dbeid, this.state.ignore_deleted, this.onObjByID_callback)
    }
    btnObjByName() {
        this.be.objectByName(this.state.dbe_name, this.state.ignore_deleted, this.onObjByID_callback)
    }
    onFullObjByID_callback(jsonObj,myobj) {
        console.log("TestBE.onFullObjByID_callback: start.");
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: myobj!==null ? myobj.to_string() : '--' // JSON.stringify(jsonObj[1])
        })
        console.log("TestBE.onFullObjByID_callback: end.");
    }
    btnFullObjByID() {
        this.be.fullObjectById(this.state.dbeid, this.state.ignore_deleted, this.onFullObjByID_callback)
    }
    btnFullObjByName() {
        this.be.fullObjectByName(this.state.dbe_name, this.state.ignore_deleted, this.onFullObjByID_callback)
    }

    render() {
        const endpoints = this.endpoints;
        const dark_theme = this.state.dark_theme;
        console.log("TestBE.render: dark_theme="+dark_theme);
        return (
            <div class="component">
                <div class="row">
                    <div class="col text-center"><h2>TestBE</h2></div>
                </div>
                <div class="row">
                    <div class="col-2 text-start fw-bold">
                        <select name="endpoint" value={this.state.endpoint} onChange={this.endpoint_handleChange}>{
                            endpoints.map((k) => {
                                return (<option value={k[0]}>{k[1]}</option>);
                            })
                        }</select>
                        &nbsp;{this.state.connected}
                    </div>
                    <div class="col text-middle">{this.prova}</div>
                    <div class="col-2 text-end">{this.state.date.toLocaleTimeString()}</div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="btn-group" role="group" aria-label="Test Modules">
                            <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#test_formexplorer" aria-expanded="false" aria-controls="test_formexplorer">Form</button>
                            <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#test_dblayer" aria-expanded="false" aria-controls="test_dblayer">DB Layer</button>
                            <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="test_formexplorer test_dblayer">All</button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>

                <div class="row collapse multi-collapse " id="test_formexplorer">
                    <div class={"col card card-body" + (dark_theme ? " card-dark" : "")}>
                        <FormExplorer endpoint={this.state.endpoint} />
                    </div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>

                <div class="row collapse multi-collapse show" id="test_dblayer">
                    <div class={"component card card-body" + (this.state.dark_theme ? " card-dark" : "")}>
                        <div class="row">
                            <div class="col text-middle fw-bold">DBLayer</div>
                        </div>
                        <div class="row">
                            <div class="col">&nbsp;</div>
                        </div>
                        <div class="row border rounded p-2 m-2">
                            <div class="col text-start fw-bold">
                                <Ping onPingServer={this.btnPingServer} />
                            </div>

                            <div class="col border rounded p-2 m-2">
                                <div class="component fw-bold">
                                    <div class="row">
                                        <div class="col text-start">
                                            <form onSubmit={this.default_handleSubmit}>
                                                Username: <input id="usr" name="usr" value={this.state.usr} onChange={this.default_handleChange} /> <br />
                                                Password: <input id="pwd" name="pwd" type="password" value={this.state.pwd} onChange={this.default_handleChange} /> <br />
                                                <button class="btn btn-secondary" onClick={this.btnLogin}>Login</button>
                                            </form>
                                        </div>
                                        <div class="col text-center">
                                            <form onSubmit={this.default_handleSubmit}>
                                                Get Logged User <button class="btn btn-secondary" onClick={this.btnLoggedUser}>Fetch</button>
                                            </form>
                                        </div>
                                        <div class="col text-center">
                                            <form onSubmit={this.default_handleSubmit}>
                                                <button class="btn btn-secondary" onClick={this.btnLogout}>Logout</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="row test-start border rounded p-2 m-2">
                                        <div class="col">User: {this.state.user}</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">&nbsp;</div>
                        </div>
                        <div class="row border rounded p-2 m-2">
                            <div class="col text-start align-top border rounded p-2 m-2">
                                <form onSubmit={this.default_handleSubmit}>
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-3 fw-bold text-end">ID</div>
                                            <div class="col text-start">
                                                <input id="dbeid" name="dbeid" value={this.state.dbeid} onChange={this.default_handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3 fw-bold text-end">Name</div>
                                            <div class="col text-start">
                                                <input id="dbe_name" name="dbe_name" value={this.state.dbe_name} onChange={this.default_handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col fw-bold text-end">Ignore Deleted</div>
                                            <div class="col text-start">
                                                <input id="ignore_deleted" name="ignore_deleted" type="checkbox" checked={this.state.ignore_deleted} onChange={this.handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col text-start">&nbsp;
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col text-center">
                                                <button class="btn btn-secondary" onClick={this.btnObjByID}>Obj. by ID</button>
                                                <button class="btn btn-secondary" onClick={this.btnFullObjByID}>Full Obj. by ID</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col text-center">
                                                <button class="btn btn-secondary" onClick={this.btnObjByName}>Obj. by Name</button>
                                                <button class="btn btn-secondary" onClick={this.btnFullObjByName}>Full Obj. by Name</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>

                            <div class="col text-start align-top border rounded p-2 m-2">
                                <SearchForm
                                    dbename="DBEFolder" tablename="folders"
                                    fieldname="name" fieldvalue="ome"
                                    onChange={this.onSearchChange} onSearch={this.onSearch}
                                />
                            </div>

                            <div class="col text-start align-top border rounded p-2 m-2">
                                <form onSubmit={this.default_handleSubmit}>
                                    <div class="container">
                                        <div class="row">
                                            <div class="col fw-bold text-end">DBE</div>
                                            <div class="col text-start">
                                                <input id="dbename" name="dbename" value={this.state.dbename} onChange={this.default_handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col fw-bold text-end">Table</div>
                                            <div class="col text-start">
                                                <input id="tablename" name="tablename" value={this.state.tablename} onChange={this.default_handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col fw-bold text-end">SQL</div>
                                            <div class="col text-start">
                                                <textarea id="sqlstring" name="sqlstring" value={this.state.sqlstring} onChange={this.default_handleChange} />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col text-start">
                                                <button class="btn btn-secondary" onClick={this.btnExecute}>Select as Array</button>
                                                <button class="btn btn-secondary" onClick={this.btnSelect}>Select</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col">&nbsp;</div>
                        </div>
                        <div class="row border rounded p-2 m-2">
                            <div class="col">
                                <ServerResponse 
                                    server_response_0={this.state.server_response_0}
                                    server_response_1={this.state.server_response_1} />
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
