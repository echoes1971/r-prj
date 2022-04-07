import React from 'react';

import { RLocalStorage } from './comp.ls';
import { DBOLink } from './comp.ui.elements';

class RNav extends React.Component {
    constructor(props) {
        super(props);

        // console.log(props);
        
        this.state = {
             dark_theme: props.dark_theme
            ,p_usr: ''
            ,p_pwd: ''
            ,user_fullname: props.user_fullname
            ,root_obj: props.root_obj
            ,top_menu: props.top_menu
        }

        this.ls = new RLocalStorage("RNav");

        // this.be = new BackEndProxy(this.state.endpoint);
        this.renderProfile = this.renderProfile.bind(this);

        this.default_handleChange = this.default_handleChange.bind(this);
        this.default_handleSubmit = this.default_handleSubmit.bind(this);

        this.logout_handleSubmit = this.logout_handleSubmit.bind(this);

        this.btnLogin = this.btnLogin.bind(this);
        this.btnLogout = this.btnLogout.bind(this);

        this.theme_handleChange = this.theme_handleChange.bind(this);
        this.clean_ls_handleChange = this.clean_ls_handleChange.bind(this);
    }

    componentDidUpdate(prevProps, prevState) {
        // console.log("FForm.componentDidUpdate: prevProps="+JSON.stringify(prevProps))
        // console.log("FForm.componentDidUpdate: prevState="+JSON.stringify(prevState))
        // console.log("FForm.componentDidUpdate: props="+JSON.stringify(this.props))
        // console.log("FForm.componentDidUpdate: state="+JSON.stringify(this.state))
        if(this.props.dark_theme !== prevProps.dark_theme) {
            this.setState({dark_theme: this.props.dark_theme})
        }
        if(this.props.user_fullname !== prevProps.user_fullname) {
            this.setState({user_fullname: this.props.user_fullname})
        }
        if(JSON.stringify(this.props.root_obj) !== JSON.stringify(prevProps.root_obj)) {
            this.setState({root_obj: this.props.root_obj})
        }
        if(JSON.stringify(this.props.top_menu) !== JSON.stringify(prevProps.top_menu)) {
            this.setState({top_menu: this.props.top_menu})
        }
    }

    clean_ls_handleChange() {
        this.ls.cleanAll();
    }

