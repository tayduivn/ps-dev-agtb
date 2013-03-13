({
    className: 'thumbnail widget',
    plugins: ['Dashlet'],
    events: {
        'click ul.nav-tabs > li > a' : 'contentSwitcher',
        'change select.interactions-filter' : 'filterSwitcher',
        'click button.interactions-list' : 'listSwitcher',
        'click a.more' : 'showMore'
    },

    filterOptions: {
        '7' : 'LBL_LAST_7_DAYS',
        '30' : 'LBL_LAST_30_DAYS',
        '90' : 'LBL_LAST_QUARTER',
        'favorites' : 'LBL_FAVORITES',
        'custom' : 'LBL_MY_CUSTOM_FILTER'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        this.collections = {
            'calls' : app.data.createBeanCollection('Calls'),
            'meetings' : app.data.createBeanCollection('Meetings'),
            'emailsSent' : app.data.createBeanCollection('Emails'),
            'emailsRecv' : app.data.createBeanCollection('Emails')
        };

        this.limit = 5;
        this.params = {
            'list' : 'all',
            'filter' : '7',
            'limit' : this.limit,
            'view' : 'calls'
        };
    },

    loadData: function() {
        var self = this,
            url = app.api.buildURL(this.model.parentModel.module, "interactions", {"id": this.model.parentModel.id}, this.params);

        app.api.call("read", url, null, { success: function(data) {
            _.each(data, function(el, name) {
                self.collections[name].count = el.count;
                self.collections[name].update(el.data);
            });
            self.collection = self.collections[self.params.view];
            self.render();
        }});
    },

    listSwitcher: function(e) {
        var $sender = this.$(e.currentTarget);
        this.params.list = $sender.val();
        this.params.limit = this.limit;
        this.loadData();
    },

    filterSwitcher: function(e) {
        var $sender = this.$(e.currentTarget);
        this.params.filter = $sender.val();
        this.params.limit = this.limit;
        this.loadData();
    },

    showMore: function(e) {
        var $sender = this.$(e.currentTarget);
        e.preventDefault();
        this.params.limit += this.limit;
        this.loadData();
    },

    contentSwitcher: function(e) {
        var $sender = this.$(e.currentTarget), self = this;
        _.each(this.collections, function(item, key) {
            if (key == $sender.attr('class')) {
                self.collection = item;
                self.params.view = key;
                self.params.limit = item.length;
            }
        });
        this.render();
    }
})
