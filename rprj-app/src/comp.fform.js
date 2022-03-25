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
        const fieldtype = field._classname==="FPassword" ? "password"
                    : field.type==="n" ? "number"
                    : field.type==='d' ? 'datetime-local'
                    : "text"; // n=number s=string d=datetime
        const fieldclass = (
                (field.cssClass>'' ? field.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '')
            ).trim();
        return (
            <div class="row">
                <div class="col-1 text-end">{field.title}</div>
                <div class="col text-start">{is_readonly}
                    {   class_unknown ?
                        <p>{field._classname}</p>
                        :
                        <input id={field.name} name={field.name} type={fieldtype}
                                class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                                value={this.state[field.name]} size={field.size}
                            onChange={this.default_handleChange} />
                    }
                </div>
            </div>
        );
    }
    renderFTextArea(field) {
        return (
            <div class="row">
                <div class="col-1 text-end">{field.title}</div>
                <div class="col text-start">
                    <textarea id={field.name} name={field.name} placeholder={field.title}
                        value={this.state[field.name]} size={field.size}
                        width={field.width} height={field.height}
                        onChange={this.default_handleChange} />
                </div>
            </div>
        );
    }
    renderField(f) {
        const field = this._getField(f);
        // console.log("FForm.renderField: field._classname="+field._classname)
        if(field._classname==='FTextArea') {
            return this.renderFTextArea(field)
        }
        if(["FDateTime","FNumber", "FString", "FPassword"].indexOf(field._classname)>=0) {
            return this.renderFField(field);
        }
        if(["FDateTimeReadOnly"].indexOf(field._classname)>=0) {
            return this.renderFField(field,true);
        }
        return this.renderFField(field, false, true);
    }
    renderGroup(g) {
        const decodeGroupNames = this.form.decodeGroupNames
        const g1 = g.length>0 ? g : "_"
        const groupName = decodeGroupNames[g]
        const group = this.form.groups[g]
        console.log("FForm.forminstance_callback: group="+JSON.stringify(group))
        return (
            <div class="component">
                <div class="row"><div class="col fw-bold text-middle">{groupName}</div></div>
                {group.map(this.renderField)}
            </div>
        );
    }
    renderGroups() {
        if(this.form===null) {
            return ("--");
        }
        const groups = this.form.groups!==null ? this.form.groups : [];
        console.log("FForm.forminstance_callback: groups="+JSON.stringify(groups))
        // for(const g in this.form.groups) {
        //     const g1 = g.length>0 ? g : "_";
        //     ret += this.renderGroup(g1);
        // }
        return (
            <div class="container">
                {Object.keys(groups).map(this.renderGroup)}
            </div>
        );
    }

    forminstance_callback(jsonObj,form) {
        console.log("FForm.forminstance_callback: start.")
        console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
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
        console.log("FForm.forminstance_callback: form.detailTitle="+form.detailTitle)
        const detailTitle = form.detailTitle
        this.setState({
        //     detailIcon: form.detailIcon,
            detailTitle: detailTitle
        })
        console.log("FForm.forminstance_callback: end.")
    }

    render() {
        console.log("FForm.forminstance_callback: this.state.formname=" + this.state.formname)
        const detailTitle = this.state.detailTitle;
        const f = this.renderGroups();
        return (
            <form onSubmit={this.default_handleSubmit} encType={this.form!==null ? this.form.enctype : null} >
                <div class="container border rounded">
                    <div class="row text-center border-bottom"><div class="col fw-bold">{detailTitle}</div></div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                    <div class="row">{f}</div>
                    <div class="row"><div class="row">&nbsp;</div></div>
                </div>
            </form>
        );
    }
}

export { FForm };
