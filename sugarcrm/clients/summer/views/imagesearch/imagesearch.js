({
    initialize: function ( options ) {
        app.view.View.prototype.initialize.call( this, options );
    },

    getImages: function () {
        var self = this;
        var user = "kdao@sugarcrm.com";
        var pwd = "+CTtuUW+uJeXKskFMauJguo7bcagh5RvculJnKu9kuA=";
        $.support.cors = true;
        $.ajax ({
            type: "GET",
            beforeSend: function ( xhr ) {
                var bytes = Crypto.charenc.Binary.stringToBytes( user + ":" + pwd );
                var base64 = Crypto.util.bytesToBase64( bytes );
                xhr.setRequestHeader("Authorization", "Basic " + base64);
            },
            url: "https://api.datamarket.azure.com/Data.ashx/Bing/Search/v1/Image?Query=%27prague%27&$top=12&$format=json",
            dataType: "json",
            success: function ( data ) { // this == success function
                self.pictures = [];
                var pictures = self.pictures;
                for (var i=0; i < data.d.results.length; i++) {
                    var mediaUrl = data.d.results[i].MediaUrl;
                    var sourceUrl = data.d.results[i].SourceUrl;
                    console.log(sourceUrl);
                    pictures.push( {mediaUrl: mediaUrl, sourceUrl: sourceUrl} );
                }
                app.view.View.prototype._renderHtml.call( self );
            },
            error: function ( jqXHR, textStatus, errorThrown ) {
                console.log( errorThrown.message );
            },
            context: this

        });
    },

    bindDataChange: function () {
        var self = this;
        this.model.on( "change", self.getImages, this );
    }

})