import React, { Component } from 'react'

import './App.scss';

import { app_cfg } from './app.cgf';
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
    const mystate = this.ls.getMyState();
    console.log("App.constructor: mystate="+JSON.stringify(mystate));
    this.setState(mystate);

    if("dark_theme" in mystate) {
      this._addDarkThemeToBody(mystate["dark_theme"]);
    } else {
      this._addDarkThemeToBody(this.state.dark_theme);
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
    // console.log("App.on_fetchuser_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''});
    // console.log("App.on_fetchuser_callback: end.");
  }
  fetchLoggedUser() {
    // console.log("App.fetchLoggedUser: start.");
    // console.log("App.fetchLoggedUser: be="+JSON.stringify(this.be));
    this.be.getLoggedUser(this.on_fetchuser_callback);
    // console.log("App.fetchLoggedUser: end.");
  }

  on_login_callback(jsonObj) {
    // console.log("App.on_login_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    this.setState({user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''});
    // console.log("App.on_login_callback: end.");
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

  _addDarkThemeToBody(dark_theme) {
    if(dark_theme) {
      document.body.classList.add('App-dark');
    } else {
      document.body.classList.remove('App-dark');
    }
  }
  onTheme(dark_theme) {
    this.ls.setValue("dark_theme", dark_theme);
    this.setState({dark_theme: dark_theme});
    this._addDarkThemeToBody(dark_theme);
  }

  parsePath() {
    console.log("App.parsePath: root_path="+app_cfg.root_path);
    console.log("App.parsePath: window.location.pathname="+window.location.pathname);
    const mypath = window.location.pathname.substring(app_cfg.root_path.length)
    console.log("App.parsePath: mypath="+mypath);
    return mypath.split("/");
  }
  renderTest() {
    return (
      <TestBE endpoint={this.state.endpoint} dark_theme={this.state.dark_theme} endpoints={app_cfg.endpoints} />
    );
  }
  render() {
    const endpoints = app_cfg.endpoints;
    const dark_theme = this.state.dark_theme;
    console.log("App.render: dark_theme="+dark_theme);
    const mypath = this.parsePath()
    console.log("App.render: mypath="+JSON.stringify(mypath));
    return (
      <div className={"App" + (this.state.dark_theme ? " App-dark":'')}>
        <RNav dark_theme={this.state.dark_theme} user_fullname={this.state.user_fullname}
          onLogin={this.onLogin} onLogout={this.onLogout} onTheme={this.onTheme} />
        <div class="container-fluid p-3">{
          mypath[0]=="test" ?
          this.renderTest()
            :
            (
              <span><img src="logo512_2.png" /><img src="logo192_2.png" /></span>
            )
          }
          
        </div>
      </div>
    );
  }
}


export default App;
