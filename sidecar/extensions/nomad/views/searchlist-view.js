(function (app) {

    app.view.views.SearchlistView = app.view.View.extend({
        ITEM_TYPE_DELAY: 400,
        events: {
            'keyup .search-query': 'onKeyUp',
            'click .favorites-btn': 'onClickFavoritesBtn',
            'click .my-items-btn': 'onClickMyItemsBtn'
        },
        initialize: function (options) {
            app.view.View.prototype.initialize.call(this, options);

            this.timerId = null;
        },
        render: function () {
            app.view.View.prototype.render.call(this);
        },
        onKeyUp: function (e) {

            if (this.timerId) {
                window.clearTimeout(this.timerId);
            }

            this.timerId = window.setTimeout(_.bind(this.search, this, [this.$('.search-query').val()]), this.ITEM_TYPE_DELAY);
        },
        onClickFavoritesBtn: function (e) {
            e.preventDefault();
            this.collection.fetch();
        },
        onClickMyItemsBtn: function (e) {
            e.preventDefault();
            this.collection.fetch();
        },
        search: function (text) {
            var cmp = this.getComponentByName('list');
            cmp.search(text);
        },
        getComponentByName: function (name) {
            var cmp = null;
            _.each(this.layout._components, function (c) {
                if (c.name === name) {
                    cmp = c;
                }
            });
            return cmp;
        }
    });

})(SUGAR.App);