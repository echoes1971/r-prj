import React, { Component } from 'react'

import './App.scss';

import { app_cfg } from './app.cgf';
import { FForm } from './comp.fform';
import { RLocalStorage } from './comp.ls';
import { BackEndProxy } from './be';
import RNav from './comp.nav';
import TestBE from './comp.test.be';
import { ServerResponse } from './comp.test.serverresponse';
import { IFRTree, IFRTreeAll } from './comp.ui.elements';
import { DBEntity } from './db/dblayer';
// import { DBEntity } from './db/dblayer';

class App extends Component {

  be = null

  constructor(props) {
    super(props)

    // See: https://it.reactjs.org/
    this.state = {
      endpoint: props.endpoint // "http://localhost:8080/jsonserver.php",
      ,dark_theme: props.dark_theme

      ,user_fullname: ''
      ,user_groups: []
      ,user_is_admin: false
      ,user_profile: null
      
      ,root_obj: null
      ,top_menu: []

      ,formname: null //'FObject'
      ,dbename: null //'DBEObject'
      ,current_obj: null
      ,children: []
    };

    this.dbe2formMapping = {};

    this.default_callback = this.default_callback.bind(this);

    this.on_login_callback = this.on_login_callback.bind(this);
    this.onLogin = this.onLogin.bind(this);

    this.on_logout_callback = this.on_logout_callback.bind(this);
    this.onLogout = this.onLogout.bind(this);

    this.on_fetchuser_callback = this.on_fetchuser_callback.bind(this);
    this.fetchLoggedUser = this.fetchLoggedUser.bind(this);

    this.onTheme = this.onTheme.bind(this);

    this.rootobj_cb = this.rootobj_cb.bind(this);
    this.topmenu_cb = this.topmenu_cb.bind(this);

    this.dbe2form_cb = this.dbe2form_cb.bind(this);

    this.currentobj_cb = this.currentobj_cb.bind(this);
    this.children_cb = this.children_cb.bind(this);

    this.onError = this.onError.bind(this)
    this.onSave = this.onSave.bind(this)
  }

