import React from 'react';

class TestBE extends React.Component {
    constructor(props) {
        super(props);
        this.prova = props.prova
        this.state = {
            date: new Date(),
            msg: 'di qualcosa.'
        }
    }

    componentDidMount() {
        this.timerID = setInterval(
            () => this.tick(),
            1000
        );
    }

    componentWillUnmount() {
        clearInterval(this.timerID);
    }

    tick() {
        this.setState({
            date: new Date()
        })
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
            </div>
        );
    }
}

export default TestBE;
