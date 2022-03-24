import React from 'react';

class ServerResponse extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div class={"component "+this.props.class}>
                <div class="row">
                    <div class="col-1">Message:</div>
                    <div class="col text-start"><pre>{this.props.server_response_0}</pre></div>
                </div>
                <div class="row">
                    <div class="col-1">Response:</div>
                    <div class="col text-start"><pre>{this.props.server_response_1}</pre></div>
                </div>
            </div>
        );
    }
}

export { ServerResponse };
