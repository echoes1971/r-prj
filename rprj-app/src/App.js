import React, { Component } from 'react'

import './App.scss';

import { app_cfg } from './app.cfg';
import { FForm } from './comp.fform';
import { RLocalStorage } from './comp.ls';
import { BackEndProxy } from './be';
import RNav from './comp.nav';
import TestBE from './comp.test.be';
import { ServerResponse } from './comp.test.serverresponse';
import { IFRTree } from './comp.ui.elements';
// import { DBEntity } from './db/dblayer';

class App extends Component {

  be = null

  constructor(props) {
    super(props)

    // See: https://it.reactjs.org/
    this.state = {
      endpoint: props.endpoint // "http://localhost:8080/jsonserver.php",
      ,endpoint_download: props.endpoint_download
      ,dark_theme: props.dark_theme

      ,user_fullname: ''
      ,user_groups: []
      ,user_is_admin: false
      ,user_profile: null

      ,site_title: app_cfg.site_title || 'R-Prj'
      
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
    this.onDelete = this.onDelete.bind(this)
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
    console.log("App.componentDidMount: args="+JSON.stringify(args))
    switch(args[0]) {
      case 'c':
        const fk_obj_id = args[1]
        const mydbename = args[2]
        const myformname = args[3]
        this.setState({formname: myformname, dbename: mydbename});
        
        this.be.getNewDBEInstance(mydbename, fk_obj_id,(jsonObj, mydbe) => {
          const current_obj = mydbe
          const dbename = current_obj.getDBEName()
          console.log("App.c.componentDidMount.cb: dbename="+dbename)
          const formname = this.be.getFormNameByDBEName(dbename);
          console.log("App.c.componentDidMount.cb: formname="+formname)
          this.setState({current_obj: current_obj, formname: formname, dbename: dbename});
          // this.setState({current_obj: current_obj, formname: formname, dbename: dbename});
          console.log("App.c.componentDidMount.cb: current_obj="+(current_obj ? current_obj.to_string() : '--'))
      
          this.setState({server_response_0: jsonObj[0],server_response_1: JSON.stringify(jsonObj[1],null,2)})
        })

        // this.be.fullObjectById(args[1],false,this.currentobj_cb);
        break;
      case 'e':
      case 'o':
        // Fetch the current object and its children
        this.be.fullDBEById(args[1],false,this.currentobj_cb);
        // this.be.fullObjectById(args[1],false,this.currentobj_cb);
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
    // console.log("App.children_cb: children="+JSON.stringify(children));
    this.setState({children: children})
  }


  rootobj_cb(jsonObj, myobj) {
    this.tmp_root_obj = myobj;
    // console.log("App.rootobj_cb: myobj="+(myobj ? myobj.to_string() : '--'))
    this.setState({root_obj: myobj})
    if(myobj===null) return;
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
    const dbename = this.state.current_obj.dbename
    const tablename = this.state.current_obj.tablename
    console.log("App.onSave: dbename="+dbename)
    console.log("App.onSave: tablename="+tablename)
    const mydbe = this.state.current_obj
    console.log("App.onSave: mydbe="+mydbe.to_string())
    mydbe.setValues(values)
    console.log("App.onSave: mydbe="+mydbe.to_string())
    console.log("App.onSave: mydbe.isNew()="+mydbe.isNew())
    // - send it to the backend
    if(mydbe.isNew()) {
      this.be.insert(mydbe, (jsonObj, myobj) => {
        console.log("App.onSave.insert.cb: myobj="+myobj.to_string())
        const link = app_cfg.root_path + "e/" + myobj.getValue('id') + "/"
        // window.location = link;
        window.history.replaceState(null, myobj.getValue('name'), link)
        this.setState({current_obj: myobj})
      })
    } else {
      this.be.update(mydbe, (jsonObj, myobj) => {
        console.log("App.onSave.update.cb: start.")
        const o = myobj
        console.log("App.onSave.update.cb: o="+JSON.stringify(o)) //.to_string())
        this.setState({current_obj: o})
        console.log("App.onSave.update.cb: end.")
      })
    }
    // - IF successful, redirect to page /o/<new_id>
    // - ELSE show error

    this.setState({server_response_0: 'mydbe='+mydbe.to_string(), server_response_1: JSON.stringify(values,null,2)})
  }
  onDelete(values) {
    const dbename = this.state.current_obj.dbename
    const tablename = this.state.current_obj.tablename
    console.log("App.onDelete: dbename="+dbename)
    console.log("App.onDelete: tablename="+tablename)
    const mydbe = this.state.current_obj
    console.log("App.onDelete: mydbe="+mydbe.to_string())
    mydbe.setValues(values)
    console.log("App.onDelete: mydbe="+mydbe.to_string())
    console.log("App.onDelete: mydbe.isNew()="+mydbe.isNew())
    const parent_id = mydbe.getValue('father_id')===undefined || mydbe.getValue('father_id')===null
         || mydbe.getValue('father_id')===0 || mydbe.getValue('father_id')==='0'
         || mydbe.getValue('father_id')===''
         ? this.state.root_obj.getValue('id') : mydbe.getValue('father_id')
    this.setState({server_response_0: 'father_id='+mydbe.getValue('father_id')+'<br/>\nparent_id='+parent_id, server_response_1: JSON.stringify(values,null,2)})
    this.be.delete(mydbe, (jsonObj, myobj) => {
      console.log("App.onDelete.delete.cb: jsonObj="+JSON.stringify(jsonObj))
      console.log("App.onDelete.delete.cb: myobj="+JSON.stringify(myobj))
      const link = app_cfg.root_path + "e/" + parent_id + "/"
      window.location = link;
      // window.history.replaceState(null, myobj.getValue('name'), link)
      // this.setState({current_obj: myobj})
    })
  }

  parsePath() {
    console.log("App.parsePath: root_path="+app_cfg.root_path);
    console.log("App.parsePath: window.location="+window.location);
    console.log("App.parsePath: window.location.pathname="+window.location.pathname);
    const mypath = window.location.pathname.substring(app_cfg.root_path.length)
    // console.log("App.parsePath: mypath="+mypath);
    var ret = mypath.split("/");
    console.log("App.parsePath: ret="+JSON.stringify(ret));
    if(ret.length===1 && ret[0]==='') {
      ret[0] = "o"
      if(this.state.root_obj!==null) {
        ret.push(this.state.root_obj.getValue("id"))
      }
      console.log("App.parsePath: => ret="+JSON.stringify(ret));
    }
    return ret
  }
  _render(args) {
    var ret = ''
    console.log("App._render: this.state.formname="+this.state.formname);
    console.log("App._render: this.state.dbename="+this.state.dbename);
    switch(args[0]) {
      case 'test':
        ret = (
          <TestBE endpoint={this.state.endpoint} dark_theme={this.state.dark_theme} endpoints={app_cfg.endpoints} />
        );
        break
      case 'c':
        // Display the current object
        ret = (
          <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
            formname={this.state.formname} dbename={this.state.dbename}
            obj={this.state.current_obj} children={[]}
            readonly={false}
            onSave={this.onSave} onDelete={this.onDelete} onError={this.onError} />
          )
        break
      case 'p':
        // User profile
      case 'o':
        // Display the current object
        console.log("App._render: formname="+this.state.formname)
        ret = (
          <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
            formname={this.state.formname} dbename={this.state.dbename}
            obj={this.state.current_obj} children={this.state.children}
            readonly={true}
            onSave={this.onSave} onDelete={this.onDelete} onError={this.onError} />
          )
        break
      case 'e':
        // Edit the current object
        // TODO: check a user is logged in and has right to edit the object
        var readonly = false
        const user = this.be!== null ? this.be.getDBEUserFromConnection() : null
        const current_obj = this.state.current_obj
        readonly = user===null ? true
            : (!this.be.canRead(current_obj) || !this.be.canWrite(current_obj))
        ret = (
          <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
            formname={this.state.formname} dbename={this.state.dbename}
            obj={this.state.current_obj} children={this.state.children}
            readonly={readonly}
            onSave={this.onSave} onDelete={this.onDelete} onError={this.onError} />
          )
        break
      case 'manage':
        // Admin-like page of the site, for objects administered by the current user
      case 's':
        // Display search results
      default:
        ret = (<IFRTree dark_theme={this.state.dark_theme} />)
    }
    return ret
  }
  render() {
    // const dark_theme = this.state.dark_theme;
    // console.log("App.render: dark_theme="+dark_theme);
    const mypath = this.parsePath()
    console.log("App.render: mypath="+JSON.stringify(mypath));

    const display_debug = false;

    return (
      <div className={"App" + (this.state.dark_theme ? " App-dark":'')}>
        <RNav dark_theme={this.state.dark_theme} endpoint={this.state.endpoint}
          user_fullname={this.state.user_fullname} user_is_admin={this.state.user_is_admin}
          user_groups={this.state.user_groups}
          site_title={this.state.site_title}
          root_obj={this.state.root_obj} top_menu={this.state.top_menu}
          onLogin={this.onLogin} onLogout={this.onLogout} onTheme={this.onTheme} />
        <div class="container-fluid p-3">{ this._render(mypath) }</div>

        {!display_debug ? '' :
          <div class="container">

            <div class="row border rounded">
              <div class="col">
                <ServerResponse class=""
                  server_response_0={this.state.server_response_0}
                  server_response_1={this.state.server_response_1} />
              </div>
            </div>

          </div>
        }

      </div>
    );
  }
}


export default App;
