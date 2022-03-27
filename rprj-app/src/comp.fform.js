import React from 'react';

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
        this.password_handleChange = this.password_handleChange.bind(this);
        this.permissions_handleChange = this.permissions_handleChange.bind(this);

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
    permissions_handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        const fieldname = name.substring(5,name.length);

        const permission_array = [
            this.state['btn0_'+fieldname], this.state['btn1_'+fieldname], this.state['btn2_'+fieldname],
            this.state['btn3_'+fieldname], this.state['btn4_'+fieldname], this.state['btn5_'+fieldname],
            this.state['btn6_'+fieldname], this.state['btn7_'+fieldname], this.state['btn8_'+fieldname]
        ];
        const myindex = name.substr(3,1)
        permission_array[parseInt(myindex)] = value

        const permissions = ''
                        + (permission_array[0] ? 'r' : '-')
                        + (permission_array[1] ? 'w' : '-')
                        + (permission_array[2] ? 'x' : '-')
                        + (permission_array[3] ? 'r' : '-')
                        + (permission_array[4] ? 'w' : '-')
                        + (permission_array[5] ? 'x' : '-')
                        + (permission_array[6] ? 'r' : '-')
                        + (permission_array[7] ? 'w' : '-')
                        + (permission_array[8] ? 'x' : '-');

        const state_fieldname = this.field_prefix + target.name.substring(5);
        this.setState({[name]: value, [state_fieldname]:permissions});
    }
    renderFPermissions(field, is_readonly=false) {
        const fieldname = field.name;
        const fieldclass = (
                (field.cssClass>'' ? field.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '')
            ).trim();
        const value = this.state[fieldname] || '---------'; //'r-x-w-rw-';
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
                <div class="col text-start">
                    <div class="container">
                        <div class="row">
                            <div class="col-2">
                                <div class="row"><div class="col fw-bold text-center">User</div></div>
                                <div class="row">
                                    <div class="col">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="User">
                                            <input type="checkbox" class="btn-check"
                                                id={'btn0_'+fieldname} name={'btn0_'+fieldname} autocomplete="off"
                                                defaultChecked={value[0]=='r'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn0_'+fieldname}>Read</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn1_'+fieldname} name={'btn1_'+fieldname} autocomplete="off"
                                                defaultChecked={value[1]=='w'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn1_'+fieldname}>Write</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn2_'+fieldname} name={'btn2_'+fieldname} autocomplete="off"
                                                defaultChecked={value[2]=='x'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn2_'+fieldname}>Execute</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="row"><div class="col fw-bold text-center">Group</div></div>
                                <div class="row">
                                    <div class="col">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Group">
                                            <input type="checkbox" class="btn-check"
                                                id={'btn3_'+fieldname} name={'btn3_'+fieldname} autocomplete="off"
                                                defaultChecked={value[3]=='r'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn3_'+fieldname}>Read</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn4_'+fieldname} name={'btn4_'+fieldname} autocomplete="off"
                                                defaultChecked={value[4]=='w'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn4_'+fieldname}>Write</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn5_'+fieldname} name={'btn5_'+fieldname} autocomplete="off"
                                                defaultChecked={value[5]=='x'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn5_'+fieldname}>Execute</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="row"><div class="col fw-bold text-center">All</div></div>
                                <div class="row">
                                    <div class="col">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="All">
                                            <input type="checkbox" class="btn-check"
                                                id={'btn6_'+fieldname} name={'btn6_'+fieldname} autocomplete="off"
                                                defaultChecked={value[6]=='r'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn6_'+fieldname}>Read</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn7_'+fieldname} name={'btn7_'+fieldname} autocomplete="off"
                                                defaultChecked={value[7]=='w'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn7_'+fieldname}>Write</label>

                                            <input type="checkbox" class="btn-check"
                                                id={'btn8_'+fieldname} name={'btn8_'+fieldname} autocomplete="off"
                                                defaultChecked={value[8]=='x'} onChange={this.permissions_handleChange} />
                                            <label class="btn btn-outline-primary" for={'btn8_'+fieldname}>Execute</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
        if(field._classname==='FPassword') {
            return this.renderFPassword(field, is_readonly);
        }
        if(field._classname==='FPercent') {
            return this.renderFPercent(field, is_readonly);
        }
        if(field._classname==='FPermissions') {
            return this.renderFPermissions(field,is_readonly);
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

        const visibleFields = this.form.detailColumnNames;
        const readonlyFields = this.form.detailReadOnlyColumnNames;

        // console.log("FForm.renderGroup: group="+JSON.stringify(group))
        return (
            <div class="component">
                <div class="row"><div class="col fw-bold text-middle bg-light">{groupName}</div></div>
                {group.map((fieldname) => {
                    if(visibleFields.indexOf(fieldname)<0) return;
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
                    <button class="btn btn-secondary btn-xs" type="button" >Delete</button>
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
                <div class="btn-group-sm" role="group">
                    <button class="btn btn-secondary btn-sm" type="button" >View</button>
                    <button class="btn btn-secondary btn-sm" type="button" >Close</button>
                </div>
            </div>
        );
    }

    forminstance_callback(jsonObj,form) {
        // console.log("FForm.forminstance_callback: start.")
        console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
        this.form = form;
        if(form===null) {
            this.props.onError(jsonObj);
            return;
        }
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
                    <div class="row">TODO: detail forms</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                </div>
            </form>
        );
    }
}

export { FForm };
