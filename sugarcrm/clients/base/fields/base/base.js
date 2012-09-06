({
    render: function() {
        var action = "view";
        if (this.def.link && this.def.route) {
            action = this.def.route.action;
        }
        if (!app.acl.hasAccessToModel(action, this.model)) {
            this.def.link = false;
        };
        app.view.Field.prototype.render.call(this);
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
                    self.$('a[href]').off(evtType).on(evtType, function(evt){
                        _function.call(self, evt);
                    });
                }
            });
        }

        return result;
    }
})