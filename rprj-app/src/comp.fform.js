import React from 'react';

import { FField, FKField, FKObjectField, FList, HTMLEdit, FPercent, FPermissions } from './comp.ffields';
import { BackEndProxy } from './be';
import { DBOButton, DBOLink, icon2emoji, IFRTree } from './comp.ui.elements';

class FForm extends React.Component {
    constructor(props) {
        super(props);

        // console.log(props);
        
        this.state = {
            endpoint: props.endpoint,
            dark_theme: props.dark_theme,
            readonly: props.readonly || false,

            server_response_0: '',
            server_response_1: '',

            detailIcon: "", //"icons/user.png",
            detailTitle: "", //"User",
            formname: props.formname,
            dbename: props.dbename,
            obj_id: props.obj_id,
            obj: props.obj || null,
            children: props.children || []
        }

        this.field_prefix = "field_"

        this.be = new BackEndProxy(this.state.endpoint);

        this.form = null;
        this.groups = [];

        this.forminstance_callback = this.forminstance_callback.bind(this);

        this.default_handleSubmit = this.default_handleSubmit.bind(this);
        this.default_handleChange = this.default_handleChange.bind(this);
        this.password_handleChange = this.password_handleChange.bind(this);

        // this.render = this.render.bind(this);
        this.renderGroups = this.renderGroups.bind(this);
        this.renderGroup = this.renderGroup.bind(this);
        this.renderField = this.renderField.bind(this);
        this._getField = this._getField.bind(this);
        this.renderActions = this.renderActions.bind(this);

        this.btnSave = this.btnSave.bind(this);
    }

    componentDidMount() {
        // console.log("FForm.componentDidMount: start.")
        this.be.getLoggedUser()
        this.be.getDBE2FormMapping((jsonObj, dbe2formMapping) => {
            this.dbe2formMapping = dbe2formMapping
            // console.log("FForm.componentDidMount.cb: dbe2formMapping="+JSON.stringify(this.dbe2formMapping))
        })
        // this.be.getFormInstance(this.state.formname,this.forminstance_callback);
        // console.log("FForm.componentDidMount: end.")
    }
    componentWillUnmount() {
        console.log("FForm.componentWillUnmount: start.");
        console.log("FForm.componentWillUnmount: end.");
    }
    componentDidUpdate(prevProps, prevState) {
        // console.log("FForm.componentDidUpdate: prevProps="+JSON.stringify(prevProps))
        // console.log("FForm.componentDidUpdate: props="+JSON.stringify(this.props))
        // console.log("FForm.componentDidUpdate: prevState="+JSON.stringify(prevState))
        // console.log("FForm.componentDidUpdate: state="+JSON.stringify(this.state))

        const changes = {};
        if(this.props.endpoint !== prevProps.endpoint) {
            // this.setState({endpoint: this.props.endpoint})
            changes["endpoint"] = this.props.endpoint;
            this.be = new BackEndProxy(this.props.endpoint);

            this.be.getFormInstance(this.props.formname,this.forminstance_callback);
        }
        if(JSON.stringify(this.props.obj) !== JSON.stringify(prevProps.obj)) {
            const obj = this.props.obj
            // console.log("FForm.componentDidUpdate: obj changed="+JSON.stringify(obj))
            console.log("FForm.componentDidUpdate: obj changed="+(typeof obj))
            this.obj2state(obj);
            this.setState({obj: obj})
            // changes["obj"] = this.props.obj;
        }
        if(JSON.stringify(this.props.children) !== JSON.stringify(prevProps.children)) {
            const children = this.props.children
            // console.log("FForm.componentDidUpdate: children changed="+JSON.stringify(children))
            console.log("FForm.componentDidUpdate: children changed="+(typeof children))
            // this.obj2state(obj);
            this.setState({children: children})
            // changes["obj"] = this.props.obj;
        }
        if(this.props.dark_theme !== prevProps.dark_theme) {
            this.setState({dark_theme: this.props.dark_theme})
            // changes["dark_theme"] = this.props.dark_theme;
        }
        if(this.props.readonly !== prevProps.readonly) {
            this.setState({readonly: this.props.readonly})
        }
        if(this.props.dbename !== prevProps.dbename) {
            this.setState({dbename: this.props.dbename})
            console.log("FForm.componentDidUpdate: dbename changed="+this.props.dbename)
        }
        if(this.props.formname !== prevProps.formname) {
            this.setState({formname: this.props.formname})
            console.log("FForm.componentDidUpdate: formname changed "+prevProps.formname + "=>" + this.props.formname)
            // changes["formname"] = this.props.formname;

            this.be.getFormInstance(this.props.formname,this.forminstance_callback);
        }
        // this.setState(changes);
    }