    default_handleSubmit(event) {
        event.preventDefault();
    }
    default_handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.setState({[name]: value});
    }
    login_handleSubmit(event) {
        event.preventDefault();
    }
    btnLogin() {
        const usr = this.state.p_usr;
        const pwd = this.state.p_pwd;
        this.props.onLogin(usr,pwd);
    }
    logout_handleSubmit(event) {
        event.preventDefault();
    }
    btnLogout() {
        this.props.onLogout();
    }

    theme_handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        // const name = target.name;

        // this.setState({[name]: value});
        this.props.onTheme(value);

        // event.preventDefault();
        // event.stopPropagation();
    }
    stopPropagation(e) {
        // e.preventDefault();
        // e.stopPropagation();
    }

    renderProfile(user_fullname, dark_theme) {
        const dropdown_menu_class = "nav-item dropdown dropdown-menu-right" + (dark_theme ? ' dropdown-menu-dark' : '')
        const dropdown_menu_class_2 = "dropdown-menu dropdown-menu-end" + (dark_theme ? ' dropdown-menu-dark' : '')
        if(user_fullname) {
            return (
                <div class={dropdown_menu_class}>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        {/* Visible ONLY on LG screens */}
                        <li class="nav-item dropdown d-none d-lg-block">

                            <a class="nav-link dropdown-toggle rounded" href="#" id="navbarProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {user_fullname}
                            </a>

                            <ul class={dropdown_menu_class_2} aria-labelledby="navbarProfileDropdown">
                                <li class="dropdown-item">
                                    <form onSubmit={this.default_handleSubmit} >
                                        <div class="form-check form-switch">
                                            <label class="form-check-label" for="dark_theme04">Dark Theme</label>
                                            <input class="form-check-input" type="checkbox" id="dark_theme04" name="dark_theme"
                                                onClick={this.stop_propagation} onChange={this.theme_handleChange}
                                                checked={this.state.dark_theme} />
                                        </div>
                                    </form>
                                </li>
                                <li class="dropdown-item">
                                    <form onSubmit={this.default_handleSubmit} >
                                        <button class="btn btn-secondary" onClick={this.clean_ls_handleChange} >Clean LS</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider" /></li>
                                <li class="dropdown-item">
                                    <form onSubmit={this.default_handleSubmit} >
                                        <a class="nav-link" href="#" onClick={this.btnLogout}>Logout</a>
                                    </form>
                                </li>
                            </ul>

                        </li>

                        {/* Visible ONLY on SM and MD screens */}
                        <li class="dropdown-item d-md-block d-lg-none">
                            <form onSubmit={this.default_handleSubmit} >
                                <div class="form-check form-switch">
                                <label class="form-check-label" for="dark_theme03">Dark Theme</label>
                                <input class="form-check-input" type="checkbox" id="dark_theme03" name="dark_theme"
                                        onChange={this.theme_handleChange} checked={this.state.dark_theme} />
                                </div>
                            </form>
                        </li>
                        <li class="dropdown-item d-md-block d-lg-none">
                            <form onSubmit={this.default_handleSubmit} >
                                <button class="btn btn-secondary" onClick={this.clean_ls_handleChange} >Clean LS</button>
                            </form>
                        </li>
                        <li class="d-md-block d-lg-none"><hr class="dropdown-divider" /></li>
                        <li class="dropdown-item d-md-block d-lg-none">
                            <form onSubmit={this.default_handleSubmit} >
                                <a class="nav-link" href="#" onClick={this.btnLogout}>Logout</a>
                            </form>
                        </li>

                    </ul>
                </div>
            );
        }
        return (
            <div class={dropdown_menu_class}>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    {/* Visible ONLY on LG screens */}
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarLoginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Login
                        </a>
                        <ul class={dropdown_menu_class_2} aria-labelledby="navbarLoginDropdown">
                            <li class="dropdown-item">
                                <form onSubmit={this.default_handleSubmit} >
                                    <div class="form-check form-switch">
                                    <label class="form-check-label" for="dark_theme00">Dark Theme</label>
                                        <input class="form-check-input" type="checkbox" id="dark_theme00" name="dark_theme"
                                               onClick={this.stop_propagation} onChange={this.theme_handleChange}
                                               checked={this.state.dark_theme} />
                                    </div>
                                </form>
                            </li>
                            <li class="dropdown-item">
                                <form onSubmit={this.default_handleSubmit} >
                                    <button class="btn btn-secondary" onClick={this.clean_ls_handleChange} >Clean LS</button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li class="dropdown-item">
                                <form onSubmit={this.default_handleSubmit} >
                                    <label class="d-none d-lg-block" for="p1_usr">Username</label> <input id="p1_usr" name="p_usr" value={this.state.usr} onChange={this.default_handleChange} placeholder="Username"/>
                                    <br />
                                    <label class="d-none d-lg-block" for="p1_pwd">Password</label> <input id="p1_pwd" name="p_pwd" type="password" value={this.state.pwd} onChange={this.default_handleChange} placeholder="Password" /> <br />
                                    <button class="btn btn-secondary" onClick={this.btnLogin}>Login</button>
                                </form>
                            </li>
                        </ul>
                    </li>

                    {/* Visible ONLY on SM and MD screens */}
                    <li class="dropdown-item d-md-block d-lg-none">
                        <form onSubmit={this.default_handleSubmit} >
                            <div class="form-check form-switch">
                            <label class="form-check-label" for="dark_theme01">Dark Theme</label>
                            <input class="form-check-input" type="checkbox" id="dark_theme01" name="dark_theme"
                                    onClick={this.stop_propagation} onChange={this.theme_handleChange}
                                    checked={this.state.dark_theme} />
                            </div>
                        </form>
                    </li>
                    <li class="dropdown-item d-md-block d-lg-none">
                        <form onSubmit={this.default_handleSubmit} >
                            <button class="btn btn-secondary" onClick={this.clean_ls_handleChange} >Clean LS</button>
                        </form>
                    </li>
                    <li class="d-md-block d-lg-none"><hr class="dropdown-divider" /></li>
                    <li class="dropdown-item d-md-block d-lg-none">
                        <form onSubmit={this.default_handleSubmit} >
                            <label for="p2_usr">Username</label> <input id="p2_usr" name="p_usr" value={this.state.usr} onChange={this.default_handleChange} placeholder="Username"/>
                            <br />
                            <label for="p2_pwd">Password</label> <input id="p2_pwd" name="p_pwd" type="password" value={this.state.pwd} onChange={this.default_handleChange} placeholder="Password" /> <br />
                            <button class="btn btn-secondary" onClick={this.btnLogin}>Login</button>
                        </form>
                    </li>
                </ul>
            </div>
        );
    }

    render() {
        //  &#129489;
        const current_theme = this.state.dark_theme ? 'dark' : 'light';
        const nav_class = "navbar sticky-top navbar-expand-lg navbar-"+current_theme+" bg-"+current_theme
        const dropdown_menu_class = "dropdown-menu" + (this.state.dark_theme ? ' dropdown-menu-dark' : '')
        const root_obj = this.state.root_obj;
        const top_menu = this.state.top_menu;
        return (
            <nav class={nav_class}>
                <a class="navbar-brand d-none d-lg-block" href="#">R-Prj</a>

                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>&#128295;
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                {/* <a class="nav-link active" aria-current="page" href="#">Home</a> */}
                                <DBOLink class="nav-link active" aria-current="page" dbo={root_obj} />
                            </li>
                            {top_menu.map((k) => {
                                // return (<li class="nav-item">{k}</li>)
                                if(k===null || ['DBEFolder','DBELink','DBEPeople'].find(k.getTypeName())<0) {
                                    return ({k})
                                } else {
                                    return (<li class="nav-item"><DBOLink class="nav-link" dbo={k} /></li>)
                                }
                            })}
                            {/* <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Dropdown
                                </a>
                                <ul class={dropdown_menu_class} aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                </ul>
                            </li> */}

                            <li class="nav-item">
                                <form class="d-flex">
                                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" />
                                    <button class="btn btn-outline-success" type="submit">Search</button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <a class="navbar-brand d-block d-lg-none" href="#">R-Prj</a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarProfileContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    {this.state.user_fullname ? ( <span>&#129333;</span> ) : ( <span>&#128100;</span> ) }
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarProfileContent">
                    { this.renderProfile(this.state.user_fullname,this.state.dark_theme) }
                    </div>
                </div>
            </nav>
        );
    }
}

export default RNav;
