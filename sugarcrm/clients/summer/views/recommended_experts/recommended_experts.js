({
    events: {
        "click .recommendme": "getRecommendations"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        this.$(".find").typeahead();
    },

    getRecommendations: function() {
        api.call('read', 'Users/recommended/');
    }
})