({
    events : { 'click .iphone-toggle-buttons span' : 'toggle' },

    toggle : function(event) {
       var include = (this.model.get('forecast') === true) ? false : true;
       this.model.set('forecast', include);
       this.view.context.set('selectedToggle', { 'model' : this.model });
    }
})