  componentDidMount() {
    console.log("App.componentDidMount: start.");

    // Local Storage
    this.ls = new RLocalStorage("App");
    const mystate = this.ls.getMyState();
    // console.log("App.constructor: mystate="+JSON.stringify(mystate));
    this.setState(mystate);

    if("dark_theme" in mystate) {
      this._addDarkThemeToBody(mystate["dark_theme"]);
    } else {
      this._addDarkThemeToBody(this.state.dark_theme);
    }

    this.be = new BackEndProxy(this.state.endpoint);

    this.fetchLoggedUser();
    this.be.getRootObj(this.rootobj_cb);

    this.be.getDBE2FormMapping(this.dbe2form_cb);

    const args = this.parsePath();
    switch(args[0]) {
      case 'e':
      case 'o':
        // Fetch the current object and its children
        this.be.fullObjectById(args[1],false,this.currentobj_cb);
        break;
      case 'p':
        // User profile
        // this.fetchUserProfile()
      case 's':
        // execute the search
      case 'management':
        // no idea yet
      default:
        break;
    }

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

  dbe2form_cb(jsonObj, dbe2formMapping) {
    // console.log("App.dbe2form_cb: dbe2formMapping="+JSON.stringify(dbe2formMapping))
    if(!dbe2formMapping) {
      return;
    }
    this.dbe2formMapping = dbe2formMapping;

    const current_obj = this.state.current_obj;
    console.log("App.dbe2form_cb: current_obj="+current_obj)
    if(current_obj===null || current_obj===undefined) return;
    const dbename = current_obj.getDBEName()
    console.log("App.dbe2form_cb: dbename="+dbename)
    if(!(dbename>'')) return;
    if(!(dbename in this.dbe2formMapping)) return;
    const formname = this.dbe2formMapping[dbename];
    console.log("App.dbe2form_cb: formname="+formname)
    if(formname===null || formname===undefined) return;
    this.setState({formname: formname});
  }

  currentobj_cb(jsonObj, myobj) {
    const current_obj = myobj
    if(current_obj===null) {
      console.log("App.currentobj_cb: current_obj not found or user has not the right to view it.");
      return
    }
    const dbename = current_obj.getDBEName()
    console.log("App.currentobj_cb: dbename="+dbename)
    const formname = this.be.getFormNameByDBEName(dbename);
    console.log("App.currentobj_cb: formname="+formname)
    this.setState({current_obj: current_obj, formname: formname, dbename: dbename});
    console.log("App.currentobj_cb: current_obj="+(current_obj ? current_obj.to_string() : '--'))

    this.setState({server_response_0: jsonObj[0],server_response_1: JSON.stringify(jsonObj[1],null,2)})

    // Load the children AFTER the object has been returned by the back-end
    this.be.getChilds(current_obj,false,this.children_cb);
  }
  children_cb(jsonObj, dbelist) {
    // console.log("App.children_cb: dbelist="+JSON.stringify(dbelist));
    const children = dbelist
    console.log("App.children_cb: children="+JSON.stringify(children));
    this.setState({children: children})
  }


  rootobj_cb(jsonObj, myobj) {
    this.tmp_root_obj = myobj;
    // console.log("App.rootobj_cb: myobj="+(myobj ? myobj.to_string() : '--'))
    // this.setState({root_obj: myobj})
    this.be.getChilds(myobj,true,this.topmenu_cb);
  }
  topmenu_cb(jsonObj, dbelist) {
    const top_menu = dbelist
    // console.log("App.rootobj_cb: top_menu="+JSON.stringify(top_menu));
    const root_obj = this.tmp_root_obj
    // console.log("App.rootobj_cb: root_obj="+JSON.stringify(root_obj));
    this.setState({root_obj: root_obj, top_menu: top_menu})
  }

  on_fetchuser_callback(jsonObj) {
    console.log("App.on_fetchuser_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    const user_groups = this.be.getUserGroupsList();
    const user_is_admin = this.be.isAdmin();
    this.setState({
       user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''
      ,user_groups: user_groups, user_is_admin: user_is_admin
    });

    this.fetchUserProfile();
    console.log("App.on_fetchuser_callback: end.");
  }
  fetchLoggedUser() {
    // console.log("App.fetchLoggedUser: start.");
    this.be.getLoggedUser(this.on_fetchuser_callback);
    // console.log("App.fetchLoggedUser: end.");
  }

  fetchUserProfile() {
    console.log("App.fetchUserProfile: start.")
    const args = this.parsePath()
    console.log("App.fetchUserProfile.cb: args="+JSON.stringify(args))
    const is_current_object = args.length>1 && args[0]==='p' && (args[1]===undefined || args[1]==='')
    var self = this
    this.be.fetchUserProfile((user_profile) => {
      console.log("App.fetchUserProfile.cb: user_profile="+JSON.stringify(user_profile))
      const _user_profile = user_profile
      if(_user_profile===null) {
        return
      }
      self.setState({user_profile: _user_profile})
      console.log("App.fetchUserProfile.cb: is_current_object="+is_current_object)
      if(!is_current_object) return;
      // IF the profile IS the current object
      const current_obj = user_profile
      const dbename = current_obj.getDBEName()
      console.log("App.fetchUserProfile.cb: dbename="+dbename)
      const formname = self.be.getFormNameByDBEName(dbename);
      console.log("App.fetchUserProfile.cb: formname="+formname)
      this.setState({current_obj: current_obj, formname: formname, dbename: dbename});
      console.log("App.fetchUserProfile.cb: current_obj="+(current_obj ? current_obj.to_string() : '--'))
      // Load the children AFTER the object has been returned by the back-end
      self.be.getChilds(current_obj,false,this.children_cb);
    })
    console.log("App.fetchUserProfile: end.")
  }

  on_login_callback(jsonObj) {
    // console.log("App.on_login_callback: start.");
    const tmpUser = this.be.getDBEUserFromConnection();
    const user_groups = this.be.getUserGroupsList();
    const user_is_admin = this.be.isAdmin();
    this.setState({
      user_fullname: tmpUser ? tmpUser.getValue('fullname') : ''
      ,user_groups: user_groups, user_is_admin: user_is_admin
    });

    this.fetchUserProfile()
    // console.log("App.on_login_callback: end.");
  }
  onLogin(usr,pwd) {
    this.be.login(usr,pwd,this.on_login_callback);
  }

  on_logout_callback(jsonObj) {
    this.setState({user_fullname: '', user_groups: [], user_is_admin: false, user_profile: null})
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

  onError(jsonObj) {
    this.setState({server_response_0: jsonObj[0], server_response_1: JSON.stringify(jsonObj[1])})
  }

  onSave(values) {
    this.setState({server_response_0: '', server_response_1: JSON.stringify(values,null,2)})
  }

  parsePath() {
    // console.log("App.parsePath: root_path="+app_cfg.root_path);
    // console.log("App.parsePath: window.location.pathname="+window.location.pathname);
    const mypath = window.location.pathname.substring(app_cfg.root_path.length)
    // console.log("App.parsePath: mypath="+mypath);
    return mypath.split("/");
  }
  _render(args) {
    switch(args[0]) {
      case 'test':
        return (
          <TestBE endpoint={this.state.endpoint} dark_theme={this.state.dark_theme} endpoints={app_cfg.endpoints} />
        );
        break
      case 'p':
        // User profile
      case 'o':
        // Display the current object
        return (
          <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
            formname={this.state.formname} dbename={this.state.dbename}
            obj={this.state.current_obj} children={this.state.children}
            readonly={true}
            onSave={this.onSave} onError={this.onError} />
          )
      case 'e':
        // Edit the current object
        // TODO: check a user is logged in and has right to edit the object
        var readonly = false
        const user = this.be!== null ? this.be.getDBEUserFromConnection() : null
        const current_obj = this.state.current_obj
        readonly = user===null ? true
            : (!this.be.canRead(current_obj) || !this.be.canWrite(current_obj))
        return (
          <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
            formname={this.state.formname} dbename={this.state.dbename}
            obj={this.state.current_obj} children={this.state.children}
            readonly={readonly}
            onSave={this.onSave} onError={this.onError} />
          )
      case 'manage':
        // Admin-like page of the site, for objects administered by the current user
      case 's':
        // Display search results
      default:
        return (<IFRTree dark_theme={this.state.dark_theme} />)
    }
  }
  render() {
    const endpoints = app_cfg.endpoints;
    const dark_theme = this.state.dark_theme;
    // console.log("App.render: dark_theme="+dark_theme);
    const mypath = this.parsePath()
    // console.log("App.render: mypath="+JSON.stringify(mypath));
    const root_obj = this.state.root_obj
    // console.log("App.render: root_obj="+(root_obj ? root_obj.to_string() : 'null'));
    return (
      <div className={"App" + (this.state.dark_theme ? " App-dark":'')}>
        <RNav dark_theme={this.state.dark_theme}
          user_fullname={this.state.user_fullname} user_is_admin={this.state.user_is_admin}
          user_groups={this.state.user_groups}
          root_obj={this.state.root_obj} top_menu={this.state.top_menu}
          onLogin={this.onLogin} onLogout={this.onLogout} onTheme={this.onTheme} />
        <div class="container-fluid p-3">{ this._render(mypath) }</div>

        <div class="container">

          <div class="row border rounded">
            <div class="col">
              <ServerResponse class=""
                server_response_0={this.state.server_response_0}
                server_response_1={this.state.server_response_1} />
            </div>
          </div>
          

        </div>

      </div>
    );
  }
}


export default App;
