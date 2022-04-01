import React from 'react';

import { BackEndProxy } from './be';
import { FForm } from './comp.fform';
import { ServerResponse } from './comp.test.serverresponse';

/*
 * This to develop backend functions to retrieve all the forms in form schema
 * and use this to dinamically create forms on react
 */
class FormExplorer extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            endpoint: props.endpoint,
            server_response_0: "",
            server_response_1: "",
            debug_form: "",
            selectedClassname: "FTodo", //null,
            classnames: [{value: "cippa", label: "Cippa"},{value:"lippa", label:"Lippa"}]
        }

        this.be = new BackEndProxy(this.state.endpoint);

        // Bindings
        this.default_callback = this.default_callback.bind(this);
        this.default_handleChange = this.default_handleChange.bind(this);
        this.default_handleSubmit = this.default_handleSubmit.bind(this);

        this.classnames_callback = this.classnames_callback.bind(this);
        this.btnClassNames = this.btnClassNames.bind(this);

        this.select_handleChange = this.select_handleChange.bind(this);

        this.forminstance_callback = this.forminstance_callback.bind(this);

        this.onSave = this.onSave.bind(this);
        this.onError = this.onError.bind(this);
    }

    componentDidMount() {
        this.be.getAllFormClassnames(this.classnames_callback);
    }

    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const target = event.target;
        console.log("target.type: "+target.type)
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

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
            selectedClassname: null,
            classnames: myoptions
        })
    }

    forminstance_callback(jsonObj,form) {
        console.log("FormExplorer.forminstance_callback: start.");
        console.log("FormExplorer.forminstance_callback: form="+JSON.stringify(form));
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
        // console.log("FormExplorer.forminstance_callback: form._classname="+form._classname);
        // console.log("FormExplorer.forminstance_callback: classname="+classname);
        this.setState({
            selectedClassname: classname
            ,server_response_0: jsonObj[0]
            ,server_response_1: "" + s.join("\n")
        })
        console.log("FormExplorer.forminstance_callback: end.");
    }
    select_handleChange(event) {
        const target = event.target;
        console.log("target.type: "+target.type)
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.setState({[name]: value});
        // RRA: if you enable this here, and then you setState in the callback, it will complain (and block) that the component to update has been unmounted
        // this.setState({selectedClassname: selectedOption.value});
        // console.log("FormExplorer.select_handleChange: selectedOption="+JSON.stringify(selectedOption));
        this.be.getFormInstance(value,this.forminstance_callback);
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
        // console.log("FormExplorer.render: selectedClassname="+selectedClassname);
        return (
            <div class={"component "+this.props.class}>
                <div class="row">
                    <div class="col text-middle fw-bold">Form Explorer</div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class="row">
                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <select value={selectedClassname} onChange={this.select_handleChange} >
                                {Object.keys(this.state.classnames).map((x) => {
                                    return (<option value={this.state.classnames[x].value}>{this.state.classnames[x].label}</option>);
                                }
                                )}
                            </select>
                        </form>
                    </div>

                    <div class="col">
                        <form onSubmit={this.default_handleSubmit}>
                            <button class="btn btn-secondary" onClick={this.btnClassNames}>Class Names</button>
                        </form>
                    </div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class="row">
                    <div class="col">
                        <FForm endpoint={this.state.endpoint} formname={selectedClassname}
                            onSave={this.onSave} onError={this.onError} />
                    </div>
                </div>

                <div class="row"><div class="col">&nbsp;</div></div>

                <div class={"component "+this.props.class}>
                    <div class="row">
                        <div class="col">
                            <div class="component border rounded">
                                <div class="row">
                                    <div class="col-1 fw-bold">Debug Form</div>
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
