import React from 'react';

import { BackEndProxy } from './be';
import { JSONDBConnection } from './db/dblayer'

class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            connected: "--",
            date: new Date(),
            msg: 'di qualcosa.',
            ping_response_0: '--',
            ping_response_1: '--'
        }

        this.be = new BackEndProxy();

        // Bindings
        this.on_ping_callback = this.on_ping_callback.bind(this);

        this.on_btn_ping_callback = this.on_btn_ping_callback.bind(this);
        this.btnPingServer = this.btnPingServer.bind(this);
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
            ping_response_0: jsonObj[0],
            ping_response_1: jsonObj[1] })
        // console.log("TestBE.on_btn_ping_callback: end.");
    }
    btnPingServer() {
        this.be.ping(this.on_btn_ping_callback);
    }

    render() {
        return (
            <div class="component">
                <div class="row">
                    <div class="col text-start">{this.prova}</div>
                    <div class="col text-end">{this.state.date.toLocaleTimeString()}</div>
                </div>
                <div class="row">
                    <div class="col text-end fw-bold">Message:</div>
                    <div class="col-11 text-start">{this.state.msg}</div>
                </div>
                <div class="row">
                    <div class="col text-start fw-bold">
                        <form onSubmit={this.ping_handleSubmit}>
                            <button onClick={this.btnPingServer}>PING</button>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col">Message:</div>
                    <div class="col-5 text-start"><pre>{this.state.ping_response_0}</pre></div>
                    <div class="col">Response:</div>
                    <div class="col-5 text-start">{this.state.ping_response_1}</div>
                </div>
                <div class="row">
                    <div class="col text-end fw-bold">Connected:</div>
                    <div class="col-11 text-start">{this.state.connected}</div>
                </div>
            </div>
        );
    }
}

export default TestBE;
