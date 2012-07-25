
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },

    getTweets: function () {
        var self = this;
        var twitter = 'sugarcrm';
        $.ajax({
            url: "http://twitter.com/statuses/user_timeline/" + twitter + ".json?count=5&callback=?",
            dataType: "jsonp",
            success: function (data) {
                self.tweets = data;
                app.view.View.prototype._renderHtml.call(self);
            },
            context: this
        });

    },

    bindDataChange: function () {
        var self = this;
        this.model.on('change', self.getTweets, this);
    }

})