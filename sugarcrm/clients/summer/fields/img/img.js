({
    events: {
        "click .imagesearch-widget-choice": "selectImage"
    },

    profile: "../clients/summer/fields/img/profile.png",

    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        this.getImages();
    },

    render: function() {
        this.profile = this.model.get("img") || this.profile;
        app.view.Field.prototype.render.call(this);
    },

    getImages: function() {
        var user = "kdao@sugarcrm.com",
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

                this.render();
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

    selectImage: function(e) {
        var target = this.$(e.target),
            self = this;

        this.model.set({img: target.attr("src")});
        this.model.save();

        e.stopPropagation();
    }
})