({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    render: function() {
        if (!this.facebookId) {
            return;
        }

        app.view.View.prototype.render.call(this);
    },

    getFacebook: function() {
        var self = this;
        var name = this.model.get('name');
        var facebookId;

        $.ajax({
            url: "https://graph.facebook.com/search?q=" + name + "&type=page",
            dataType: "json",
            context: this,
            success: function(data) {
                if (data.data && data.length > 0) {
                    facebookId = data.data[0].id;   // get the first name in search result
                    self.facebookId = facebookId;

                    $.ajax({
                        url: "https://graph.facebook.com/" + facebookId,
                        dataType: "json",
                        success: function(data) {
                            self.facebookUrl = data.link;
                            console.log(self.facebookUrl);
                            this.render();
                        },
                        error: function() {
                            console.log('failed facebook widget');
                        }
                    });
                } else {
                    return;
                }
            },
            error: function() {
                console.log('failed facebook id');
            }
        });
    },

    bindDataChange: function() {
        this.model.on("change", this.getFacebook, this);
    }

})