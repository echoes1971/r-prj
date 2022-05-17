import React, { useRef, useState } from 'react';

import JoditEditor from "jodit-react";

import { DBELink, DBELinkEdit } from './comp.ui.elements'

class FPermissions extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            [props.name]: props.value
        }

        this.name = props.name;
        this.title = props.title;
        this.cssClass = props.cssClass;
        this.is_readonly = props.is_readonly;
        this.field_prefix = props.field_prefix;

        this.handleChange = this.handleChange.bind(this);
    }

    handleChange(event) {
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
        console.log("FPermissions.")
        this.setState({[name]: value, [state_fieldname]:permissions});

        this.props.onChange({
            target: { name: state_fieldname, value: permissions}
        });
    }

    render() {
        const fieldname = this.name;
        // const is_readonly = this.props.is_readonly
        // const fieldclass = (
        //         (this.cssClass>'' ? this.cssClass : '') + ' '
        //         + (is_readonly ? 'form-control-plaintext' : '')
        //     ).trim();
        const value = this.state[fieldname] || '---------';
        return (
            <div class="row">
                <div class="col-1 text-end d-none d-lg-block">{this.title}</div>
                <div class="col text-start">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="container">
                                    <div class="row"><div class="col fw-bold text-center">User</div></div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="User">
                                                <input type="checkbox" class="btn-check"
                                                    id={'btn0_'+fieldname} name={'btn0_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[0]==='r'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn0_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn1_'+fieldname} name={'btn1_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[1]==='w'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn1_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn2_'+fieldname} name={'btn2_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[2]==='x'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn2_'+fieldname}>Execute</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="container">
                                    <div class="row"><div class="col fw-bold text-center">Group</div></div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Group">
                                                <input type="checkbox" class="btn-check"
                                                    id={'btn3_'+fieldname} name={'btn3_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[3]==='r'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn3_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn4_'+fieldname} name={'btn4_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[4]==='w'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn4_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn5_'+fieldname} name={'btn5_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[5]==='x'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn5_'+fieldname}>Execute</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="container">
                                    <div class="row"><div class="col fw-bold text-center">All</div></div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="All">
                                                <input type="checkbox" class="btn-check"
                                                    id={'btn6_'+fieldname} name={'btn6_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[6]==='r'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn6_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn7_'+fieldname} name={'btn7_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[7]==='w'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn7_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn8_'+fieldname} name={'btn8_'+fieldname} autoComplete="off"
                                                    defaultChecked={value[8]==='x'} onChange={this.handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn8_'+fieldname}>Execute</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

const FTextArea = props => {

    const dark_theme = props.dark_theme

    const field = props.field;
    const field_prefix = props.field_prefix;
    const is_readonly = props.is_readonly

    const fieldname = field_prefix + field.name
    const fieldclass = (
        (field.cssClass>'' ? field.cssClass : '') + ' '
        + (is_readonly ? 'form-control-plaintext'  + (dark_theme ? ' form-control-plaintext-dark' : '')
             : '')
    ).trim();

    return (
        <div class="row">
            <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
            <div class="col text-start">
                <textarea id={fieldname} name={fieldname}
                    class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                    value={this.state[fieldname]}
                    // size={field.size} width={field.width} height={field.height}
                    onChange={this.default_handleChange} />
            </div>
        </div>
        )
}

/**
 * See: https://github.com/jodit/jodit-react
 * and https://www.sanity.io/guides/top-5-rich-text-react-components
 */
class HTMLEdit extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            name: props.name,
            readonly: props.readonly,
            html: props.value
        }
        this.field_prefix = props.field_prefix
        console.log("HTMLEdit.onChange: field_prefix="+this.field_prefix)

        this.onChange = this.onChange.bind(this)
    }

    onChange(v) {
        const html = v
        this.setState({html: html})

        const state_fieldname = this.field_prefix + this.state.name;
        console.log("HTMLEdit.onChange: state_fieldname="+state_fieldname)
        this.props.onChange({
            target: { name: state_fieldname, value: html }
        });
    }

    render() {
        return (
            this.state.readonly ?
            <div class="border rounded" dangerouslySetInnerHTML={{__html: this.state.html}} />
            :
            <JoditEditor
            	// ref={editor}
                value={this.state.html}
                // config={config}
                tabIndex={1} // tabIndex of textarea
                onBlur={this.onChange} // preferred to use only this option to update the content for performance reasons
                onChange={newContent => {}}
            />
        );
    }
}

