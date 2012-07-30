
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },

    getTweets: function () {
        var self = this;
        var twitter = this.model.get("name");
        if(!twitter)twitter = this.model.get('account_name');
        if(!twitter)twitter = this.model.get('full_name');


        if (this.model.get('twitter')) {
            twitter = this.model.get('twitter');
        } else {
            twitter = this.model.get('name').replace(" ", "");
            if(!twitter)twitter = this.model.get('account_name').replace(" ", "");
            if(!twitter)twitter = this.model.get('full_name').replace(" ", "");
        }

        $.ajax({
            url: "http://twitter.com/statuses/user_timeline/" + twitter + ".json?count=6&callback=?",
            dataType: "jsonp",
            success: function (data) {
                console.log(data);

                self.tweets = [];
                var tweets = self.tweets;
                for (var i=0; i < data.length; i++) {
                    console.log(data[i]);
                    var day = data[i].created_at.substring(8,10);
                    var month = data[i].created_at.substring(4,7);
                    var year = data[i].created_at.substring(data[i].created_at.length-4, data[i].created_at.length);

                    var text = data[i].text;
                    var sourceUrl = data[i].source;

                    var temp = text.split(' ');
                    for (var j = 0; j<temp.length; j++) {

                        if (temp[j].charAt(0) == 'h' && temp[j].charAt(1) == 't'){
                            temp[j] = "<a class='googledoc-fancybox' href=" + '"' + temp[j] + '"' + "target='_blank'>"+temp[j]+"</a>"
                        }

                    }

                    var text2 = ""

                    for (var k = 0; k<temp.length; k++){
                        text2+=temp[k];
                        text2+=" ";
                    }


                    tweets.push({text: text2, source: sourceUrl, day: day, month: month, year: year} );
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