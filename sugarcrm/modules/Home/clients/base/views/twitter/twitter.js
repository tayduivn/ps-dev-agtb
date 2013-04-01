({
    plugins: ['Dashlet', 'timeago'],
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        if (this.model.parentModel && this.model.get("requiredModel")) {
            this.model.parentModel.on("change", this.loadData, this);
        }
    },
    _render: function () {
        if (this.tweets || this.viewName === 'config') {
            app.view.View.prototype._render.call(this);
        }
    },

    initDashlet: function (view) {
        this.viewName = view;
        if (view === 'config' && this.model.get("requiredModel")) {
            _.each(this.meta.config.fields, function (field, index) {
                if (field.name === 'twitter') {
                    this.meta.config.fields.splice(index, 1);
                }
            }, this);
        }
    },
    loadData: function (options) {

        if (this.disposed) {
            return;
        }

        var twitter = this.model.get('twitter') ||
                this.model.parentModel.get('name') ||
                this.model.parentModel.get('account_name') ||
                this.model.parentModel.get('full_name'),
            limit = parseInt(this.model.get("limit") || 20, 10),
            self = this;

        this.screen_name = this.model.get('twitter') || false;
        if (!twitter || this.viewName === 'config') {
            return false;
        }

        twitter = twitter.replace(" ", "");
        this.twitter = twitter;
        $.ajax({
            url: "https://api.twitter.com/1/statuses/user_timeline.json?screen_name=" + twitter + "&include_rts=true&count=" + limit + "&callback=?",
            dataType: "jsonp",
            context: this,
            success: function (data) {
                if (this.disposed) {
                    return;
                }
                var tweets = [];

                _.each(data, function (tweet) {
                    var time = new Date(tweet.created_at.replace(/^\w+ (\w+) (\d+) ([\d:]+) \+0000 (\d+)$/,
                            "$1 $2 $4 $3 UTC")),
                        date = app.date.format(time, "Y-m-d H:i:s"),
                        text = tweet.text,
                        sourceUrl = tweet.source,
                        id = tweet.id_str,
                        name = tweet.user.name,
                        tokenText = text.split(' '),
                        screen_name = tweet.user.screen_name,
                        profile_image_url = tweet.user.profile_image_url_https,
                        j;

                    // Search for links and turn them into hrefs
                    for (j = 0; j < tokenText.length; j++) {
                        if (tokenText[j].charAt(0) == 'h' && tokenText[j].charAt(1) == 't') {
                            tokenText[j] = "<a class='googledoc-fancybox' href=" + '"' + tokenText[j] + '"' + "target='_blank'>" + tokenText[j] + "</a>";
                        }
                    }

                    text = tokenText.join(' ');
                    tweets.push({id: id, name: name, screen_name: screen_name, profile_image_url: profile_image_url, text: text, source: sourceUrl, date: date});
                }, this);

                this.tweets = tweets;
                self.render();
            },
            complete: (options) ? options.complete : null
        });
    },
    _dispose: function () {
        this.model.parentModel.off("change", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
