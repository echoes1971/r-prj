import React, {useRef, useState} from 'react';

import { app_cfg } from './app.cgf';
import { getFlagEmojiByID } from './countries'

const icon2emoji = (detail_icon) => {
    var ret = ('');
    switch(detail_icon) {
        case 'icons/user.png':
            ret = (<span>&#128100;</span>)
            break
        case 'icons/group_16x16.gif':
            ret = (<span>&#128101;</span>)
            break
        case 'icons/text-x-log.png':
            // ret = (<span>&#128195;</span>)
            ret = (<span>&#128220;</span>)
            break
        case 'icons/company_16x16.gif':
            ret = (<span>&#127981;</span>)
            break
        case 'icons/people.png':
            ret = (<span>&#129333;</span>)
            break
        case 'icons/event_16x16.png':
            ret = (<span>&#128198;</span>)
            break
        case 'icons/file_16x16.gif':
            ret = (<span>&#128196;</span>)
            break
        case 'icons/folder_16x16.gif':
            ret = (<span>&#128193;</span>)
            break
        case 'icons/link_16x16.gif':
            ret = (<span>&#128279;</span>)
            break
        case 'icons/note_16x16.gif':
            ret = (<span>&#128466;</span>)
            break
        case 'icons/page_16x16.gif':
            ret = (<span>&#128195;</span>)
            break
        case 'icons/news.png':
            ret = (<span>&#128240;</span>)
            break
        case 'icons/project_16x16.gif':
            ret = (<span>&#127959;</span>) // 128200
            break
        case 'icons/timetrack_16x16.gif':
            ret = (<span>&#9201;</span>)
            break
        case 'icons/task_16x16.gif':
            ret = (<span>&#9745;</span>)
            break
        default:
            ret = (<span>&#9881;</span>)
            // ret = (<b>{detail_icon}</b>)
            break
    }
    return ret
}


const IFRTree = props => {
    const dark_theme = props.dark_theme || false

    if(dark_theme) {
        return (
            <span>
              <div class="d-block d-md-none text-center"><img src={app_cfg.root_path+"logo256_2.png"} alt='' /></div>
              <div class="d-none d-md-block text-center"><img src={app_cfg.root_path+"logo512_2.png"} alt='' /></div>
            </span>)
        }
    return (
        <span>
            <div class="d-block d-md-none text-center"><img src={app_cfg.root_path+"logo256.png"} alt='' /></div>
            <div class="d-none d-md-block text-center"><img src={app_cfg.root_path+"logo512.png"} alt='' /></div>
        </span>)
}
const IFRTreeAll = props => {
    const dark_theme = props.dark_theme || false

    if(dark_theme) {
        return (
            <span>
              <div class="text-center"><img src={app_cfg.root_path+"logo16_2.png"} alt='' /><img src={app_cfg.root_path+"logo32_2.png"} alt='' /><img src={app_cfg.root_path+"logo64_2.png"} alt='' /><img src={app_cfg.root_path+"logo128_2.png"} alt='' /><img src={app_cfg.root_path+"logo256_2.png"} alt='' /><img src={app_cfg.root_path+"logo512_2.png"} alt='' /></div>
              <div class="text-center"><img src={app_cfg.root_path+"logo512_2.png"} alt='' /><img src={app_cfg.root_path+"logo256_2.png"} alt='' /><img src={app_cfg.root_path+"logo128_2.png"} alt='' /><img src={app_cfg.root_path+"logo64_2.png"} alt='' /><img src={app_cfg.root_path+"logo32_2.png"} alt='' /><img src={app_cfg.root_path+"logo16_2.png"} alt='' /></div>
            </span>)
        }
    return (
        <span>
          <div class="text-center"><img src={app_cfg.root_path+"logo16.png"} alt='' /><img src={app_cfg.root_path+"logo32.png"} alt='' /><img src={app_cfg.root_path+"logo64.png"} alt='' /><img src={app_cfg.root_path+"logo128.png"} alt='' /><img src={app_cfg.root_path+"logo256.png"} alt='' /><img src={app_cfg.root_path+"logo512.png"} alt='' /></div>
          <div class="text-center"><img src={app_cfg.root_path+"logo512.png"} alt='' /><img src={app_cfg.root_path+"logo256.png"} alt='' /><img src={app_cfg.root_path+"logo128.png"} alt='' /><img src={app_cfg.root_path+"logo64.png"} alt='' /><img src={app_cfg.root_path+"logo32.png"} alt='' /><img src={app_cfg.root_path+"logo16.png"} alt='' /></div>
        </span>)
}

/** A generic link */
const RLink = props => {

    const path = props.path;
    const name = props.name;

    return <a class={props.class} aria-current={props.ariacurrent} href={ app_cfg.root_path + path}>{name}</a>
}

const DBOButton = props => {
    const dbo = props.dbo
    // console.log("DBOButton: dbo="+JSON.stringify(dbo))
    // if(! ('getValue' in dbo)) alert("cippa")
    const id = dbo ? dbo.getValue('id') : ''
    const name = props.name>'' ? props.name
            : (dbo!==null && dbo!==undefined ? dbo.getValue('name') : '')
    const edit = props.edit || false

    const link = app_cfg.root_path + (edit ? "e/" : "o/") + id + "/"

    return <button class={props.class} type="button" onClick={() => {window.location=link}} >{name}</button>
}

const DBOLink = props => {
    const id = props.dbo ? props.dbo.getValue('id') : ''
    const name = props.name || (props.dbo ? props.dbo.getValue('name') : '')
    const edit = props.edit || false
    const detailIcon = props.detailIcon || ''
    const detailIconTitle = props.detailIconTitle || ''

    return <span title={detailIconTitle}>{detailIcon}{detailIcon>'' ? ' ' : ''}<a class={props.class} aria-current={props.ariacurrent} href={ app_cfg.root_path + (edit ? "e/" : "o/") + id + "/"}>{name}</a></span>
}

const DBELink = props => {
    const id = props.dbeid ? props.dbeid : ''
    const name = props.name || (props.dbo ? props.dbo.getValue('name') : '')
    const edit = props.edit || false

    const be = props.be
    const tablename = props.tablename

    const [detailIcon, setDetailIcon] = useState(props.detailIcon || '')
    const [detailIconTitle, setDetailIconTitle] = useState(props.detailIconTitle || '')
    const refSearchStarted = useRef(false)

    if(detailIcon==='' && detailIconTitle==='' && tablename && tablename!=='countrylist' && !refSearchStarted.current && be) {
        refSearchStarted.current = true
        console.log("DBELink.search: tablename="+JSON.stringify(tablename))
        be.getDBEInstanceByTablename(tablename, (jsonObj, mydbe) => {
            const dbe = mydbe
            console.log("DBELink.search: dbe="+JSON.stringify(dbe))
            be.getFormInstanceByDBEName(dbe.dbename, (jsonObj, form) => {
                console.log("DBELink.search: dbe.dbename="+dbe.dbename)
                const myform = form
                console.log("DBELink.search: jsonObj="+JSON.stringify(jsonObj))
                if(myform) {
                    console.log("DBELink.search: myform="+JSON.stringify(myform))
                    setDetailIcon(icon2emoji(myform.detailIcon))
                    setDetailIconTitle(form.detailTitle)
                }
            })
        })
    }
    return (
        <span title={detailIconTitle}>{detailIcon}{detailIcon>'' ? ' ' : ''}
            {tablename==='countrylist' ? getFlagEmojiByID(id) + ' ' : '' }
            <a class={props.class} aria-current={props.ariacurrent} href={ tablename!=='countrylist' ? app_cfg.root_path + (edit ? "e/" : "o/") + id + "/" : null}>{name}</a>
        </span>
        )
}

const DBELinkEdit = props => {
    const id = props.dbeid ? props.dbeid : ''
    const name = props.name || (props.dbo ? props.dbo.getValue('name') : '')
    const edit = props.edit || false
    const fieldname = props.fieldname || ''
    const fieldclass = props.fieldclass || ''

    const be = props.be
    const tablename = props.tablename
    const decodeField = props.decodeField

    const [detailIcon, setDetailIcon] = useState(props.detailIcon || '')
    const [detailIconTitle, setDetailIconTitle] = useState(props.detailIconTitle || '')
    const refSearchStarted = useRef(false)

    const [searchString, setSearchString] = useState('')
    const [listvalues, setListvalues] = useState({})    //  {'60':'France', '82':'Italy', '84': 'Japan', '167': 'Switzerland', 'xxx':'zozzo'}

    if(detailIcon==='' && detailIconTitle==='' && tablename && tablename!=='countrylist' && !refSearchStarted.current && be) {
        refSearchStarted.current = true
        console.log("DBELink.search: tablename="+JSON.stringify(tablename))
        be.getDBEInstanceByTablename(tablename, (jsonObj, mydbe) => {
            const dbe = mydbe
            console.log("DBELink.search: dbe="+JSON.stringify(dbe))
            be.getFormInstanceByDBEName(dbe.dbename, (jsonObj, form) => {
                console.log("DBELink.search: dbe.dbename="+dbe.dbename)
                const myform = form
                console.log("DBELink.search: jsonObj="+JSON.stringify(jsonObj))
                if(myform) {
                    console.log("DBELink.search: myform="+JSON.stringify(myform))
                    setDetailIcon(icon2emoji(myform.detailIcon))
                    setDetailIconTitle(form.detailTitle)
                }
            })
        })
    }
    return (
        <span title={detailIconTitle}>{detailIcon}{detailIcon>'' ? ' ' : ''}
            {tablename==='countrylist' ? getFlagEmojiByID(id) + ' ' : '' }
            <a class="dropdown-toggle" id={'dropdown_' + fieldname} role="button" data-bs-toggle="dropdown" aria-expanded="false"
                href={ tablename!=='countrylist' ? app_cfg.root_path + (edit ? "e/" : "o/") + id + "/" : null }>{name}</a>
            <ul class="dropdown-menu" aria-labelledby={'dropdown_' + fieldname}>
                <li>
                    <input id={fieldname} name={fieldname} value={searchString} class={('form-control '+fieldclass).trim()} placeholder="Search..."
                        onChange={e => {
                            const target = e.target;
                            const v = target.value;
                            const name = target.name;
                            // console.log("DBELinkEdit: "+name+"="+v)
                            if(v.length>0) {
                                be.getDBEInstanceByTablename(tablename, (jsonObj, mydbe) => {
                                    var search = mydbe
                                    search.setValue(decodeField,v+"%%")
                                    // console.log("search="+search.to_string())
                                    const uselike = true
                                    const caseSensitive = false
                                    const orderBy = decodeField
                                    be.search(search, uselike, caseSensitive, orderBy, (server_messages, dbelist) => {
                                        // console.log("DBELinkEdit.onChange: start.");
                                        // console.log(server_messages)
                                        var tmp = {}
                                        for(var i=0; dbelist!==null && i<dbelist.length; i++) {
                                            tmp[dbelist[i].getValue('id')] = dbelist[i].getValue(decodeField)
                                        }
                                        setListvalues(tmp)
                                        // console.log("DBELinkEdit.onChange: end.")
                                    })
                                })
                            }
                            setSearchString(v)
                            // props.onChange(name, v)
                        }} />
                </li>
                {Object.keys(listvalues).map((k) => {
                    return (<li><a class="dropdown-item" href="#" role="button"
                        onClick={() => {
                            refSearchStarted.current = false
                            props.onSelect(k)
                        }}>{tablename==='countrylist' ? getFlagEmojiByID(k) + ' ' : '' }{listvalues[k]}</a></li>)
                })}
            </ul>
        </span>
        )
}

export { DBELink, DBELinkEdit, DBOButton, DBOLink, icon2emoji, IFRTree, IFRTreeAll, RLink }
