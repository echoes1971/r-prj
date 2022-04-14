// Client
import { DBEntity, DBMgr, JSONDBConnection } from './db/dblayer'

import { app_cfg } from './app.cgf';
// import { RLocalStorage } from './comp.ls';

/**
 * Back End Proxy
 * 
 * This class is a frontend for the UI for all the calls in the Backend.
 * 
 */
class BackEndProxy {
    constructor(endpoint = 'http://localhost:8080/jsonserver.php') {
        this.endpoint = endpoint

        this.con = new JSONDBConnection(endpoint, true);
        this.dbmgr = new DBMgr(this.con);
        // this.ls = new RLocalStorage("BE");
        this.dbe2formMapping = {}
        this.root_obj = null

        this._user_profile = null


        this.getDBEInstance = simpleCache(this.getDBEInstance.bind(this))
        this.getFormInstance = simpleCache(this.getFormInstance.bind(this))
        this.getFormInstanceByDBEName = simpleCache(this.getFormInstanceByDBEName.bind(this))
    }

    do_nothing_callback() {
        // console.log("SUNCHI");
    }

    // ******************** DBConnection

    ping(on_ping_callback = null) {
        this.con.ping(on_ping_callback);
    }

    connect(a_callback = null) {
        this.con.connect(a_callback);
    }
    isConnected() { return this.con.isConnected(); }
    disconnect(a_callback = null) {
        this.con.disconnect(a_callback);
    }

    login(user,pwd,a_callback) {
        this.con.login(user,pwd,a_callback);
    }
    getDBEUserFromConnection() {
        return this.con._dbe_user;
    }
    getUserGroupsList() {
        return this.con.getUserGroupsList();
    }
    hasGroup(group_id) {
        const user = this.getDBEUserFromConnection()
        const groups = this.con.getUserGroupsList();
        return groups!==undefined && groups!==null
            && (group_id in groups || user.getValue('group_id'));
    }
    isAdmin() {
        const groups = this.getUserGroupsList();
        const ret = groups===null ? false
                : groups.indexOf(app_cfg.GROUP_ADMIN)>=0
        // console.log("BackEndProxy.isAdmin: groups="+groups)
        // console.log("BackEndProxy.isAdmin: ret="+ret)
        // console.log("BackEndProxy.isAdmin: GROUP_ADMIN="+app_cfg.GROUP_ADMIN)
        return ret
    }
    logout(a_callback) {
        this.con.logout(a_callback);
        this._user_profile = null;
    }

    canRead(obj) {
        if(obj===undefined || obj===null) return false;
        return this._canDo(obj,0,'r')
    }
    canWrite(obj) {
        if(obj===undefined || obj===null) return false;
        return this._canDo(obj,1,'w')
    }
    canExecute(obj) {
        if(obj===undefined || obj===null) return false;
        return this._canDo(obj,2,'x')
    }
    _canDo(obj,offset,perm) {
        // console.log("BackEndProxy._canDo: offset="+offset+" perm="+perm)
        const user = this.getDBEUserFromConnection()
        // console.log("BackEndProxy._canDo: user="+JSON.stringify(user))
        const values = 'dict' in obj ? obj['dict'] : obj
        // console.log("BackEndProxy._canDo: values="+JSON.stringify(values))
        const permissions = 'permissions' in values ? values['permissions'] : '---------'
        // console.log("BackEndProxy._canDo: permissions="+permissions)

        // Public
        // console.log("BackEndProxy._canDo: Public")
        if(permissions.charAt(6+offset)===perm) {
            return true
        }
        if(user===false) return false;
        // Group
        // console.log("BackEndProxy._canDo: Group "+permissions.charAt(3+offset))
        if(permissions.charAt(3+offset)===perm && this.hasGroup(values['group_id'])) {
            return true
        }
        // User
        // console.log("BackEndProxy._canDo: User "+permissions.charAt(0+offset))
        if(permissions.charAt(0+offset)===perm && user!==null && user.getValue('id')===values['owner']) {
            return true
        }
        // console.log("BackEndProxy._canDo: Can't do!!!")
        return false
    }


