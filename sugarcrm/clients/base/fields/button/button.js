({
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        if(this.def.events) {
            var self = this,
                _customEvents = {};

            _.each(this.def.events, function(function_code, evtType) {
                _customEvents[evtType + " .btn"] =  function_code;
            });
            this.events = _.extend(this.events || {}, _customEvents);
        }
    },
    _render:function(){
        // buttons use the value property in metadata to denote their action for acls
        if (app.acl.hasAccessToModel(this.def.value, this.model, this)) {
            app.view.Field.prototype._render.call(this);
        }
    }
})
