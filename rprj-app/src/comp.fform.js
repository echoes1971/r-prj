import React from 'react';
// See: https://react-select.com/home
import Select from 'react-select';

import { BackEndProxy } from './be';

class FForm extends React.Component {
    constructor(props) {
        super(props);

        console.log(props);
        
        this.state = {
            endpoint: props.endpoint,
            formname: props.formname,
            detailIcon: "icons/user.png",
            detailTitle: "User"
        }

        this.field_prefix = "field_"

        this.be = new BackEndProxy(this.state.endpoint);

        this.form = null;
        this.groups = [];

        this.forminstance_callback = this.forminstance_callback.bind(this);

        this.default_handleSubmit = this.default_handleSubmit.bind(this);
        this.default_handleChange = this.default_handleChange.bind(this);

        // this.render = this.render.bind(this);
        this.renderGroups = this.renderGroups.bind(this);
        this.renderGroup = this.renderGroup.bind(this);
        this.renderField = this.renderField.bind(this);
        this._getField = this._getField.bind(this);
        this.renderActions = this.renderActions.bind(this);

        this.btnSave = this.btnSave.bind(this);
    }

    componentDidMount() {
        this.be.getFormInstance(this.state.formname,this.forminstance_callback);
    }
    componentWillUnmount() {
        console.log("FForm.componentWillUnmount: start.");
        console.log("FForm.componentWillUnmount: end.");
    }
    // componentDidUpdate(prevProps, prevState, snapshot) {
    componentDidUpdate(prevProps, prevState) {
        // console.log("FForm.componentDidUpdate: prevProps="+JSON.stringify(prevProps))
        // console.log("FForm.componentDidUpdate: prevState="+JSON.stringify(prevState))
        // console.log("FForm.componentDidUpdate: props="+JSON.stringify(this.props))
        // console.log("FForm.componentDidUpdate: state="+JSON.stringify(this.state))
        var update = false;
        if(this.props.endpoint !== prevProps.endpoint) {
            this.setState({endpoint: this.props.endpoint})
            this.be = new BackEndProxy(this.props.endpoint);
            update = true;
        }
        if(this.props.formname !== prevProps.formname) {
            this.setState({formname: this.props.formname})
            update = true;
        }

        if(update) {
            this.be.getFormInstance(this.props.formname,this.forminstance_callback);
        }
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
    //  FFileField
    //  FList
    //      FChildSort
    //  FCheckBox
    //  FTextArea
    //      FHtml
    //  FDateTime
    //      FDateTimeReadOnly
    //  FKField
    //      FKObjectField
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
                (field.cssClass>'' ? field.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '')
            ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    {   class_unknown ?
                        <p>{field._classname}</p>
                        :
                        <input id={fieldname} name={fieldname} type={fieldtype}
                                class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                                value={this.state[fieldname]} size={field.size}
                            onChange={this.default_handleChange} />
                    }
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
    renderField(f, is_readonly=false) {
        const field = this._getField(f);
        // console.log("FForm.renderField: field._classname="+field._classname)
        if(["FDateTime","FLanguage","FNumber","FString","FUuid"].indexOf(field._classname)>=0) {
            return this.renderFField(field,is_readonly);
        }
        if(["FDateTimeReadOnly"].indexOf(field._classname)>=0) {
            return this.renderFField(field,true);
        }
        if(["FList"].indexOf(field._classname)>=0) {
            return this.renderFList(field,is_readonly);
        }
        if(field._classname==='"FPassword"') {
            return this.renderFField(field, false, false);
        }
        if(field._classname==='FTextArea') {
            return this.renderFTextArea(field,is_readonly)
        }
        return this.renderFField(field, false, true);
    }
    renderGroup(g) {
        const decodeGroupNames = this.form.decodeGroupNames
        const g1 = g.length>0 ? g : "_"
        const groupName = decodeGroupNames[g]
        const group = this.form.groups[g]
        const is_readonly = false; // TODO
        // console.log("FForm.renderGroup: group="+JSON.stringify(group))
        return (
            <div class="component">
                <div class="row"><div class="col fw-bold text-middle">{groupName}</div></div>
                {group.map((fieldname) => {
                    return this.renderField(fieldname, is_readonly)
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
                <div class="btn-group mr-2" role="group">
                    <button class="btn btn-secondary" type="button" >Delete</button>
                    {Object.keys(actions).map((k) => {
                        // {"label":"Reload","page":"obj_reload_do.php","icon":"icons/reload.png","desc":"Reload"}
                        return (
                            <button class="btn btn-secondary" type="button" title={actions[k].desc}>{actions[k].label || actions[k][0]}</button>
                        );

                    })}
                    <button class="btn btn-secondary" type="button"
                        onClick={this.btnSave} >Save</button>
                </div>
                &nbsp;
                <div class="btn-group mr-2" role="group">
                    <button class="btn btn-secondary" type="button" >View</button>
                    <button class="btn btn-secondary" type="button" >Close</button>
                </div>
            </div>
        );
    }

    forminstance_callback(jsonObj,form) {
        // console.log("FForm.forminstance_callback: start.")
        // console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
        this.form = form;
        // var s = [];
        // for(const property in form) {
        //     if(property=='fields' || property=='groups') continue;
        //     s.push(property +": "+JSON.stringify(form[property]));
        // }
        // s.push('groups:')
        // for(const p in form.groups) {
        //     s.push("  "+p)
        // }
        // s.push('fields:')
        // for(const p in form.fields) {
        //     s.push("  "+p+": "+JSON.stringify(form.fields[p]))
        // }
        const detailTitle = form.detailTitle
        this.setState({
        //     detailIcon: form.detailIcon,
            detailTitle: detailTitle
        })
        // console.log("FForm.forminstance_callback: end.")
    }

    render() {
        const detailTitle = this.state.detailTitle;
        const f = this.renderGroups();
        const actions = this.renderActions();
        return (
            <form onSubmit={this.default_handleSubmit} encType={this.form!==null ? this.form.enctype : null} >
                <div class="container border rounded">
                    <div class="row text-center border-bottom"><div class="col fw-bold">{detailTitle}</div></div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">{actions}</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">{f}</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                </div>
            </form>
        );
    }
}

export { FForm };
