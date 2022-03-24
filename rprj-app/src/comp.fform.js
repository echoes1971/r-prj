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

        this.forminstance_callback = this.forminstance_callback.bind(this);
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

    // shouldComponentUpdate(nextProps, nextState) {
    //     console.log(nextProps)
    //     console.log(nextState)
    //     console.log(this.props)
    //     console.log(this.state)
    //     return nextProps.formname!=this.state.formname;
    // }

    forminstance_callback(jsonObj,form) {
        console.log("FForm.forminstance_callback: start.")
        console.log("FForm.forminstance_callback: form="+JSON.stringify(form))
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
        this.setState({
            detailIcon: form.detailIcon,
            detailTitle: form.detailTitle
        })
        console.log("FForm.forminstance_callback: end.")
    }

    render() {
        console.log("SUNCHI" + this.state.formname)
        return (
            <form>
                SUNCHI {this.state.detailTitle}
            </form>
        );
    }
}

export { FForm };
