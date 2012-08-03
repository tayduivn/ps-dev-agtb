({
    _render:function(){
        // buttons use the value property in metadata to denote their action for acls
        if (app.acl.hasAccessToModel(this.def.value, this.model, this)) {
            app.view.Field.prototype._render.call(this);
        }
    }
})
