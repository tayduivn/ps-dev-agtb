({
    events: {
        "click .imagesearch-widget-choice": "selectImage",
        "click .imagesearch-widget img": "openImageModal"
    },

    profile: "../styleguide/assets/img/profile.png",

    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
    },

    _render: function() {
        if (!this.model) {
            return;
        }

        this.profile = this.model.get("img") || ((this.model.has("picture")) ? app.api.buildFileURL({
            module: 'Users',
            id: this.model.id,
            field: 'picture'
        }) : false) || this.profile;
        app.view.Field.prototype._render.call(this);
    },

    getImages: function(callback) {
        var user = "kdao@sugarcrm.com",
            name = this.model.get("name") || this.model.get("first_name") + " " + this.model.get("last_name") || this.model.get("full_name"),
            pwd = "+CTtuUW+uJeXKskFMauJguo7bcagh5RvculJnKu9kuA=";
        $.support.cors = true;

        $.ajax({
            type: "GET",
            beforeSend: function(xhr) {
                var base64 = base64_encode(user + ":" + pwd);
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
                if(_.isFunction(callback)){
                    callback.call(this);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown.message);
            },
            context: this
        });
    },

    selectImage: function(e) {
        var target = this.$(e.target),
            self = this;

        this.model.set({img: target.attr("src")});
        this.model.save();

        e.stopPropagation();
    },
    openImageModal: function(e){
        this.getImages(function(){
            this.$("#imagesearch").modal('show');
        });
        e.stopPropagation();
    }
})