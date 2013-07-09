({
    plugins: ['Dashlet', 'timeago'],
    events: {
        'mouseover .news-article': 'onTweetOver',
        'mouseout .news-article': 'onTweetOut'
    },
    initDashlet: function() {
        if(this.meta.config) {
            var limit = this.settings.get("limit") || "20";
            this.settings.set("limit", limit);
        }
    },
    onTweetOver: function(event) {
        if ( !_.isUndefined(event.currentTarget) ) {
            this.$(event.currentTarget).find('.footer').show();
        }
    },
    onTweetOut: function(event) {
        if ( !_.isUndefined(event.currentTarget) ) {
            this.$(event.currentTarget).find('.footer').hide();
        }
    },
    _render: function () {
        if (this.tweets || this.meta.config) {
            app.view.View.prototype._render.call(this);
        }
    },
    bindDataChange: function(){
        if(this.model) {
            this.model.on("change", this.loadData, this);
        }
    },
    loadData: function (options) {
        var self = this;
        if (this.disposed || this.meta.config) {
            return;
        }

        var twitter = this.settings.get('twitter') ||
                this.model.get('twitter') ||
                this.model.get('name') ||
                this.model.get('account_name') ||
                this.model.get('full_name'),
            limit = parseInt(this.settings.get("limit"), 10) || 5,
            self = this;
        this.screen_name = this.settings.get('twitter') || false;
        if (!twitter || this.viewName === 'config') {
            return false;
        }

        twitter = twitter.replace(" ", "");
        this.twitter = twitter;
        var url = app.api.buildURL('connector/twitter','',{id:twitter},{count:limit});
        app.api.call('READ', url, {},{
            success:function (data) {
                if (self.disposed) {
                    return;
                }

                var tweets = [];

                if (data.success !== false) {
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
                }

                self.tweets = tweets;
                self.render();
            },
            error: function(xhr,status,error){
                if (xhr.status == 424) {
                    self.needConnect = false;
                    if (xhr.message && xhr.message == 'need OAuth') {
                        self.needConnect = true;
                    }
                    self.template = app.template.get(self.name + '.twitter-need-configure.Home');
                    app.view.View.prototype._render.call(self);
                }
            },
            complete: (options) ? options.complete : null
        });
    },
    _dispose: function() {
        this.model.off("change", this.loadData, this);
        app.view.View.prototype._dispose.call(this);
    }
})