const FList = props => {
    const is_readonly = props.is_readonly
    // const field_prefix = props.field_prefix
    const field = props.field

    const [value, setValue] = useState(field.value)

    const fieldname = props.name //field_prefix + field.name
    const fieldclass = (
            (field.cssClass>'' ? field.cssClass : '') + ' ' + (is_readonly ? 'form-control-plaintext' : '')
        ).trim();
    const listvalues = field.valueslist

    return (
        <div class="row">
            <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
            <div class="col text-start">
                <select id={fieldname} name={fieldname}
                    class={fieldclass} readOnly={is_readonly}
                    value={value} onChange={e => {
                        const target = e.target;
                        const v = target.type === 'checkbox' ? target.checked : target.value;
                        const name = target.name;

                        props.onChange(name, v)
                        setValue(v)
                    }} >
                    {Object.keys(listvalues).map((k) => {
                        return (<option value={k}>{listvalues[k]}</option>);
                    })}
                </select>
            </div>
        </div>
    );
}

const FPercent = props => {
    const is_readonly = props.is_readonly
    const field = props.field

    const [value, setValue] = useState(field.value)

    const fieldname = props.name
    const fieldclass = (
            (field.cssClass>'' ? field.cssClass : '') + ' ' + (is_readonly ? 'form-control-plaintext' : '')
        ).trim()

    return (
        <div class="row">
            <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
            <div class="col text-start">
                <input id={fieldname} name={fieldname} type="number"
                    class={fieldclass} readOnly={is_readonly} placeholder={field.title}
                    value={value} size={field.size}
                    onChange={e => {
                        const target = e.target;
                        const v = target.type === 'checkbox' ? target.checked : target.value;
                        const name = target.name;

                        props.onChange(name, v)
                        setValue(v)
                    }} /> %
            </div>
        </div>
    )
}

const FKField = props => {
    const is_readonly = props.is_readonly
    const field = props.field
    const dbe = props.dbe
    const dark_theme = props.dark_theme
    const be = props.be

    const refSearchStarted = useRef(false)

    console.log("FKField: start "+field.name)

    // console.log("FKField: dbe="+JSON.stringify(dbe))

    const [value, setValue] = useState(field.value)
    const [decodedValue, setDecodedValue] = useState("--")
    console.log("FKField: decodedValue="+decodedValue)
    
    console.log("FKField: refSearchStarted="+refSearchStarted.current)

    const fk = dbe && dbe._fks ? dbe._fks.filter(v => v.colonna_fk===(field.name))[0] : {}
    console.log("FKField: fk="+JSON.stringify(fk))

    const decodeField = fk && fk.tabella_riferita==='users' ? "login"
        : fk && fk.tabella_riferita==='countrylist' ? "Common_Name"
        : "name"
    console.log("FKField: decodeField="+decodeField);

    if(fk && !refSearchStarted.current) { //decodedValue==="--") {
        refSearchStarted.current = true

        console.log("FKField: field="+JSON.stringify(field))

        console.log("FKField: fk.tabella_riferita="+fk.tabella_riferita);
        be.getDBEInstanceByTablename(fk.tabella_riferita, (jsonObj, mydbe) => {
            var search = mydbe;
            search.setValue(fk.colonna_riferita,value || field.value);
            console.log("FKField: search="+search.to_string());
        
            be.search(search, false, true, decodeField, (server_messages,dbelist) => {
                console.log("FKField: server_messages="+server_messages)
                console.log("FKField: dbelist="+JSON.stringify(dbelist))
                const mylist = dbelist
                if(mylist && mylist.length===1) {
                    const res = dbelist[0]
                    console.log("FKField: res="+JSON.stringify(res))
                    console.log("FKField: res.getValue("+decodeField+")="+res.getValue(decodeField))
                    setDecodedValue(res.getValue(decodeField))
                }
            });
        })
    }

    const fieldname = props.name
    const fieldclass = (
        (field.cssClass>'' ? field.cssClass : '') + ' ' +
        (is_readonly ?
            'form-control-plaintext' + (dark_theme ? ' form-control-plaintext-dark' : '')
            : '')
        ).trim();
    
    function _renderReadOnly() {
        return (
            <span>
                <input id={fieldname} name={fieldname} type="hidden" value={value || field.value} class={fieldclass}/>
                <DBELink dbeid={value || field.value} name={decodedValue} edit={!is_readonly}
                    be={be} tablename={fk ? fk.tabella_riferita : null} />
            </span>
        )
    }
    function _render() {
        return (
            <span>
                <input id={fieldname} name={fieldname} type="hidden" value={value || field.value} class={fieldclass}/>
                <DBELinkEdit dbeid={value || field.value} name={decodedValue} edit={!is_readonly}
                    fieldname={fieldname} fieldclass={fieldclass} be={be} tablename={fk ? fk.tabella_riferita : null}
                    onSelect={(newid) => {
                        console.log("FKField.onSelect: "+fieldname+"="+newid)
                        props.onChange(fieldname, newid)
                    }}
                    />
            </span>
        )
    }

    console.log("FKField: end "+field.name)
    return (
        <div class="row">
            <div class="col-1 text-end d-none d-lg-block">{field.title}</div>
            <div class="col text-start">{
                is_readonly ? _renderReadOnly() : _render()
            }</div>
        </div>
    )
}

export { FKField, FList, FPercent, FPermissions, HTMLEdit, FTextArea }
