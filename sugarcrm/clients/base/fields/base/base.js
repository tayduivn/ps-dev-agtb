({
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        if(this.def.events) {
            var self = this,
                _customEvents = {};

            _.each(this.def.events, function(function_code, evtType) {
                _customEvents[evtType + " a[href]"] =  function_code;
            });
            this.events = _.extend(this.events || {}, _customEvents);
        }
    },
    render: function() {
        var action = "view"
        if (this.def.link && this.def.route) {
            action = this.def.route.action;
        }
        if (!app.acl.hasAccessToModel(action, this.model)) {
            this.def.link = false;
        };
        app.view.Field.prototype.render.call(this);
    }
})