    forminstance_callback(jsonObj,form) {
        console.log("FForm.forminstance_callback: start.")
        // console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
        this.form = form;
        if(form===null) {
            // this.props.onError(jsonObj);
            return;
        }

        this.obj2state();
        const detailTitle = form.detailTitle
        this.setState({
            detailIcon: form.detailIcon,
            detailTitle: detailTitle
        })
        console.log("FForm.forminstance_callback: end.")
    }

    obj2state(an_obj=null) {
        const values = {};
        var myobj = an_obj===null ? this.state.obj : an_obj;
        if('dict' in myobj) {
            myobj = myobj.dict
        }

        // Clean all fields
        for(const k in this.state) {
            if(k.indexOf(this.field_prefix)<0) continue;
            // values[k] = null;
            delete this.state[k]
        }

        // Store new values
        // console.log("FForm.obj2state: myobj="+JSON.stringify(myobj));
        for(const k in myobj) { //.getValues()) {
            // console.log("FForm.obj2state: k="+k)
            const k1 = this.field_prefix + k
            values[k1] = myobj[k]
        }
        // console.log("FForm.obj2state: values="+JSON.stringify(values));
        this.setState(values);
    }

    btnSave() {
        const values = {};
        for(const k in this.state) {
            // console.log("FForm.btnSave: k="+k)
            if(k.indexOf(this.field_prefix)<0) continue;
            const k1 = k.replace(this.field_prefix,"");
            values[k1] = this.state[k]
        }
        console.log("FForm.btnSave: values="+JSON.stringify(values))
        this.props.onSave(values)
    }


    getViewColumnNames() { return this.form.viewColumnNames; }

    // password_handleChange(event) {
    //     const target = event.target;
    //     const value = target.type === 'checkbox' ? target.checked : target.value;
    //     const name = target.name;

