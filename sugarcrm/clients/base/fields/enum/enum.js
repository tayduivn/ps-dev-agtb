({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);
        this.$(this.fieldTag).chosen();
        this.$(".chzn-container").addClass("tleft");
        return this;
    },
    unformat:function(value) {
        return value;
    },
    format:function(value) {
        var newval = '';
        if(this.def.isMultiSelect && this.view.name === 'detail') {
            _.each(value, function(element) {
                newval += element + ', ';
            });
            newval = newval.slice(0, newval.length - 2); // strips extra ', '
        } else {
            newval = this.model.get(this.name);
        }
        return newval;
    }
})
