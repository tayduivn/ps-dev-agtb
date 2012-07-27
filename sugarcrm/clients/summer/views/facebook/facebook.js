({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getFacebook: function () {
        var self = this;
        var name = self.model.get('name');
        var facebookId = "";
        $.ajax ({
            url: "https://graph.facebook.com/search?q=" + name + "&type=page",
            dataType: "json",
            success: function ( data ) {
                if (data.data) {
                    facebookId = data.data[0].id;   // get the first name in search result
                }
                console.log( facebookId );
                $.ajax ({
                    url: "https://graph.facebook.com/" + facebookId,
                    dataType: "json",
                    success: function ( data ) {
                        self.facebookUrl = data.link;
                        console.log ( self.facebookUrl );
                        self._render();
                    },
                    error: function () {
                        console.log ('failed facebook widget');
                    }
                });
            },
            error: function () {
                console.log ('failed facebook id');
            }
        });


    },

    bindDataChange: function () {
        var self = this;
        this.model.on( "change", self.getFacebook, this);
    }

})