import React from 'react';

import { BackEndProxy } from './be';

class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            connected: "--",
            date: new Date(),
            user: "--",
            usr: '',
            pwd: '',
            server_response_0: '--',
            server_response_1: '--'
        }

        this.be = new BackEndProxy();

        // Bindings
        this.default_server_callback = this.default_server_callback.bind(this);
        this.on_ping_callback = this.on_ping_callback.bind(this);

        this.login_handleChange = this.login_handleChange.bind(this);
        this.login_handleSubmit = this.login_handleSubmit.bind(this);
        this.on_login_callback = this.on_login_callback.bind(this);

        this.on_btn_ping_callback = this.on_btn_ping_callback.bind(this);
        this.btnPingServer = this.btnPingServer.bind(this);

        this.btnLogin = this.btnLogin.bind(this);
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
            15 * 1000                   // Better 60 seconds?
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

    ping_handleSubmit(event) {
        event.preventDefault();
    }
    login_handleSubmit(event) {
        event.preventDefault();
    }
    login_handleChange(event) {
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

    on_ping_callback(jsonObj) {
        // console.log("TestBE.on_ping_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        this.setState({
            connected: this.be.isConnected() ? "True" : "False"
        })
        // console.log("TestBE.on_ping_callback: end.");
    }
    on_btn_ping_callback(jsonObj) {
        // console.log("TestBE.on_btn_ping_callback: start.");
        // console.log(jsonObj)
        // console.log(this.be.isConnected())
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: jsonObj[1] })
        // console.log("TestBE.on_btn_ping_callback: end.");
    }
    btnPingServer() {
        this.be.ping(this.on_btn_ping_callback);
    }

    btnLogin() {
        var user = this.state.usr;
        var pwd = this.state.pwd;
        this.be.login(user,pwd,this.on_login_callback);
    }

    render() {
        return (
            <div class="component">
                <div class="row">
                    <div class="col"><h2>TestBE</h2></div>
                </div>
                <div class="row">
                    <div class="col-1 text-end fw-bold">Connected:</div>
                    <div class="col-1 text-start">{this.state.connected}</div>
                    <div class="col text-middle">{this.prova}</div>
                    <div class="col-2 text-end">{this.state.date.toLocaleTimeString()}</div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.ping_handleSubmit}>
                            <button onClick={this.btnPingServer}>PING</button>
                        </form>
                    </div>
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.login_handleSubmit}>
                            Username: <input id="usr" name="usr" value={this.state.usr} onChange={this.login_handleChange} />
                            Password: <input id="pwd" name="pwd" type="password" value={this.state.pwd} onChange={this.login_handleChange} />
                            <button onClick={this.btnLogin}>Login</button>
                        </form>
                        User: {this.state.user}
                    </div>
                </div>
                <div class="row">
                    <div class="col">&nbsp;</div>
                </div>
                <div class="row">
                    <div class="col-1">Message:</div>
                    <div class="col text-start"><pre>{this.state.server_response_0}</pre></div>
                </div>
                <div class="row">
                    <div class="col-1">Response:</div>
                    <div class="col text-start">{this.state.server_response_1}</div>
                </div>
                <div class="row">
                </div>
            </div>
        );
    }
}

export default TestBE;
