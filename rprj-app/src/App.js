import React, { Component } from 'react'

import './App.scss';

import { RLocalStorage } from './comp.ls';
import { BackEndProxy } from './be';
import RNav from './comp.nav';
import TestBE from './comp.test.be';

class App extends Component {

  be = null

  constructor(props) {
    super(props)

    // See: https://it.reactjs.org/
    this.state = {
      endpoint: props.endpoint // "http://localhost:8080/jsonserver.php",
      ,dark_theme: props.dark_theme
      ,user_fullname: ''
    };

    this.default_callback = this.default_callback.bind(this);

    this.on_login_callback = this.on_login_callback.bind(this);
    this.onLogin = this.onLogin.bind(this);

    this.on_logout_callback = this.on_logout_callback.bind(this);
    this.onLogout = this.onLogout.bind(this);

    this.on_fetchuser_callback = this.on_fetchuser_callback.bind(this);
    this.fetchLoggedUser = this.fetchLoggedUser.bind(this);

    this.onTheme = this.onTheme.bind(this);
  }

  componentDidMount() {
    console.log("App.componentDidMount: start.");

    // Local Storage
    this.ls = new RLocalStorage("App");
    const ls_endpoint = this.ls.getValue("endpoint");
    if(ls_endpoint!==null && ls_endpoint!==this.state.endpoint) {
      this.setState({endpoint: ls_endpoint});
    }
    const ls_dark_theme = this.ls.getValue("dark_theme");
    if(ls_dark_theme!==null && ls_dark_theme!==this.state.dark_theme) {
      this.setState({dark_theme: ls_dark_theme});
    }

    this.be = new BackEndProxy(this.state.endpoint);

    this.fetchLoggedUser();
    // this.be.getLoggedUser(this.on_fetchuser_callback);

    this.pingUser = setInterval(
        // () => this.be.ping(this.on_ping_callback),
        () => { this.fetchLoggedUser() },
        30 * 1000                   // Better 60 seconds?
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
    console.log("App.fetchLoggedUser: be="+JSON.stringify(this.be));
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

  onTheme(dark_theme) {
    this.ls.setValue("dark_theme", dark_theme);
    this.setState({dark_theme: dark_theme})
  }

  render() {
    return (
      <div className={"App" + (this.state.dark_theme ? " App-dark":'')}>
        <RNav dark_theme={this.state.dark_theme} user_fullname={this.state.user_fullname}
          onLogin={this.onLogin} onLogout={this.onLogout} onTheme={this.onTheme} />
        <div class="container">
          <TestBE endpoint={this.state.endpoint} dark_theme={this.state.dark_theme} />
        </div>
      </div>
    );
  }
}


export default App;
