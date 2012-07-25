
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },

    getTweets: function () {
        var self = this;
        var twitter = this.model.get('twitter');
        $.ajax({
            url: "http://twitter.com/statuses/user_timeline/" + twitter + ".json?count=5&callback=?",
            dataType: "jsonp",
            success: function (data) {
                console.log(data);

                self.tweets = [];
                var tweets = self.tweets;
                for (var i=0; i < data.length; i++) {
                    var text = data[i].text;
                    var sourceUrl = "";

                    var temp = text.split(' ');
                    for (var j = 0; j<temp.length; j++) {

                        if (temp[j].charAt(0) == 'h' && temp[j].charAt(1) == 't'){
                            sourceUrl = temp[j];
                        }

                    }



                    tweets.push({text: text, source: sourceUrl} );
                }



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