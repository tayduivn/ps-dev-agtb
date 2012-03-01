handler : {
    get:function(field){ console.log('get' + field); return this.prototype.get(field); }
    set:function(field, value){console.log('set' + field); return this.prototype.set(field, value);}
}