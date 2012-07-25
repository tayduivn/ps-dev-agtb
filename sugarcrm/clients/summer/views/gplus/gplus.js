({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getAbout: function ( api_key, googlePlus_id ) {
        var self = this;
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
            }
        });

    },

    getActivities: function ( api_key, googlePlus_id ) {
        var self = this;
        this.gPlustPosts = [];
        var gPlusPosts = this.gPlustPosts;
        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people/" + googlePlus_id + "/activities/public?key=" + api_key + "&maxResults=5",
            dataType: "json",
            success: function ( data ) {
                for (var i=0; i < data.items.length; i++ ) {
                    gPlusPosts.push ( {
                        content: data.items[i].title,
                        author: data.items[i].object.displayName,
                        attachments: data.items[i].object.attachments
                    });
                }
            },
            error: function () {
                console.log ( 'failed' );
            }
        });
    },

    getData: function () {
        var self = this;
        var api_key = "AIzaSyCIXeFNDztbmOPSX9jA1eRAzGzDmCmM9Ig"; // kdao api_key
        var googlePlus_id = "100975726761624703436";
        self.gPlusAbout = self.getAbout( api_key, googlePlus_id );
        self.gPlusActivities = self.getActivities( api_key, googlePlus_id );
        app.view.View.prototype._renderHtml( self );
    },

    bindDataChange: function () {
        var self = this;
        this.model.on( "change", self.getData, this );
    }

})