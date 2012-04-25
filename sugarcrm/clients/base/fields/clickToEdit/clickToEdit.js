({
    render:function() {
        this.app.view.Field.prototype.render.call(this);
        var self = this;
        var ctefield = this.$el.find('.' + this.cteclass);
        ctefield.editable(function(value, settings) {
                return value;
            }
        );
        return this;
    },

    bindDomChange: function(model, fieldName) {
        var thing = this.$el.find('.' + this.cteclass);
        thing.on('change', function(ev) {
            console.log("***** start onDomChange callback *****");
            console.log(ev.target.value);
            model.set(fieldName, ev.target.value);
            console.log("***** end onDomChange callback *****");
        });
    }
})