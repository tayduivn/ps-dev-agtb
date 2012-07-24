({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getFacebook: function () {
        var self = this;
        this.facebookAccount = this.model.get("facebook");
        app.view.View.prototype._renderHtml.call(self);
    },

    bindDataChange: function () {
        var self = this;
        this.model.on( "change", self.getFacebook, this);
    }

})