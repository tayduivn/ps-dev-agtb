/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    plugins: ['Dashlet', 'Timeago'],
    limit : 20,
    events: {
        'click .connect-twitter': 'onConnectTwitterClick'
    },
    initDashlet: function() {
        // if config view overide with module specific
        if (this.meta.config) {
            this.dashletConfig = app.metadata.getView(app.controller.context.get('module'), this.name) || this.dashletConfig;
        }

        var limit = this.settings.get("limit") || this.limit;
            this.settings.set("limit", limit);
        this.cacheKey = "twitter.dashlet.current_user_cache";
        var currentUserCache = app.cache.get(this.cacheKey);
        if (currentUserCache && currentUserCache.current_twitter_user_name) {
            self.current_twitter_user_name = currentUserCache.current_twitter_user_name;
        }
        if (currentUserCache && currentUserCache.current_twitter_user_pic) {
            self.current_twitter_user_pic = currentUserCache.current_twitter_user_pic;
        }
    },
    onConnectTwitterClick: function(event) {
        if ( !_.isUndefined(event.currentTarget) ) {
            event.preventDefault();
            var href = this.$(event.currentTarget).attr('href');
            app.bwc.login(false, function(response){
                window.open(href);
            });
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
        if (this.disposed || this.meta.config) {
            return;
        }
        var twitter =
                this.model.get('twitter') ||
                this.model.get('name') ||
                this.model.get('account_name') ||
                this.model.get('full_name'),
            limit = parseInt(this.settings.get("limit"), 10) || this.limit,
            self = this;

        this.screen_name = this.settings.get('twitter') || false;
        //workaround because home module acutally pulls a dashboard instead of an
        //empty home model
        if (_.isNull(this.context.parent)) {
            twitter = this.settings.get('twitter');
        }

        if (!twitter || this.viewName === 'config') {
            return false;
        }

        twitter = twitter.replace(/ /g, "");

        this.twitter = twitter;
        var currentUserUrl = app.api.buildURL('connector/twitter/currentUser');
        if (!self.current_twitter_user_name) {
            app.api.call('READ', currentUserUrl, {},{
                success: function(data) {
                    app.cache.set(self.cacheKey, {
                        'current_twitter_user_name': data.screen_name,
                        'current_twitter_user_pic': data.profile_image_url
                    });
                    self.current_twitter_user_name = data.screen_name;
                    self.current_twitter_user_pic = data.profile_image_url;
                    if (!this.disposed) {
                        self.render();
                    }
                }
            });
        }
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
                            date = app.date.format(time, "Y/m/d H:i:s"),
                            // retweeted tweets are sometimes truncated so use the original as source text
                            text = tweet.retweeted_status ? 'RT @'+tweet.retweeted_status.user.screen_name+': '+tweet.retweeted_status.text : tweet.text,
                            sourceUrl = tweet.source,
                            id = tweet.id_str,
                            name = tweet.user.name,
                            tokenText = text.split(' '),
                            screen_name = tweet.user.screen_name,
                            profile_image_url = tweet.user.profile_image_url_https,
                            j,
                            rightNow = new Date(),
                            diff = (rightNow.getTime() - time.getTime())/(1000*60*60*24),
                            timeLabel= diff > 1 ? 'LBL_TIME_RELATIVE_TWITTER_LONG' : 'LBL_TIME_RELATIVE_TWITTER_SHORT';


                        // Search for links and turn them into hrefs
                        for (j = 0; j < tokenText.length; j++) {
                            if (tokenText[j].charAt(0) == 'h' && tokenText[j].charAt(1) == 't') {
                                tokenText[j] = "<a class='googledoc-fancybox' href=" + '"' + tokenText[j] + '"' + "target='_blank'>" + tokenText[j] + "</a>";
                            }
                        }

                        text = tokenText.join(' ');
                        tweets.push({id: id, name: name, screen_name: screen_name, profile_image_url: profile_image_url, text: text, source: sourceUrl, date: date, timeLabel: timeLabel});
                    }, this);
                }

                self.tweets = tweets;
                if (!this.disposed) {
                    self.template = app.template.get(self.name + '.Home');
                    self.render();
                }
            },
            error: function(xhr,status,error){
                self.showGeneric = true;
                self.errorLBL = app.lang.get('ERROR_UNABLE_TO_RETRIEVE_DATA');
                self.template = app.template.get(self.name + '.twitter-need-configure.Home');
                if (xhr.status === 404) {
                    self.showGeneric = true;
                    self.errorLBL = app.lang.get('LBL_ERROR_CANNOT_FIND_TWITTER') + self.twitter;
                } else if (xhr.status === 424) {
                    app.cache.cut(self.key);
                    self.needConnect = false;
                    self.showGeneric = false;
                    if (xhr.code && xhr.code === 'ERROR_NEED_AUTHORIZE') {
                        self.needConnect = true;
                    } else if (xhr.code && xhr.code === 'ERROR_NEED_OAUTH') {
                        self.needOAuth = true;
                    }
                    self.showAdmin = app.acl.hasAccess('admin', 'Administration');
                }
                if (!self.disposed) {
                    app.view.View.prototype._render.call(self);
                }
            },
            complete: (options) ? options.complete : null
        });
    },
    _dispose: function() {
        if (this.model) {
            this.model.off("change", this.loadData, this);
        }

        app.view.View.prototype._dispose.call(this);
    }
})
