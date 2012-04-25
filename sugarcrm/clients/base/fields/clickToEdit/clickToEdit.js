({
    render:function() {

        this.app.view.Field.prototype.render.call(this);
        var self = this;
        var ctefield = this.$el.find('.' + this.cteclass);

        ctefield.editable(function(value, settings) {
                self.model.set( self.name , value );
                return value;
            }
        );
        return this;
    }
})