    getLoggedUser(a_callback) {
        // console.log("BackEndProxy.getLoggedUser: start.");
        this.con.getLoggedUser(a_callback);
        // console.log("BackEndProxy.getLoggedUser: end.");
    }

    fetchUserProfile(a_callback) {
        // console.log("BackEndProxy.fetchUserProfile: start.");
        const user = this.getDBEUserFromConnection();
        // console.log("BackEndProxy.fetchUserProfile: user="+JSON.stringify(user));
        if(user===null) {
            return
        }
        var search = new DBEntity("DBEPeople","people")
        search.setValue('fk_users_id',user.getValue('id'))
        var self = this
        this.search(search,false,true,'', (server_messages,dbelist) => {
            // console.log("BackEndProxy.fetchUserProfile.cb: start.");
            if(dbelist===null || dbelist.length!==1) {
                // console.log("BackEndProxy.fetchUserProfile: server_messages="+server_messages);
                return
            }
            self._user_profile = dbelist[0]
            const user_profile = dbelist[0]
            a_callback(user_profile)
            // console.log("BackEndProxy.fetchUserProfile.cb: end.");
        })
        // console.log("BackEndProxy.fetchUserProfile: end.");
    }

    execute(tablename,sql_string,a_callback) {
        this.con.execute(tablename,sql_string,a_callback);
    }

    // select(dbename,tablename,sql_string,a_callback) {
    //     this.con.Select(dbename,tablename,sql_string,a_callback);
    // }

    // search(dbe, uselike, caseSensitive, orderBy, a_callback) {
    //     this.con.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
    // }

    objectById(oid, ignore_deleted, a_callback) {
        this.con.objectById(oid, ignore_deleted, a_callback);
    }
    fullObjectById(oid, ignore_deleted, a_callback) {
        this.con.fullObjectById(oid, ignore_deleted, a_callback);
    }
    objectByName(name, ignore_deleted, a_callback) {
        this.con.objectByName(name, ignore_deleted, a_callback);
    }
    fullObjectByName(name, ignore_deleted, a_callback) {
        this.con.fullObjectByName(name, ignore_deleted, a_callback);
    }


    getDBEInstance(aclassname, a_callback) {
        if(aclassname===null || aclassname===undefined || aclassname.length===0) return;
        this.con.getDBEInstance(aclassname,a_callback);
    }
    getFormNameByDBEName(dbeName) {
        return this.dbe2formMapping[dbeName];
    }
    getDBE2FormMapping(a_callback) {
		var self = this
		var my_cb = (jsonObj, dbe2formMapping) => {
			console.log("BackEndProxy.getDBE2FormMapping.my_cb: start.");
			self.dbe2formMapping = dbe2formMapping;
			a_callback(jsonObj, dbe2formMapping);
			console.log("BackEndProxy.getDBE2FormMapping.my_cb: end.");
		}
        this.con.getDBE2FormMapping(my_cb.bind(self));
    }
    getAllFormClassnames(a_callback) {
        this.con.getAllFormClassnames(a_callback);
    }
    getFormInstance(aclassname,a_callback) {
        if(aclassname===null || aclassname===undefined || aclassname.length===0) return;
        this.con.getFormInstance(aclassname,a_callback);
    }
    getFormInstanceByDBEName(aclassname,a_callback) {
        if(aclassname===null || aclassname===undefined || aclassname.length===0) return;
        this.con.getFormInstanceByDBEName(aclassname,a_callback);
    }

    getRootObj(a_callback) {
        if(this.root_obj!==null) {
            a_callback(['',''], this.root_obj);
            return
        }
		var self = this
		var my_cb = (jsonObj, myobj) => {
			console.log("BackEndProxy.getRootObj.my_cb: start.");
			self.root_obj = myobj;
			a_callback(jsonObj, myobj);
			console.log("BackEndProxy.getRootObj.my_cb: end.");
		}
        this.con.getRootObj(my_cb);
        // this.con.getRootObj(a_callback);
    }
    getChilds(dbe, without_index_page, a_callback) {
        this.con.getChilds(dbe, without_index_page, a_callback)
    }

