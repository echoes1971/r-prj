import React from 'react';

import { FPermissions } from './comp.ffields';
import { BackEndProxy } from './be';

class FForm extends React.Component {
    constructor(props) {
        super(props);

        // console.log(props);
        
        this.state = {
            endpoint: props.endpoint,
            dark_theme: props.dark_theme,
            detailIcon: "icons/user.png",
            detailTitle: "User",
            formname: props.formname,
            dbename: props.dbename,
            obj_id: props.obj_id,
            obj: props.obj || {}
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
        // this.be.getFormInstance(this.state.formname,this.forminstance_callback);
        // console.log("FForm.componentDidMount: end.")
    }
    componentWillUnmount() {
        console.log("FForm.componentWillUnmount: start.");
        console.log("FForm.componentWillUnmount: end.");
    }
    // componentDidUpdate(prevProps, prevState, snapshot) {
    componentDidUpdate(prevProps, prevState) {
        // console.log("FForm.componentDidUpdate: prevProps="+JSON.stringify(prevProps))
        // console.log("FForm.componentDidUpdate: props="+JSON.stringify(this.props))
        // console.log("FForm.componentDidUpdate: prevState="+JSON.stringify(prevState))
        // console.log("FForm.componentDidUpdate: state="+JSON.stringify(this.state))
        var update = false;
        const changes = {};
        if(this.props.endpoint !== prevProps.endpoint) {
            // this.setState({endpoint: this.props.endpoint})
            changes["endpoint"] = this.props.endpoint;
            this.be = new BackEndProxy(this.props.endpoint);
            update = true;
        }
        if(JSON.stringify(this.props.obj) != JSON.stringify(prevProps.obj)) {
            const obj = this.props.obj
            console.log("FForm.componentDidUpdate: obj changed="+JSON.stringify(obj))
            console.log("FForm.componentDidUpdate: obj changed="+(typeof obj))
            this.obj2state(obj);
            this.setState({obj: obj})
            // changes["obj"] = this.props.obj;
        }
        if(this.props.dark_theme !== prevProps.dark_theme) {
            this.setState({dark_theme: this.props.dark_theme})
            // changes["dark_theme"] = this.props.dark_theme;
        }
        if(this.props.formname !== prevProps.formname) {
            this.setState({formname: this.props.formname})
            // changes["formname"] = this.props.formname;
            update = true;
        }
        // this.setState(changes);

        if(update) {
            this.be.getFormInstance(this.props.formname,this.forminstance_callback);
        }
    }

    forminstance_callback(jsonObj,form) {
        console.log("FForm.forminstance_callback: start.")
        // console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
        this.form = form;
        if(form===null) {
            this.props.onError(jsonObj);
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
        console.log("FForm.obj2state: myobj="+JSON.stringify(myobj));
        for(const k in myobj) { //.getValues()) {
            console.log("FForm.obj2state: k="+k)
            const k1 = this.field_prefix + k
            values[k1] = myobj[k]
        }
        console.log("FForm.obj2state: values="+JSON.stringify(values));
        this.setState(values);
    }

    btnSave() {
        const values = {};
        for(const k in this.state) {
            console.log("FForm.btnSave: k="+k)
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
    //      FHtml - TODO
    //  FDateTime
    //      FDateTimeReadOnly
    //  FKField - TODO
    //      FKObjectField - TODO
    renderFField(field, is_readonly=false, class_unknown=false) {
        const fieldname = this.field_prefix + field.name
        const fieldtype = field._classname==="FPassword" ? "password"
                    : field.type==="n" ? "number"
                    : field.type==='d' ? 
                        ( field.show_date && field.show_time ? 'datetime-local'
                            : field.show_date ? 'date'
                            : 'time'
                        )
                    : "text"; // n=number s=string d=datetime
        const fieldclass = (
                (field.cssClass>'' ? field.cssClass : '') + ' ' +
                (is_readonly ?
                    'form-control-plaintext' + (this.state.dark_theme ? ' form-control-plaintext-dark' : '')
                    : '')
            ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start align-top">
                    {   class_unknown ?
                        <p>{field._classname}</p>
                        :
                        <input id={fieldname} name={fieldname} type={fieldtype}
                                // size={field.size}
                                class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                                value={this.state[fieldname] && field.type==='d' ?
                                            ( fieldtype=='time' ? this.state[fieldname].split(" ")[1] : this.state[fieldname].replace(" ","T") )
                                        : this.state[fieldname] }
                            onChange={this.default_handleChange} />
                    }
                </div>
            </div>
        );
    }
    renderFList(field, is_readonly=false) {
        const fieldname = this.field_prefix + field.name
        const fieldclass = (
            (field.cssClass>'' ? field.cssClass : '') + ' '
            + (is_readonly ? 'form-control-plaintext' : '')
        ).trim();
        const listvalues = field.valueslist;
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    <select id={fieldname} name={fieldname}
                        class={fieldclass} readOnly={is_readonly}
                        value={this.state[fieldname]} onChange={this.default_handleChange} >
                        {Object.keys(listvalues).map((k) => {
                            return (<option value={k}>{listvalues[k]}</option>);
                        }
                        )}
                    </select>
                </div>
            </div>
        );
    }
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
    renderFPercent(field, is_readonly=false) {
        const fieldname = this.field_prefix + field.name
        const fieldtype = "number";
        const fieldclass = (
                (field.cssClass>'' ? field.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '')
            ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    <input id={fieldname} name={fieldname} type={fieldtype}
                            class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                            value={this.state[fieldname]} size={field.size}
                        onChange={this.default_handleChange} /> %
                </div>
            </div>
        );
    }
    renderFTextArea(field, is_readonly=false) {
        const fieldname = this.field_prefix + field.name
        const fieldclass = (
            (field.cssClass>'' ? field.cssClass : '') + ' '
            + (is_readonly ? 'form-control-plaintext' : '')
        ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    <textarea id={fieldname} name={fieldname}
                        class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                        value={this.state[fieldname]} size={field.size}
                        width={field.width} height={field.height}
                        onChange={this.default_handleChange} />
                </div>
            </div>
        );
    }
    renderField(f, is_readonly=false) {
        var field = this._getField(f);
        // console.log("FForm.renderField: field._classname="+field._classname)
        const field_name = this.field_prefix + field.name;
        field["value"] = this.state[field_name];
        if(field["value"]) {
            console.log("FForm.renderField: field="+JSON.stringify(field));
        }
        if(["FDateTime","FLanguage","FNumber","FString","FUuid"].indexOf(field._classname)>=0) {
            return this.renderFField(field,is_readonly);
        }
        if(["FDateTimeReadOnly"].indexOf(field._classname)>=0) {
            return this.renderFField(field,true);
        }
        if(["FList"].indexOf(field._classname)>=0) {
            return this.renderFList(field,is_readonly);
        }
        if(field._classname==='FPassword') {
            return this.renderFPassword(field, is_readonly);
        }
        if(field._classname==='FPercent') {
            return this.renderFPercent(field, is_readonly);
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
        return this.renderFField(field, false, true);
    }
    renderGroup(g) {
        const decodeGroupNames = this.form.decodeGroupNames
        const groupName = decodeGroupNames[g]
        const group = this.form.groups[g]
        const is_readonly = false; // TODO

        const visibleFields = this.form.detailColumnNames;
        const readonlyFields = this.form.detailReadOnlyColumnNames;

        console.log("FForm.renderGroup: this.state.dark_theme="+this.state.dark_theme)
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
    renderActions() {
        if(this.form===null) {
            return ("--");
        }
        const actions = this.form.actions;
        return (
            <div class="btn-toolbar" role="toolbar" aria-label="Actions">
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-secondary btn-sm" type="button" >Delete</button>
                    {Object.keys(actions).map((k) => {
                        // {"label":"Reload","page":"obj_reload_do.php","icon":"icons/reload.png","desc":"Reload"}
                        return (
                            <button class="btn btn-secondary btn-xs" type="button" title={actions[k].desc}>{actions[k].label || actions[k][0]}</button>
                        );

                    })}
                    <button class="btn btn-secondary btn-xs" type="button"
                        onClick={this.btnSave} >Save</button>
                </div>
                &nbsp;
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-secondary btn-sm" type="button" >View</button>
                    <button class="btn btn-secondary btn-sm" type="button" >Close</button>
                </div>
            </div>
        );
    }

    icon2emoji(detail_icon) {
        var ret = ('');
        switch(detail_icon) {
            case 'icons/user.png':
                ret = (<span>&#128100;</span>)
                break
            case 'icons/group_16x16.gif':
                ret = (<span>&#128101;</span>)
                break
            case 'icons/text-x-log.png':
                // ret = (<span>&#128195;</span>)
                ret = (<span>&#128220;</span>)
                break
            case 'icons/company_16x16.gif':
                ret = (<span>&#127981;</span>)
                break
            case 'icons/people.png':
                ret = (<span>&#129333;</span>)
                break
            case 'icons/event_16x16.png':
                ret = (<span>&#128198;</span>)
                break
            case 'icons/file_16x16.gif':
                ret = (<span>&#128196;</span>)
                break
            case 'icons/folder_16x16.gif':
                ret = (<span>&#128193;</span>)
                break
            case 'icons/link_16x16.gif':
                ret = (<span>&#128279;</span>)
                break
            case 'icons/note_16x16.gif':
                ret = (<span>&#128466;</span>)
                break
            case 'icons/page_16x16.gif':
                ret = (<span>&#128195;</span>)
                break
            case 'icons/news.png':
                ret = (<span>&#128240;</span>)
                break
            case 'icons/project_16x16.gif':
                ret = (<span>&#127959;</span>) // 128200
                break
            case 'icons/timetrack_16x16.gif':
                ret = (<span>&#9201;</span>)
                break
            case 'icons/task_16x16.gif':
                ret = (<span>&#9745;</span>)
                break
            default:
                ret = (<span>&#9881;</span>)
                break
        }
        return ret
    }
    render() {
        const detailIcon = this.state.detailIcon;
        const detailTitle = this.state.detailTitle;
        const f = this.renderGroups();
        const actions = this.renderActions();
        console.log("FForm.render: formname="+this.state.formname)
        console.log("FForm.render: dark_theme="+this.state.dark_theme)
        console.log("FForm.render: obj="+JSON.stringify(this.state.obj))
        return (
            <form onSubmit={this.default_handleSubmit} encType={this.form!==null ? this.form.enctype : null} >
                <div class="container border rounded">
                    <div class="row text-center border-bottom"><div class="col fw-bold">{this.icon2emoji(detailIcon)} {detailTitle}</div></div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">{actions}</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">{f}</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">TODO: detail forms</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                </div>
            </form>
        );
    }
}

export { FForm };
