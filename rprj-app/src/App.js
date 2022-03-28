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
      endpoint: "http://localhost:8080/jsonserver.php",

      user_fullname: ''
    };

    this.be = new BackEndProxy(this.state.endpoint);

    // Init stuff
    this.be.ping();
    this.be.getLoggedUser();

    this.default_callback = this.default_callback.bind(this);

    this.on_login_callback = this.on_login_callback.bind(this);
    this.onLogin = this.onLogin.bind(this);

    this.on_logout_callback = this.on_logout_callback.bind(this);
    this.onLogout = this.onLogout.bind(this);

    this.on_fetchuser_callback = this.on_fetchuser_callback.bind(this);
    this.fetchLoggedUser = this.fetchLoggedUser.bind(this);
  }

  componentDidMount() {
    this.fetchLoggedUser();
    this.pingID = setInterval(
        // () => this.be.ping(this.on_ping_callback),
        () => this.fetchLoggedUser(),
        20 * 1000                   // Better 60 seconds?
    );
}

  default_callback(jsonObj) {
    // console.log("App.on_btn_ping_callback: start.");
    // console.log(jsonObj)
    // console.log(this.be.isConnected())
    // console.log("App.on_btn_ping_callback: end.");
  }

  on_fetchuser_callback(jsonObj) {
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({
        user: tmpUser ? tmpUser.getValue('fullname') + " " + tmpUser.to_string() : ''
    })
  }
  fetchLoggedUser() {
    this.be.getLoggedUser(this.on_fetchuser_callback);
  }

  on_login_callback(jsonObj) {
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({
        user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''
    })
  }
  onLogin(usr,pwd) {
    this.be.login(usr,pwd,this.on_login_callback);
  }

  on_logout_callback(jsonObj) {
    this.setState({user_fullname: ''})
  }
  onLogout() {
    this.be.logout(this.on_logout_callback);
  }

  render() {
    return (
      <div className="App">
        <RNav user_fullname={this.state.user_fullname}
          onLogin={this.onLogin} onLogout={this.onLogout} />
        <div class="container">
          <TestBE endpoint={this.state.endpoint} ref={this.myRef} />
        </div>
      </div>
    );
  }
}


export default App;
