({
    events : { 'click .iphone-toggle-buttons span' : 'toggle' },

    toggle : function(event) {
       var include = (this.model.get('forecast') === true) ? false : true;
       this.model.set('forecast', include);
       this.view.context.set('selectedToggle', { 'model' : this.model });
    },

    _render: function(value) {
        app.view.Field.prototype._render.call(this);
        //If the is_owner attribute is available and it is false, apply the check
        if(!this.model.get('is_owner'))
        {
           var checkboxes = this.$el.find('input[type=checkbox]');
           _.each(checkboxes, function (value, key) {
                  value.disabled = true;
           });
        }
    }
})
