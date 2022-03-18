import React from 'react';

import { BackEndProxy } from './be';
import { JSONDBConnection } from './db/dblayer'

class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            date: new Date(),
            msg: 'di qualcosa.',
            ping_response_0: '--',
            ping_response_1: '--'
        }

        this.be = new BackEndProxy();

        this.on_ping_callback = this.on_ping_callback.bind(this);
        this.btnPingServer = this.btnPingServer.bind(this);
    }

    componentDidMount() {
        // this.timerID = setInterval(
        //     () => this.tick(),
        //     1000
        // );
    }

    componentWillUnmount() {
        // clearInterval(this.timerID);
    }

    tick() {
        this.setState({
            date: new Date()
        })
    }

    ping_handleSubmit(event) {
        // alert('Blocked submits: ' + event);
        // alert(this.be);
        event.preventDefault();
    }
    
    on_ping_callback(jsonObj) {
        console.log("TestBE.on_ping_callback: start.");
        // update the state of the component with the result here
        console.log(jsonObj)    
        this.setState({ ping_response_0: jsonObj[0], ping_response_1: jsonObj[1] })
        console.log("TestBE.on_ping_callback: end.");
    }
    btnPingServer() {
        console.log("TestBE.btnPingServer: start.");
        this.be.ping(this.on_ping_callback);
        console.log("TestBE.btnPingServer: end.");
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
                    <div class="col">{this.state.ping_response_0}</div>
                    <div class="col-10 text-start">{this.state.ping_response_1}</div>
                </div>
            </div>
        );
    }
}

export default TestBE;
