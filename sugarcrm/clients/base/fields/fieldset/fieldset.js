({
    fields: null,
    getPlaceholder : function(){
        var ret = "";
        var self = this;
        if (!this.fields){
            this.fields = [];
            _.each(this.def.fields, function(fieldDef){
                var field = app.view.createField({
                    def: fieldDef,
                    view: self.view,
                    model: self.model
                });
                self.fields.push(field);
                ret += field.getPlaceholder();
            });
        }
        return new Handlebars.SafeString(ret);
    }
})