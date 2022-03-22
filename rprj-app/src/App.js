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
    this.state = { value: 'Ciao, mondo!' };
  }

  componentDidMount() {
  }

  render() {
    return (
      <div className="App">
        <RNav/>
        <div class="container">
          <TestBE endpoint="http://localhost:8080/jsonserver.php" ref={this.myRef} />
        </div>
      </div>
    );
  }
}


export default App;
