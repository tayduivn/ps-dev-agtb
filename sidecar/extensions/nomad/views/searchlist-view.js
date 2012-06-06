(function(app) {

    app.view.views.SearchlistView = app.view.View.extend({
        ITEM_TYPE_DELAY: 400,
        className: "searchlist",
        events: {
            'keyup .search-query': 'onKeyUp',
            'click .favorites-btn': 'onClickFavoritesBtn',
            'click .my-items-btn': 'onClickMyItemsBtn',
            'click .menu-cancel': 'onClickMenuCancel',
            'click .menu-save': 'onClickMenuSave'
        },

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            this.listView = null;
            this.timerId = null;
        },

        render: function() {
            app.view.View.prototype.render.call(this);
        },

        onKeyUp: function(e) {
            if (this.timerId) {
                window.clearTimeout(this.timerId);
            }

            this.timerId = window.setTimeout(_.bind(this.search, this, [this.$('.search-query').val()]), this.ITEM_TYPE_DELAY);
        },

        onClickFavoritesBtn: function(e) {
            e.preventDefault();
            $(e.currentTarget).toggleClass('active');
            this.collection.fetch();
        },

        onClickMyItemsBtn: function(e) {
            e.preventDefault();
            $(e.currentTarget).toggleClass('active');
            this.collection.fetch();
        },

        onClickMenuCancel: function() {
            this.trigger('menu:cancel:clicked');
        },
        onClickMenuSave: function() {

        },
        setListView:function(listView){
            this.listView = listView;
        },
        search: function(text) {
            if(this.listView){
                this.listView.search(text);
            }
        }
    });

})(SUGAR.App);
