({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getData: function () {
        var self = this;
        var api_key = "AIzaSyCIXeFNDztbmOPSX9jA1eRAzGzDmCmM9Ig";  // kdao api_key
        //var googlePlus_id = "100975726761624703436";   // sugarcrm id
        var googlePlus_id = this.model.get('google_plus');
        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people/" + googlePlus_id + "?key=" + api_key,
            dataType: "json",
            success: function ( data ) {
                self.gPlusAbout = data.aboutMe;
                self.gPlusImageUrl = data.image.url;
                self.gPlusUrl = data.url;
            },
            error: function () {
                console.log ( 'failed' );
            },
            context: this
        });

        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people/" + googlePlus_id + "/activities/public?key=" + api_key + "&maxResults=5",
            dataType: "json",
            success: function ( data ) {
                self.gPlusPosts = [];
                for (var i=0; i < data.items.length; i++ ) {
                    self.gPlusPosts.push ( {
                        content: data.items[i].title,
                        author: data.items[i].object.displayName,
                        attachments: data.items[i].object.attachments
                    });
                }
                self.render();
            },
            error: function () {
                console.log ( 'failed' );
            },
            context: this
        });
    },

    bindDataChange: function () {
        var self = this;
        this.model.on( "change", self.getData, this );
    }

})