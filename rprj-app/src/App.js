import React, { Component } from 'react'

import logo from './logo.svg';
import './App.scss';

import { DBMgr } from './dblayer';
import RNav from './comp.nav';

class App extends Component {

  dbmgr = null

  constructor() {
    super()

    this.dbmgr = new DBMgr;
    this.msg = ''
    this.abody = 'mo zao'

    // See: https://it.reactjs.org/
    this.state = { value: 'Ciao, mondo!' };

    //this.handleChange = this.handleChange.bind(this);
  }

  componentDidMount() {
    var self = this
    var a_callback = (function(xhr) {
      console.log("SUNCHI"+this.abody)
      // update the state of the component with the result here
      console.log(xhr.responseText)
      const jsonObj = JSON.parse(xhr.responseText)
      this.abody = jsonObj[1]
      this.msg = atob(jsonObj[0])
      console.log("SUNCHI"+this.abody)

      this.setState({ value: jsonObj[1] })

    }).bind(this)
    this.dbmgr.ping(a_callback);
  }

  //handleChange(e) {
  //  this.setState({ value: e.target.value })
  //}

  render() {
    return (
      <div className="App">
        <header className="App-header">
          <RNav/>
        </header>
        Cippa Lippa
        <div class="container">
          Msg: {this.msg}
          <hr/>
          {this.state.value}
          <hr/>
        </div>
      </div>
    );
    }
}


export default App;