    // ******************** DBConnection

    // getLoggedUser() {
    //     return this.dbmgr.getLoggedUser(this.do_nothing_callback);
    // }

    select(dbename,tablename,sql_string,a_callback) {
        this.dbmgr.Select(dbename,tablename,sql_string,a_callback);
    }

    search(dbe, uselike, caseSensitive, orderBy, a_callback) {
        // this.con.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
        this.dbmgr.Search(dbe, uselike, caseSensitive, orderBy, a_callback);
    }

}

/**
 * To cache immutable results 
 * for functions like: f(key,a_cb)
 */
function simpleCache(cb) {
    const cache = new Map()
    return (...args) => {
        console.log("simpleCache: start.")
        const key = args[0]
        const a_cb = args[1]
        console.log("simpleCache: key="+key)
        // IF chache has key, then apply then callback we have as second parameter
        if(cache.has(key)) {
            const jsonValue = cache.get(key)
            // console.log("simpleCache: FOUND typeof(jsonValue)="+typeof(JSON.parse(jsonValue)))
            console.log("simpleCache: FOUND jsonValue="+jsonValue)
            a_cb(['',[]], JSON.parse(jsonValue))
        } else {
        // ELSE call the function cb, aith my_cb as a callback
            var my_cb = (jsonObj, myobj) => {
                console.log("simpleCache.my_cb: start.")
                // console.log("simpleCache.my_cb: typeof(myobj)="+typeof(myobj))
                // Save in cache
                const jsonValue = JSON.stringify(myobj)
                console.log("simpleCache.my_cb: jsonValue="+jsonValue)
                cache.set(key, jsonValue)
                // Apply the callback in argument
                a_cb(jsonObj, myobj)
                console.log("simpleCache.my_cb: end.")
            }
            cb(key, my_cb)
        }
        console.log("simpleCache: end.")
    }
}

// function simpleCache(cb) {
//     const cache = new Map()
//     const requested = [];
//     return (...args) => {
//         console.log("simpleCache: start.")
//         const key = args[0]
//         const a_cb = args[1]
//         console.log("simpleCache: key="+key)
//         // IF chache has key, then apply then callback we have as second parameter
//         if(cache.has(key)) {
//             const jsonValue = cache.get(key)
//             // console.log("simpleCache: FOUND typeof(jsonValue)="+typeof(JSON.parse(jsonValue)))
//             console.log("simpleCache: FOUND jsonValue="+jsonValue)
//             a_cb(['',[]], JSON.parse(jsonValue))
//         } else if(requested.indexOf(key)>=0) {
//             setTimeout(() => {
//                 const jsonValue = cache.get(key)
//                 // console.log("simpleCache: FOUND typeof(jsonValue)="+typeof(JSON.parse(jsonValue)))
//                 console.log("simpleCache: FOUND jsonValue="+jsonValue)
//                 a_cb(['',[]], JSON.parse(jsonValue))
//             }, 400)
//         } else {
//         // ELSE call the function cb, aith my_cb as a callback
//             requested.push(key);
//             var my_cb = (jsonObj, myobj) => {
//                 console.log("simpleCache.my_cb: start.")
//                 // console.log("simpleCache.my_cb: typeof(myobj)="+typeof(myobj))
//                 // Save in cache
//                 const jsonValue = JSON.stringify(myobj)
//                 console.log("simpleCache.my_cb: jsonValue="+jsonValue)
//                 if(jsonValue===null) {
//                     requested.splice(requested.indexOf(key),1)
//                     return
//                 }
//                 cache.set(key, jsonValue)
//                 // Apply the callback in argument
//                 a_cb(jsonObj, myobj)
//                 console.log("simpleCache.my_cb: end.")
//             }
//             // setTimeout(() => {
//                 cb(key, my_cb)
//             // }, 200);
//         }
//         console.log("simpleCache: end.")
//     }
// }

export { BackEndProxy };
