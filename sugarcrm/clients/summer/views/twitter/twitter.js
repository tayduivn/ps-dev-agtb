({
    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
    },

    render: function() {
        if (this.tweets) {
            app.view.View.prototype.render.call(this);
        }
    },

    getTweets: function() {
        var self = this;
        var twitter = this.model.get('twitter') ||
            this.model.get('name').replace(" ", "") ||
            this.model.get('account_name').replace(" ", "") ||
            this.model.get('full_name').replace(" ", "");

        $.ajax({
            url: "http://twitter.com/statuses/user_timeline/" + twitter + ".json?count=6&callback=?",
            dataType: "jsonp",
            context: this,
            success: function(data) {
                var tweets = [];

                _.each(data, function(tweet) {
                    var day = tweet.created_at.substring(8, 10),
                        month = tweet.created_at.substring(4, 7),
                        year = tweet.created_at.substring(tweet.created_at.length - 4, tweet.created_at.length),
                        text = tweet.text,
                        sourceUrl = tweet.source,
                        tokenText = text.split(' '),
                        j;

                    // Search for links and turn them into hrefs
                    for (j = 0; j < tokenText.length; j++) {
                        if (tokenText[j].charAt(0) == 'h' && tokenText[j].charAt(1) == 't') {
                            tokenText[j] = "<a class='googledoc-fancybox' href=" + '"' + tokenText[j] + '"' + "target='_blank'>" + tokenText[j] + "</a>"
                        }
                    }

                    text = tokenText.join(" ");
                    tweets.push({text: text, source: sourceUrl, day: day, month: month, year: year});
                }, this);

                this.tweets = tweets;

                app.view.View.prototype.render.call(this);
            },
            context: this
        });
    },

    bindDataChange: function() {
        var self = this;
        this.model.on('change', self.getTweets, this);
    }
})