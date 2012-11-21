({
    render: function() {
        if (this.tweets) {
            app.view.View.prototype.render.call(this);
        }
    },

    getTweets: function() {
        var twitter = this.model.get('twitter') ||
            this.model.get('name') ||
            this.model.get('account_name') ||
            this.model.get('full_name');

        if (!twitter) {
            return false;
        }

        twitter = twitter.replace(" ", "");

        $.ajax({
            url: "https://api.twitter.com/1/statuses/user_timeline.json?screen_name=" + twitter + "&count=3&callback=?",
            dataType: "jsonp",
            context: this,
            success: function(data) {
                var tweets = [];

                _.each(data, function(tweet) {
                    var time = new Date(tweet.created_at.replace(/^\w+ (\w+) (\d+) ([\d:]+) \+0000 (\d+)$/, "$1 $2 $4 $3 UTC")),
                        date = app.date.format(time, "Y-m-d H:i:s"),
                        text = tweet.text,
                        sourceUrl = tweet.source,
                        tokenText = text.split(' '),
                        j;

                    // Search for links and turn them into hrefs
                    for (j = 0; j < tokenText.length; j++) {
                        if (tokenText[j].charAt(0) == 'h' && tokenText[j].charAt(1) == 't') {
                            tokenText[j] = "<a href=" + '"' + tokenText[j] + '"' + "target='_blank'>" + tokenText[j] + "</a>";
                        }
                    }

                    text = tokenText.join(" ");
                    tweets.push({text: text, source: sourceUrl, date: date});
                }, this);

                this.tweets = tweets;
                this.render();
            }
        });
    },

    bindDataChange: function() {
        this.model.on('change', this.getTweets, this);
    }
})