    //     this.setState({[name]: value});
    // }
    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.setState({[name]: value});
    }

    _getField(f) {
        const form = this.form;
        var ret = null;
        for(const p in form.fields) {
            // console.log("FForm.renderField: p="+JSON.stringify(p))
            if(form.fields[p].name===f) {
                ret = form.fields[p];
                break;
            }
        }
        return ret;

    }
    // FField
    //  FNumber
    //      FPercent
    //  FString
    //      FLanguage
    //      FUuid
    //      FPassword
    //      FPermissions
    //  FFileField - TODO
    //  FList
    //      FChildSort - TODO
    //  FCheckBox - TODO
    //  FTextArea
    //      FHtml
    //  FDateTime
    //      FDateTimeReadOnly
    //  FKField
    //      FKObjectField - TODO
    password_handleChange(event) {
        const target = event.target;
        const value1 = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        const fieldname = name.indexOf('pwd1_')>=0 ? name.replace('pwd1_','') : name.replace('pwd2_','');
        const fieldname1 = "pwd1_" + fieldname
        const fieldname2 = "pwd2_" + fieldname
        const prefix_fieldname = this.field_prefix + fieldname

        const value2 = name.indexOf('pwd1_')>=0 ? this.state[fieldname2] : this.state[fieldname1];
        const value = value1===value2 ? value1 : this.state[prefix_fieldname]

        this.setState({[prefix_fieldname]: value, [name]: value1});
    }
    renderFPassword(field, is_readonly=false) {
        const fieldname1 = "pwd1_" + field.name
        const fieldname2 = "pwd2_" + field.name
        const fieldclass = (
                (field.cssClass>'' ? field.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '') + ' '
                + (this.state[fieldname1] !== this.state[fieldname2] ? 'bg-danger'
                    : this.state[fieldname1]===undefined || this.state[fieldname1]===null ? ''
                        : 'bg-success' )
            ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    <input id={fieldname1} name={fieldname1} type="password"
                            class={fieldclass} readOnly={is_readonly} placeholder="Enter password"
                            value={this.state[fieldname1]}
                        onChange={this.password_handleChange} />
                    <input id={fieldname2} name={fieldname2} type="password"
                            class={fieldclass} readOnly={is_readonly} placeholder="Re-Enter password"
                            value={this.state[fieldname2]}
                        onChange={this.password_handleChange} />
                </div>
            </div>
        );
    }
    renderFTextArea(field, is_readonly=false) {
        const fieldname = this.field_prefix + field.name
        const fieldclass = (
            (field.cssClass>'' ? field.cssClass : '') + ' '
            + (is_readonly ? 'form-control-plaintext'  + (this.state.dark_theme ? ' form-control-plaintext-dark' : '')
                 : '')
        ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">{
                    is_readonly ?
                    <pre class="border rounded">{field['value']}</pre>
                    :
                    <textarea id={fieldname} name={fieldname}
                        class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                        value={this.state[fieldname]} size={field.size}
                        width={field.width} height={field.height}
                        onChange={this.default_handleChange} />
                    }
                </div>
            </div>
        );
    }
    renderField(f, is_readonly=false) {
        var field = this._getField(f);
        // console.log("FForm.renderField: field._classname="+field._classname)
        const field_name = this.field_prefix + field.name;
        field["value"] = this.state[field_name];
        // if(field["value"]) {
        //     console.log("FForm.renderField: field="+JSON.stringify(field));
        // }
        if(["FDateTime","FLanguage","FNumber","FString","FUuid"].indexOf(field._classname)>=0) {
            return <FField name={field_name} field={field} is_readonly={is_readonly} dark_theme={this.state.dark_theme}
                onChange={this.default_handleChange} />
        }
        if(["FDateTimeReadOnly"].indexOf(field._classname)>=0) {
            return <FField name={field_name} field={field} is_readonly={true} dark_theme={this.state.dark_theme}
                onChange={this.default_handleChange} />
        }
        if(["FList"].indexOf(field._classname)>=0) {
            return <FList name={field_name} field={field} is_readonly={is_readonly} onChange={(n,v) => { this.setState({[n]: v}); }} />
        }
        if(field._classname==='FPassword') {
            return this.renderFPassword(field, is_readonly);
        }
        if(field._classname==='FPercent') {
            return <FPercent name={field_name} field={field} is_readonly={is_readonly} dark_theme={this.state.dark_theme} onChange={(n,v) => { this.setState({[n]: v}); }} />
        }
        if(field._classname==='FPermissions') {
            return (
                <FPermissions name={field.name} value={this.state[field_name]}
                    title={field.title} cssClass={this.cssClass}
                    is_readonly={is_readonly} field_prefix={this.field_prefix}
                    onChange={this.default_handleChange} />
            );
        }
        if(field._classname==='FTextArea') {
            return this.renderFTextArea(field,is_readonly)
        }
        if(field._classname==='FHtml' || field._classname==='FHtmlEdit') {
            return <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                    <div class="col text-start align-top">
                        <HTMLEdit name={field.name} value={this.state[field_name]}
                            readonly={is_readonly} field_prefix={this.field_prefix}
                            onChange={this.default_handleChange} />
                    </div>
                </div>
        }
        if(field._classname==='FKField') {
            const obj = this.state.obj
            return <FKField name={field_name} field={field} be={this.be} dbe={obj} is_readonly={is_readonly} dark_theme={this.state.dark_theme}
                onChange={(n,v) => {
                    // console.log("FForm.renderField.onChange: "+n+"="+v)
                    var myobj = this.state.obj
                    myobj.setValue('id',v)
                    this.setState({[n]: v, 'obj': myobj});
                }} />
        }
        if(field._classname==='FKObjectField') {
            const obj = this.state.obj
            return <FKObjectField name={field_name} field={field} be={this.be} dbe={obj} is_readonly={is_readonly} dark_theme={this.state.dark_theme}
                onChange={(n,v) => {
                    // console.log("FForm.renderField.onChange: "+n+"="+v)
                    var myobj = this.state.obj
                    myobj.setValue('id',v)
                    this.setState({[n]: v, 'obj': myobj});
                }} />
        }
        return <FField name={field_name} field={field} is_readonly={is_readonly} class_unknown={true} dark_theme={this.state.dark_theme}
            onChange={this.default_handleChange} />
    }
    renderGroup(g) {
        const decodeGroupNames = this.form.decodeGroupNames
        const groupName = decodeGroupNames[g]
        const group = this.form.groups[g]
        // console.log("FForm.renderGroup: this.state.readonly="+this.state.readonly+" "+typeof(this.state.readonly))
        const is_readonly = this.state.readonly;

        const visibleFields = this.form.detailColumnNames;
        const readonlyFields = this.form.detailReadOnlyColumnNames;

        // console.log("FForm.renderGroup: this.state.dark_theme="+this.state.dark_theme)
        return (
            <div class="component">
                <div class="row"><div class={"col fw-bold text-middle m-2 rounded" + (this.state.dark_theme ? " bg-dark" : " bg-light")}>{groupName}</div></div>
                {group.map((fieldname) => {
                    if(visibleFields.indexOf(fieldname)<0) return ('');
                    return this.renderField(fieldname, is_readonly || readonlyFields.indexOf(fieldname)>=0);
                })}
            </div>
        );
    }
    renderGroups() {
        if(this.form===null) {
            return ("--");
        }
        const groups = this.form.groups!==null ? this.form.groups : [];
        // console.log("FForm.renderGroups: groups="+JSON.stringify(groups))
        return (
            <div class="container">
                {Object.keys(groups).map(this.renderGroup)}
            </div>
        );
    }
    renderActions(readonly) {
        if(this.form===null) {
            return ("--");
        }

        const user = this.be.getDBEUserFromConnection()
        // console.log("FForm.renderActions: user="+JSON.stringify(user))

        const obj = this.state.obj
        // console.log("FForm.renderActions: obj="+JSON.stringify(obj))
        // console.log("FForm.renderActions: obj="+obj.to_string())
        const can_write = this.be.canWrite(obj)
        // console.log("FForm.renderActions: readonly="+readonly)
        // console.log("FForm.renderActions: can_write="+can_write)
        const actions = this.form.actions;
        return (
            <div class="btn-toolbar" role="toolbar" aria-label="Actions">{
                readonly ?
                <div class="btn-group btn-group-sm" role="group">
                    <DBOButton class="btn btn-secondary btn-sm"
                        dbo={this.state.obj} name="Edit" edit={can_write}/>
                </div>
                : ''
                }
                { readonly ? '' :
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-secondary btn-sm" type="button" >Delete</button>
                    {Object.keys(actions).map((k) => {
                        // {"label":"Reload","page":"obj_reload_do.php","icon":"icons/reload.png","desc":"Reload"}
                        return (
                            <button class="btn btn-secondary btn-xs" type="button" title={actions[k].desc}>{actions[k].label || actions[k][0]}</button>
                        );

                    })}
                    <button class="btn btn-secondary btn-xs" type="button" onClick={this.btnSave} >Save</button>
                </div>
                }
                { readonly ? '' : <span>&nbsp;</span> }
                { readonly ? '' :
                    <div class="btn-group btn-group-sm" role="group">
                        {/* <button class="btn btn-secondary btn-sm" type="button" >View</button> */}
                        <DBOButton class="btn btn-secondary btn-sm"
                            dbo={obj} name="View" edit={false}/>
                        <button class="btn btn-secondary btn-sm" type="button" >Close</button>
                    </div>
                }
            </div>
        );
    }

    renderChildren(readonly) {
        const children = this.state.children
        if(children===undefined || children===null || children.length===0) return ('')
        const detailForms = this.form && 'detailForms' in this.form ? this.form['detailForms'] : []
        if(detailForms.length===0) return ('')
        var formnames = []
        for(const i in children) {
            const dbename = children[i].getDBEName()
            // console.log("FForm.renderChildren: dbename="+dbename)
            const formname = this.be.getFormNameByDBEName(dbename)
            // console.log("FForm.renderChildren: formname="+formname)
            formnames.push(formname)
            const form_icon = formnames[i] + "_icon"
            const form_icon_title = formnames[i] + "_icon_title"
            const state_form_icon = this.state[form_icon]
            if(state_form_icon===undefined || state_form_icon===null) {
                var my_cb = (jsonObj,form) => {
                    const myform = form
                    this.setState({[form_icon]: myform.detailIcon,[form_icon_title]:form.detailTitle})
                }
                my_cb = my_cb.bind(this).bind(form_icon)
                this.be.getFormInstance(formname,my_cb)
            }
        }

        return (<div class="container">
            {Object.keys(children).map((k) => {
                const form_icon = formnames[k] + "_icon"
                const form_icon_title = formnames[k] + "_icon_title"
                return (<div class="row">
                    <div class="col"><DBOLink dbo={children[k]} detailIconTitle={this.state[form_icon_title]} detailIcon={icon2emoji(this.state[form_icon])} edit={readonly===false} /></div>
                </div>)
            })
            }</div>)
    }

    render() {
        // console.log("FForm.render: dbename="+this.state.dbename)
        // console.log("FForm.render: formname="+this.state.formname)
        // console.log("FForm.render: obj="+JSON.stringify(this.state.obj))
        if(!this.state.dbename || !this.state.formname || !this.state.obj) {
            return (<IFRTree dark_theme={this.state.dark_theme} />)
        }
        const obj = this.state.obj
        const can_write = this.be.canWrite(obj)
        // console.log("FForm.renderActions: can_write="+can_write)

        const readonly =  this.state.readonly
        const detailIcon = this.state.detailIcon;
        const detailTitle = this.state.detailTitle;
        const dark_theme = this.state.dark_theme;
        const f = this.renderGroups();
        const actions = this.renderActions(readonly);
        const children = this.state.children
        const childrenUI = this.renderChildren(readonly)

        // console.log("FForm.render: dark_theme="+this.state.dark_theme)
        // console.log("FForm.render: readonly="+this.state.readonly)
        // console.log("FForm.render: dbename="+this.state.dbename)
        // console.log("FForm.render: formname="+this.state.formname)
        // console.log("FForm.render: obj="+JSON.stringify(this.state.obj))
        return (
            <form onSubmit={this.default_handleSubmit} encType={this.form!==null ? this.form.enctype : null} >
                <div class="container border rounded">
                    <div class="row text-center border-bottom"><div class="col fw-bold">{icon2emoji(detailIcon)} {detailTitle}</div></div>
                    { !can_write ? '' : <div class="row"><div class="col">&nbsp;</div></div>}
                    { !can_write ? '' : <div class="row">{actions}</div>}
                    { !can_write ? '' : <div class="row"><div class="col">&nbsp;</div></div>}
                    <div class="row">{f}</div>
                    <div class="row"><div class="col">&nbsp;</div></div>
                    { children!==undefined && children!==null && children.length>0 ?
                        <div class="row"><div class={"col fw-bold text-middle m-2 rounded" + (dark_theme ? " bg-dark" : " bg-light")}>Content</div></div>
                        :''
                    }
                    { children!==undefined && children!==null && children.length>0 ?
                        <div class="row"><div class="col">{childrenUI}</div></div>
                        : ''
                    }
                    {/* <div class="row"><div class="col"><pre>{JSON.stringify(this.form, null, 2)}</pre></div></div> */}
                    <div class="row"><div class="col">&nbsp;</div></div>
                </div>
            </form>
        );
    }
}

export { FForm };
