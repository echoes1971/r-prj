import React from 'react';

class FPermissions extends React.Component {
    constructor(props) {
        super(props);

        console.log(props);
        
        this.state = {
            endpoint: props.endpoint,
            formname: props.formname,
            detailIcon: "icons/user.png",
            detailTitle: "User"
        }

        this.name = props.name;
        this.title = props.title;
        this.cssClass = props.cssClass;
        this.is_readonly = props.is_readonly;
        this.field_prefix = props.field_prefix;

        this.permissions_handleChange = this.permissions_handleChange.bind(this);
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

        this.props.onChange({
            target: { name: state_fieldname, value: permissions}
        });
    }

    render(field, is_readonly=false) {
        const fieldname = this.name;
        const fieldclass = (
                (this.cssClass>'' ? this.cssClass : '') + ' '
                + (is_readonly ? 'form-control-plaintext' : '')
            ).trim();
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
                                                    id={'btn0_'+fieldname} name={'btn0_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[0]=='r'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn0_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn1_'+fieldname} name={'btn1_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[1]=='w'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn1_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn2_'+fieldname} name={'btn2_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[2]=='x'} onChange={this.permissions_handleChange} />
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
                                                    id={'btn3_'+fieldname} name={'btn3_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[3]=='r'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn3_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn4_'+fieldname} name={'btn4_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[4]=='w'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn4_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn5_'+fieldname} name={'btn5_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[5]=='x'} onChange={this.permissions_handleChange} />
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
                                                    id={'btn6_'+fieldname} name={'btn6_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[6]=='r'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn6_'+fieldname}>Read</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn7_'+fieldname} name={'btn7_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[7]=='w'} onChange={this.permissions_handleChange} />
                                                <label class="btn btn-outline-secondary" for={'btn7_'+fieldname}>Write</label>

                                                <input type="checkbox" class="btn-check"
                                                    id={'btn8_'+fieldname} name={'btn8_'+fieldname} autocomplete="off"
                                                    defaultChecked={value[8]=='x'} onChange={this.permissions_handleChange} />
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

export { FPermissions };
