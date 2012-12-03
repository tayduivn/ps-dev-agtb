({
    events: {
        'click input.selection': 'toggleSelect'
    },
    toggleSelect: function(evt) {
        var $el = $(evt.currentTarget).is(":checked");
        if($el) {
            this.check();
        } else {
            this.uncheck();
        }
    },
    check: function() {
        if(this.model) {
            this.context.set('selection_model', this.model);
        }
    },
    uncheck: function() {
        if(this.model) {
            this.context.unset('selection_model');
        }
    },
    bindDomChange: function() {
        //don't update the row's model & re-render, this is just a mechanism for selecting a row
    }
})