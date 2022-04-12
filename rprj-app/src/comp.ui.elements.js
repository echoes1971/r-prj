import React from 'react';

import { app_cfg } from './app.cgf';


const IFRTree = props => {
    const dark_theme = props.dark_theme || false

    if(dark_theme) {
        return (
            <span>
              <div class="d-block d-md-none text-center"><img src={app_cfg.root_path+"logo256_2.png"} /></div>
              <div class="d-none d-md-block text-center"><img src={app_cfg.root_path+"logo512_2.png"} /></div>
            </span>)
        }
    return (
        <span>
            <div class="d-block d-md-none text-center"><img src={app_cfg.root_path+"logo256.png"} /></div>
            <div class="d-none d-md-block text-center"><img src={app_cfg.root_path+"logo512.png"} /></div>
        </span>)
}
const IFRTreeAll = props => {
    const dark_theme = props.dark_theme || false

    if(dark_theme) {
        return (
            <span>
              <div class="text-center"><img src={app_cfg.root_path+"logo16_2.png"} /><img src={app_cfg.root_path+"logo32_2.png"} /><img src={app_cfg.root_path+"logo64_2.png"} /><img src={app_cfg.root_path+"logo128_2.png"} /><img src={app_cfg.root_path+"logo256_2.png"} /><img src={app_cfg.root_path+"logo512_2.png"} /></div>
              <div class="text-center"><img src={app_cfg.root_path+"logo512_2.png"} /><img src={app_cfg.root_path+"logo256_2.png"} /><img src={app_cfg.root_path+"logo128_2.png"} /><img src={app_cfg.root_path+"logo64_2.png"} /><img src={app_cfg.root_path+"logo32_2.png"} /><img src={app_cfg.root_path+"logo16_2.png"} /></div>
            </span>)
        }
    return (
        <span>
          <div class="text-center"><img src={app_cfg.root_path+"logo16.png"} /><img src={app_cfg.root_path+"logo32.png"} /><img src={app_cfg.root_path+"logo64.png"} /><img src={app_cfg.root_path+"logo128.png"} /><img src={app_cfg.root_path+"logo256.png"} /><img src={app_cfg.root_path+"logo512.png"} /></div>
          <div class="text-center"><img src={app_cfg.root_path+"logo512.png"} /><img src={app_cfg.root_path+"logo256.png"} /><img src={app_cfg.root_path+"logo128.png"} /><img src={app_cfg.root_path+"logo64.png"} /><img src={app_cfg.root_path+"logo32.png"} /><img src={app_cfg.root_path+"logo16.png"} /></div>
        </span>)
}

/** A generic link */
const RLink = props => {

    const path = props.path;
    const name = props.name;

    return <a class={props.class} aria-current={props.ariacurrent} href={ app_cfg.root_path + path}>{name}</a>
}

const DBOLink = props => {

    const id = props.dbo ? props.dbo.getValue('id') : ''
    const name = props.dbo ? props.dbo.getValue('name') : ''
    const dbename = props.dbo ? props.dbo.dbename : ''

    return <a class={props.class} aria-current={props.ariacurrent} href={ app_cfg.root_path + "o/" + id + "/"}>{name}</a>
}

export { DBOLink, IFRTree, IFRTreeAll, RLink }
