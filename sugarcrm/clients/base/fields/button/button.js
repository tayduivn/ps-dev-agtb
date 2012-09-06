({
    _render:function(){
        // buttons use the value property in metadata to denote their action for acls
        if (app.acl.hasAccessToModel(this.def.value, this.model, this)) {
            app.view.Field.prototype._render.call(this);
        }
    },
    _renderHtml: function() {
        var result = app.view.Field.prototype._renderHtml.call(this);
        if(this.def.events) {
            var self = this;
            _.each(this.def.events, function(function_code, evtType) {
                try{
                    eval('var _function = ' + function_code);
                } catch(e) {
                    app.logger.error("Failed to eval custom onclick event function.\n" + e);
                    // TODO: Trigger app:error event
                }
                if(_.isFunction(_function)) {
                    self.$('.btn').off(evtType).on(evtType, function(evt){
                        _function.call(self, evt);
                    });
                }
            });
        }

        return result;
    }
})
