({
    /**
     * Combine widget: by Justin Park(jpark@sugarcrm.com)
     * type => 'combine',
     *
     * combine multiple fields into one field set.
     * Use format parameter in order to customize its display message.
     * i.e. format => array('%0%1%2 %3:%4', year_part, month_part, day_part, hour_part, minute_part),
     *
     * All combine fields are defined in the related_fields
     * i.e. related_fields => array(year_part, month_part, day_part, ...),
     */
    getData: function() {
        if(this.def.format) {
            var _format = this.def.format.splice(0, 1),
                _values = {},
                _args = this.def.format,
                _module = this.module;

            _values[this.name] = this.model.get(this.name);
            for(var key in this.def.related_fields) {
                _values[this.def.related_fields[key]] = this.model.get(this.def.related_fields[key]);
            }

            this.value = _format[0].replace(/%(\d+)/g, function(match, index) {
                if(_args[index] instanceof Object)
                    return app.lang.get(_args[index].label, _module);

                return typeof _values[_args[index]] != "undefined" ? _values[_args[index]] : "";
            });
            //push back to its original context
            this.def.format.splice(0, 0, _format[0]);
            this.$el.html(this.template(this));
        } else {
            this.$el.remove();
        }
    },
    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }
})