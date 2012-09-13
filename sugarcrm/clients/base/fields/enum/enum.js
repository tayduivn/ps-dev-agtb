({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);
        this.$(this.fieldTag).chosen({disable_search_threshold: 5})
        this.$(".chzn-container").addClass("tleft");
        return this;
    },
    unformat:function(value) {
        return value;
    },
    format:function(value) {
        var newval = '', optionsObject, optionLabels;

        if(this.def.isMultiSelect && this.view.name !== 'edit') {

            // Gets the dropdown options e.g. {foo:foolbl, bar:barlbl ...}
            optionsObject = app.lang.getAppListStrings(this.def.options);

            // value are selected option keys .. grab corresponding labels
            _.each(value, function(p) {
                if(_.has(optionsObject, p)) {
                    newval += optionsObject[p]+', ';
                }
            });
            newval = newval.slice(0, newval.length - 2); // strips extra ', '
        } else {
            // Normal dropdown, just get selected
            newval = this.model.get(this.name);
        }
        return newval;
    }
})
