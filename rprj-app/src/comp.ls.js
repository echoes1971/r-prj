
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
}

export { RLocalStorage };
