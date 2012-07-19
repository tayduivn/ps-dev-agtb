({
    events : { 'click .iphone-toggle-buttons span' : 'toggle' },

    toggle : function(event) {
        if(this.isMyWorksheet()) {
           var include = (this.model.get('forecast') === true) ? false : true;
           this.model.set('forecast', include);
           this.view.context.set('selectedToggle', { 'model' : this.model });
        }
    },

    _render: function(value) {
        app.view.Field.prototype._render.call(this);
        if(!this.isMyWorksheet())
        {
            // disable the checkboxes
            var checkboxes = this.$el.find('input[type=checkbox]');
            _.each(checkboxes, function (value, key) {
               value.disabled = true;
            });

            // add the disabled value to the label so we can trigger the cursor to show
            // the default cursor instead of the pointer cursor
            var toggles = this.$el.find('div.iphone-toggle-buttons label');
            _.each(toggles, function (value, key) {
                $(value).addClass('disabled');
           });
        }
    }
})
