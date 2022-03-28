import React, { Component } from 'react'

import logo from './logo.svg';
import './App.scss';

import { BackEndProxy } from './be';
import RNav from './comp.nav';
import TestBE from './comp.test.be';

class App extends Component {

  be = null

  constructor() {
    super()

    // See: https://it.reactjs.org/
    this.state = {
      endpoint: "http://localhost:8080/jsonserver.php"
    };

    this.be = new BackEndProxy(this.state.endpoint);

    // Init stuff
    this.be.ping()
    this.be.getLoggedUser()
  }

  componentDidMount() {
  }

  render() {
    const dbeuser = this.be.getDBEUserFromConnection();
    const user_fullname = dbeuser!==null && dbeuser!==undefined ? dbeuser.getValue("fullname") : null;
    return (
      <div className="App">
        <RNav user_fullname={user_fullname} />
        <div class="container">
          <TestBE endpoint={this.state.endpoint} ref={this.myRef} />
        </div>
      </div>
    );
  }
}


export default App;
