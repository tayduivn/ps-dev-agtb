({
    events : { 'click .iphone-toggle-buttons span' : 'toggle' },

    toggle : function(event) {
       var include = this.model.get('forecast') === true ? false : true;
       this.model.set('forecast', include);
       this.model.set('id', this.model.get('id'));
       this.model.url = 'rest/v10/modules/Opportunities';
       this.model.save();
       this.view.context.set('selectedToggle', {'include' : include, 'amount' : this.model.get('amount') });
    }
})
