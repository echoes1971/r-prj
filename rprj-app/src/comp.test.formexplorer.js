import React from 'react';

import { RLocalStorage } from './comp.ls';
import { BackEndProxy } from './be';
import { FForm } from './comp.fform';
import { ServerResponse } from './comp.test.serverresponse';
import { DBEntity } from './db/dblayer';

/*
 * This to develop backend functions to retrieve all the forms in form schema
 * and use this to dinamically create forms on react
 */
class FormExplorer extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            endpoint: props.endpoint,
            dark_theme: props.dark_theme,
            server_response_0: "",
            server_response_1: "",
            debug_form: "",
            selectedClassname: "FGroup", //null,
            obj_id: null,
            myobj: null,
            classnames: [{value: "cippa", label: "Cippa"},{value:"lippa", label:"Lippa"}]
        }

        this.be = new BackEndProxy(this.state.endpoint);

        // this.myobj = new DBEntity("DBEObject","objects");
        this.dbe2formMapping = {}
        
        // Bindings
        this.default_callback = this.default_callback.bind(this);
        this.default_handleChange = this.default_handleChange.bind(this);
        this.default_handleSubmit = this.default_handleSubmit.bind(this);

        this.classnames_callback = this.classnames_callback.bind(this);
        this.btnClassNames = this.btnClassNames.bind(this);

        this.select_handleChange = this.select_handleChange.bind(this);

        this.forminstance_callback = this.forminstance_callback.bind(this);
        this.btnDebugFForm = this.btnDebugFForm.bind(this);
        this.btnDebugDBE = this.btnDebugDBE.bind(this);

        this.onSave = this.onSave.bind(this);
        this.onError = this.onError.bind(this);

        this.onLoadForm_callback = this.onLoadForm_callback.bind(this);
        this.onLoad_callback = this.onLoad_callback.bind(this);
        this.btnLoadObject = this.btnLoadObject.bind(this);

        this.dbe2form_cb = this.dbe2form_cb.bind(this);
        this.btnDBE2Form = this.btnDBE2Form.bind(this);

        this.btnLoadDBE = this.btnLoadDBE.bind(this);
    }

    componentDidMount() {
        console.log("FormExplorer.componentDidMount: start.");
        // Local Storage
        this.ls = new RLocalStorage("FormExplorer");
        const mystate = this.ls.getMyState();
        const myobj = mystate["myobj"]
        if(myobj && myobj.dbename>'' && myobj.tablename>'') {
            mystate["myobj"] = new DBEntity(mystate["myobj"].dbename, mystate["myobj"].tablename);
            mystate["myobj"].setValues(myobj);
        }
        console.log("FormExplorer.componentDidMount: ls="+this.ls.toString());
        console.log("FormExplorer.componentDidMount: mystate="+JSON.stringify(mystate));
        this.setState(mystate);

        this.be.getDBE2FormMapping(this.dbe2form_cb);
        this.be.getAllFormClassnames(this.classnames_callback);
        console.log("FormExplorer.componentDidMount: end.");
    }
    componentWillUnmount() {
        console.log("FormExplorer.componentWillUnmount: start.");
        this.ls.saveMyState(this.state);
        console.log("FormExplorer.componentWillUnmount: end.");
    }
    componentDidUpdate(prevProps, prevState) {
        if(this.props.dark_theme !== prevProps.dark_theme) {
            this.setState({dark_theme: this.props.dark_theme})
        }
    }

    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const target = event.target;
        // console.log("target.type: "+target.type)
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.ls.setValue(name,value);
        this.setState({[name]: value});
    }
    default_callback(jsonObj,formlist) {
        var myoptions = [];
        for(var i=0; i<formlist.length; i++) {
            myoptions.push({value: formlist[i], label: formlist[i]})
        }
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: "" + jsonObj[1],
            // selectedClassname: null,
            classnames: myoptions
        })
    }

    forminstance_callback(jsonObj,form) {
        console.log("FormExplorer.forminstance_callback: start.");
        console.log("FormExplorer.forminstance_callback: form="+JSON.stringify(form));
        var s = [];
        for(const property in form) {
            // if(property==='fields' || property==='groups') continue;
            if(typeof(form[property])==='function') {
                s.push(property +"()");
                continue
            }
            s.push(property +": "+JSON.stringify(form[property]));
        }
        s.push('groups:')
        for(const p in form.groups) {
            s.push("  "+p)
        }
        s.push('fields:')
        for(const p in form.fields) {
            s.push("  "+p+": "+JSON.stringify(form.fields[p]))
        }
        const classname = form._classname;
        // console.log("FormExplorer.forminstance_callback: form._classname="+form._classname);
        // console.log("FormExplorer.forminstance_callback: classname="+classname);
        if(jsonObj[0]>'') {
            this.setState({
                selectedClassname: classname
                ,server_response_0: jsonObj[0]
                ,server_response_1: "" + s.join("\n")
            })
        } else {
            this.ls.removeValue("obj_id")
            this.ls.removeValue("myobj")
            this.setState({
                selectedClassname: classname
                ,debug_form: "" + s.join("\n")
                ,obj_id: null
                ,myobj: null
                })
        }
        console.log("FormExplorer.forminstance_callback: end.");
    }
    select_handleChange(event) {
        const target = event.target;
        console.log("target.type: "+target.type)
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;


        console.log("FormExplorer.select_handleChange: "+name+"="+value);
        this.ls.setValue(name,value);
        this.setState({[name]: value});
        // RRA: if you enable this here, and then you setState in the callback, it will complain (and block) that the component to update has been unmounted
        // this.setState({selectedClassname: selectedOption.value});
        // console.log("FormExplorer.select_handleChange: selectedOption="+JSON.stringify(selectedOption));

        if(name==='dbename') {
            const selectedClassname = this.be.getFormNameByDBEName(value);
            this.setState({selectedClassname: selectedClassname})
        }
        // this.be.getFormInstance(value,this.forminstance_callback);
    }
    btnDebugFForm() {
        const classname = this.state.selectedClassname;
        this.be.getFormInstance(classname,this.forminstance_callback);
    }
    btnDebugDBE() {
        const classname = this.state.dbename;
        this.be.getDBEInstance(classname,this.forminstance_callback);
    }

    dbe2form_cb(jsonObj, dbe2formMapping) {
        if(dbe2formMapping) {
            this.dbe2formMapping = dbe2formMapping;
            this.setState({
                server_response_0: jsonObj[0],
                server_response_1: JSON.stringify(jsonObj[1])
            })
        } else {
            this.setState({
                server_response_0: jsonObj[0],
                server_response_1: JSON.stringify(jsonObj[1])
            })
        }
    }
    btnDBE2Form() {
        this.be.getDBE2FormMapping(this.dbe2form_cb);
    }

    classnames_callback(jsonObj,formlist) {
        if(formlist) {
            var myoptions = [];
            for(var i=0; i<formlist.length; i++) {
                myoptions.push({value: formlist[i], label: formlist[i]})
            }
            this.setState({
                // selectedClassname: null,
                classnames: myoptions
                // ,server_response_0: jsonObj[0]
                // ,server_response_1: "" + jsonObj[1]
            })
        } else {
            this.setState({
                server_response_0: jsonObj[0],
                server_response_1: "" + jsonObj[1]
            })
        }
    }
    btnClassNames() {
        this.be.getAllFormClassnames(this.classnames_callback);
    }

    onLoadForm_callback(jsonObj,form) {
        console.log("FormExplorer.onLoadForm_callback: start.");
        console.log("FormExplorer.onLoadForm_callback: form="+JSON.stringify(form));
        if(form===null) {
            this.setState({
                 server_response_0: jsonObj[0]
                ,server_response_1: JSON.stringify(jsonObj[1])
            })
            console.log("FormExplorer.onLoadForm_callback: end.");
            return
        }
        var s = [];
        for(const property in form) {
            // if(property==='fields' || property==='groups') continue;
            s.push(property +": "+JSON.stringify(form[property]));
        }
        s.push('groups:')
        for(const p in form.groups) {
            s.push("  "+p)
        }
        s.push('fields:')
        for(const p in form.fields) {
            s.push("  "+p+": "+JSON.stringify(form.fields[p]))
        }
        const classname = form._classname;
        console.log("FormExplorer.onLoadForm_callback: form._classname="+form._classname);
        // console.log("FormExplorer.onLoadForm_callback: classname="+classname);
        this.setState({
            selectedClassname: classname
            ,server_response_0: jsonObj[0]
            ,server_response_1: "" + s.join("\n")
        })
        console.log("FormExplorer.onLoadForm_callback: end.");
    }
    onLoad_callback(jsonObj,myobj) {
        console.log("FormExplorer.onLoad_callback: start.");
        if(myobj!==null) {
            // this.myobj = myobj
            const dbename = myobj.getDBEName()
            const formName = this.be.getFormNameByDBEName(dbename);
            this.ls.setValue("dbename",dbename);
            this.ls.setValue("selectedClassname",formName);
            this.ls.setValue("myobj",myobj);
            this.setState({
                debug_form: myobj!==null ? myobj.to_string() : "--",
                selectedClassname: formName,
                dbename: dbename,
                myobj: myobj,
                server_response_0: jsonObj[0],
                server_response_1: myobj.to_string()
            })
            console.log("FormExplorer.onLoad_callback: myobj="+myobj.to_string());
            // this.be.getFormInstanceByDBEName(dbename, this.onLoadForm_callback)
        } else {
            this.setState({
                // server_response_0: jsonObj[0],
                server_response_0: JSON.stringify(jsonObj[0]),
                // server_response_1: myobj!==null ? JSON.stringify(myobj.getValues()) : '--' // JSON.stringify(jsonObj[1])
                server_response_1: myobj!==null ? myobj.to_string() : '--' // JSON.stringify(jsonObj[1])
            })    
        }
        console.log("FormExplorer.onLoad_callback: end.");
    }
    btnLoadObject() {
        const obj_id = this.state.obj_id
        const ignore_deleted = false
        this.be.fullObjectById(obj_id, ignore_deleted, this.onLoad_callback)
    }

    btnLoadDBE() {
        const obj_id = this.state.obj_id
        if(!(obj_id.length>0)) return;
        const dbename = this.state.dbename
        const search = new DBEntity(dbename)
        search.setValue('id',obj_id)

		var self = this
		var my_cb = (server_messages, dbelist) => {
			console.log("TestBE.btnLoadDBE.my_cb: start.");
            this.setState({server_response_0: server_messages, server_response_1: JSON.stringify(dbelist)})
            const myobj = dbelist!==null && dbelist.length===1 ? dbelist[0] : null
            if(myobj===null) {
                console.log("TestBE.btnLoadDBE.my_cb: end.")
                return
            }
            const dbename = myobj.getDBEName()
            const formName = this.be.getFormNameByDBEName(dbename);
            this.ls.setValue("dbename",dbename);
            this.ls.setValue("selectedClassname",formName);
            this.ls.setValue("myobj",myobj);
            this.setState({
                selectedClassname: formName,
                dbename: dbename,
                myobj: myobj
                ,debug_form: myobj.to_string()
                ,server_response_0: server_messages
                ,server_response_1: JSON.stringify(dbelist)
            })
            console.log("TestBE.btnLoadDBE.my_cb: end.");
		}
        my_cb = my_cb.bind(self)

        // search(dbe, uselike, caseSensitive, orderBy, a_callback)
        this.be.search(search,false,false,'id',my_cb)
    }

    onSave(values) {
        this.setState({debug_form: JSON.stringify(values)})
    }
    onError(jsonObj) {
        this.setState({
            server_response_0: jsonObj[0],
            server_response_1: JSON.stringify(jsonObj[1])
        })
    }

    render() {
        const selectedClassname = this.state.selectedClassname;
        console.log("FormExplorer.render: selectedClassname="+selectedClassname);
        console.log("FormExplorer.render: this.state.dark_theme="+this.state.dark_theme);
        // console.log("FormExplorer.render: this.state.myobj="+JSON.stringify(this.state.myobj));
        if(this.state.myobj!==null && this.state.myobj!==undefined) {
            console.log("FormExplorer.render: this.state.myobj="+this.state.myobj.to_string());
        }
        const obj = this.state.myobj!==null && this.state.myobj!==undefined ? this.state.myobj : {};
        // const obj = this.state.myobj!==null && this.state.myobj!==undefined ? this.state.myobj.getValues() : {};
        console.log("FormExplorer.render: obj="+JSON.stringify(obj));
        const dbeNames = this.dbe2formMapping ? Object.keys(this.dbe2formMapping) : []
        console.log("FormExplorer.render: dbeNames="+JSON.stringify(dbeNames));
        return (
            <div class={"component "+this.props.class}>
                <div class="row">
                    <div class="col text-middle fw-bold">Form Explorer</div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class="row">
                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <select name="dbename" value={this.state.dbename} onChange={this.select_handleChange} >
                                {dbeNames.map((x) => {
                                    return (<option value={x}>{x}</option>);
                                }
                                )}
                            </select>
                            <select name="selectedClassname" value={selectedClassname} onChange={this.select_handleChange} >
                                {Object.keys(this.state.classnames).map((x) => {
                                    return (<option value={this.state.classnames[x].value}>{this.state.classnames[x].label}</option>);
                                }
                                )}
                            </select>
                            <div class="btn-group m-1" role="group" aria-label="Test Modules">
                                <button class="btn btn-secondary" onClick={this.btnDebugDBE}>Debug DBE</button>
                                <button class="btn btn-secondary" onClick={this.btnDebugFForm}>Debug FForm</button>
                            </div>
                        </form>
                    </div>

                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <label for="obj_id" /><input id="obj_id" name="obj_id" value={this.state.obj_id} onChange={this.default_handleChange} />
                            <div class="btn-group m-1" role="group" aria-label="Test Modules">
                                <button class="btn btn-secondary" onClick={this.btnLoadDBE}>Load DBE</button>
                                <button class="btn btn-secondary" onClick={this.btnLoadObject}>Load Object</button>
                            </div>
                        </form>
                    </div>

                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <button class="btn btn-secondary" onClick={this.btnClassNames}>Class Names</button>
                        </form>
                    </div>

                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <button class="btn btn-secondary" onClick={this.btnDBE2Form}>DBE 2 Form</button>
                        </form>
                    </div>
                </div>
                
                <div class="row"><div class="col">&nbsp;</div></div>

                <div class="row">
                    <div class="col">
                        <FForm endpoint={this.state.endpoint} dark_theme={this.state.dark_theme}
                            formname={selectedClassname} dbename={this.state.dbename}
                            obj={obj}
                            onSave={this.onSave} onError={this.onError} />
                    </div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class={"component "+this.props.class}>
                    <div class="row">
                        <div class="col">
                            <div class="component border rounded">
                                <div class="row">
                                    <div class="col fw-bold d-none d-lg-block">Debug Form</div>
                                    <div class="col text-start"><pre>{this.state.debug_form}</pre></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class="row">
                    <div class="col">
                        <ServerResponse class="border rounded"
                            server_response_0={this.state.server_response_0}
                            server_response_1={this.state.server_response_1} />
                    </div>
                </div>

            </div>
        );
    }
}

export {FormExplorer};
