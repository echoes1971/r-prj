
class RLocalStorage {
    constructor(myid) {
        this.myid = myid;
    }

    cleanAll() {
        localStorage.clear();
    }

    getValue(k, default_value=null) {
        const k1 = this.myid + '.' + k;
        const tmp = localStorage.getItem(k1);

        return tmp===undefined ? default_value : JSON.parse(tmp);
    }

    setValue(k,v) {
        const k1 = this.myid + '.' + k;
        localStorage.setItem(k1,JSON.stringify(v));
    }

    /*
        Returns all keys for this.myid
    */
    getAllMyKeys() {
        var ret = [];
        const prefix = this.myid + '.';
        for(var i=0; i<localStorage.length; i++) {
            if(localStorage.key(i).indexOf(prefix)<0) continue;
            const k1 = localStorage.key(i).replace(prefix,'');
            ret.push(k1);
        }
        return ret;
    }
    /** Returns the state for myid */
    getMyState() {
        var ret = {};
        const prefix = this.myid + '.';
        for(var i=0; i<localStorage.length; i++) {
            if(localStorage.key(i).indexOf(prefix)<0) continue;
            const k = localStorage.key(i);
            const tmp = localStorage.getItem(k);
            if(tmp===undefined || tmp===null) continue;

            const k1 = k.replace(prefix,'');
            const v = JSON.parse(tmp);
            ret[k1]=v;
        }
        return ret;
    }
    saveMyState(state) {
        for(const k in state) {
            console.log("RLocalStorage.saveMyState: k="+k)
            // this.setValue(k,state[k]);
        }
    }

    toString() {
        var ret = "";
        const prefix = this.myid + '.';
        for(var i=0; i<localStorage.length; i++) {
            if(localStorage.key(i).indexOf(prefix)<0) continue;
            const k = localStorage.key(i);
            const tmp = localStorage.getItem(k);
            if(tmp===undefined || tmp===null) continue;

            const k1 = k.replace(prefix,'');
            // const v = JSON.parse(tmp);
            ret += k1+"="+tmp+"\n";
        }
        return ret;
    }
}

export { RLocalStorage };


