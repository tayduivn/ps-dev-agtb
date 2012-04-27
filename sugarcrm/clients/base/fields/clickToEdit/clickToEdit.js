({
    events: {
        'mouseenter': 'showActions',
        'mouseleave': 'hideActions'
    },

    ctefield: {},
    cteicon: {},

    render:function() {
        this.app.view.Field.prototype.render.call(this);
        var self = this;
        this.ctefield = this.$el.find('.cte' + this.cteclass);
        this.ctefield.editable(function(value, settings) {
                self.model.set(self.name,value);
                self.model.save(self.name,value);
                return value;
            },{
                select:true
            }
        );

        this.cteicon = this.ctefield.parent().find('.cteimage');
        _.each(this.events, function(act, event){
            this.ctefield.on(event, this.act);
        }, this);
        return this;
    },
    
    /***
     * Overwriting default bindDomChange function to prevent default behavior
     *
     * @param model
     * @param fieldName
     */
    bindDomChange: function(model, fieldName) {},

    showActions: function (e) {
        this.cteicon.show();
    },

    hideActions: function(e) {
        this.cteicon.hide();
    },

})