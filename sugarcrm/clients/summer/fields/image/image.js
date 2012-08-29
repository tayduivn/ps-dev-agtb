({
    events: {
        "click .imagesearch-widget-choice": "saveModel"
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    getImages: function() {
        var self = this,
            user = "kdao@sugarcrm.com",
            name = this.model.get("name") || this.model.get("first_name") + " " + this.model.get("last_name") || this.model.get("full_name"),
            pwd = "+CTtuUW+uJeXKskFMauJguo7bcagh5RvculJnKu9kuA=";

        $.support.cors = true;
        $.ajax({
            type: "GET",
            beforeSend: function(xhr) {
                var base64 = Crypto.util.bytesToBase64(Crypto.charenc.Binary.stringToBytes(user + ":" + pwd));
                xhr.setRequestHeader("Authorization", "Basic " + base64);
            },

            url: "https://api.datamarket.azure.com/Data.ashx/Bing/Search/v1/Image?Query=%27" + name + "%27&$top=15&$format=json",
            dataType: "json",
            success: function(data) {
                this.pictures = [];

                _.each(data.d.results, function(result) {
                    this.pictures.push({mediaUrl: result.MediaUrl, sourceUrl: result.SourceUrl})
                }, this);

                if (!self.profile) {
                    self.profile = "../clients/summer/views/imagesearch/anonymous.jpg";
                }
                app.view.View.prototype._renderHtml.call(self);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown.message);
            },
            context: this
        });
    },

    bindDataChange: function() {
        this.model.on("change", this.getImages, this);
    },

    saveModel: function(event) {
        var self = this;
        var chosenImageUrl = ( this.$(event.target)[0].getAttribute('src') );
        self.profile = chosenImageUrl;
        self.render();
        self.model.save();
    }

})