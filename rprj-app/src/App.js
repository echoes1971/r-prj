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

    this.default_callback = this.default_callback.bind(this);

    this.on_login_callback = this.on_login_callback.bind(this);
    this.onLogin = this.onLogin.bind(this);

    this.on_logout_callback = this.on_logout_callback.bind(this);
    this.onLogout = this.onLogout.bind(this);

    this.on_fetchuser_callback = this.on_fetchuser_callback.bind(this);
    this.fetchLoggedUser = this.fetchLoggedUser.bind(this);

  }

  componentDidMount() {
    console.log("App.componentDidMount: start.");

    this.be = new BackEndProxy(this.state.endpoint);


    this.fetchLoggedUser();
    // this.be.getLoggedUser(this.on_fetchuser_callback);

    this.pingUser = setInterval(
        // () => this.be.ping(this.on_ping_callback),
        () => this.fetchLoggedUser,
        10 * 1000                   // Better 60 seconds?
    );
    console.log("App.componentDidMount: end.");
  }
  componentWillUnmount() {
    clearInterval(this.pingUser);
  }

  default_callback(jsonObj) {
    // console.log(jsonObj)
  }

  on_fetchuser_callback(jsonObj) {
    console.log("App.on_fetchuser_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''});
    console.log("App.on_fetchuser_callback: end.");
  }
  fetchLoggedUser() {
    console.log("App.fetchLoggedUser: start.");
    this.be.getLoggedUser(this.on_fetchuser_callback);
    console.log("App.fetchLoggedUser: end.");
  }

  on_login_callback(jsonObj) {
    console.log("App.on_login_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''});
    console.log("App.on_login_callback: end.");
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
