({
    fieldTag: 'input',
    bindDomChange: function() {
        if (!(this.model instanceof Backbone.Model)) return;
        var self = this;
        var el = this.$el.find(this.fieldTag);
        el.on("change", function() {
            self.model.set(self.name, self.unformat(self.$(self.fieldTag+":radio:checked").val()));
        });
    }
})