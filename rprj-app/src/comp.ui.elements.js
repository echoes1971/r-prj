import React from 'react';

import { app_cfg } from './app.cgf';

const DBOLink = props => {

    const id = props.dbo ? props.dbo.getValue('id') : ''
    const name = props.dbo ? props.dbo.getValue('name') : ''
    const dbename = props.dbo ? props.dbo.dbename : ''

    return <a class={props.class} aria-current={props.ariacurrent} href={ app_cfg.root_path + "o/" + id}>{name}</a>
}

export { DBOLink }
