({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getData: function () {
        var self = this;
        var api_key = "AIzaSyCIXeFNDztbmOPSX9jA1eRAzGzDmCmM9Ig";  // kdao api_key
        //var googlePlus_id = "100975726761624703436";   // sugarcrm id
        //var googlePlus_id = this.model.get('google_plus');
        var name = self.model.get('name');

        // search for google id account
        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people?query=" + name + "&key=" + api_key,
            dataType: "json",
            success: function ( data ) {
                if (data.items) {
                    self.gPlusId = data.items[0].id;      // get the first result
                    console.log( 'success get google plus id' );
                }
            },
            error: function () {
                console.log ( 'failed get google plus id' );
            },
            context: this,
            async: false
        });

        if (!self.gPlusId) return;

        // search based on id
        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people/" + self.gPlusId + "?key=" + api_key,
            dataType: "json",
            success: function ( data ) {
                self.gPlusAbout = data.aboutMe;
                self.gPlusImageUrl = data.image.url;
                self.gPlusUrl = data.url;
                self.gPlusDisplayName = data.displayName;
                self._render();
            },
            error: function () {
                console.log ( 'failed' );
            },
            context: this
        });

        $.ajax ({
            url : "https://www.googleapis.com/plus/v1/people/" + self.gPlusId + "/activities/public?key=" + api_key + "&maxResults=5",
            dataType: "json",
            success: function ( data ) {
                self.gPlusPosts = [];
                var date = "";
                for (var i=0; i < data.items.length; i++ ) {
                    date ="";
                    date+=data.items[i].published.substring(5,7);
                    if (date == '01'){
                        date = 'January';
                    }
                    if (date == '02'){
                        date = 'February';
                    }
                    if (date == '03'){
                        date = 'March';
                    }
                    if (date == '04'){
                        date = 'April';
                    }
                    if (date == '05'){
                        date = 'May';
                    }
                    if (date == '06'){
                        date = 'June';
                    }
                    if (date == '07'){
                        date = 'July';
                    }
                    if (date == '08'){
                        date = 'August';
                    }
                    if (date == '09'){
                        date = 'September';
                    }
                    if (date == '10'){
                        date = 'October';
                    }
                    if (date == '11'){
                        date = 'November';
                    }
                    if (date == '12'){
                        date = 'December';
                    }

                    date += " ";
                    date += data.items[i].published.substring(8,10);
                    date += ", ";
                    date += data.items[i].published.substring(0,4);
                    date += " - ";



                    self.gPlusPosts.push ( {
                        image: self.gPlusImageUrl,
                        content: data.items[i].object.content,
                        author: data.items[i].object.displayName,
                        attachments: data.items[i].object.attachments,
                        published: date,
                        url: data.items[i].url
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