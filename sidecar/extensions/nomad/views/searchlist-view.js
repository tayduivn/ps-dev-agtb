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

        onKeyUp: function(e) {
            if (this.timerId) {
                window.clearTimeout(this.timerId);
            }

            this.timerId = window.setTimeout(_.bind(this.search, this, [this.$('.search-query').val()]), this.ITEM_TYPE_DELAY);
        },

        onClickFavoritesBtn: function(e) {
            e.preventDefault();
            $(e.currentTarget).toggleClass('active');
            app.nomad.showLoading();
            this.collection.fetch({favorites: !this.collection.favorites,
                success: function() {
                    app.nomad.hideLoading();
                },
                error: function () {
                    $(e.currentTarget).toggleClass('active');
                    app.nomad.hideLoading();
                    // TODO: This will handled by error module
                    //app.alert.show("data_fetch_error", {level: "error", messages: "Data fetch error!", autoClose: true});
                }
            });
        },

        onClickMyItemsBtn: function(e) {
            e.preventDefault();
            $(e.currentTarget).toggleClass('active');
            app.nomad.showLoading();
            this.collection.fetch({myItems: !this.collection.myItems,
                success: function() {
                    app.nomad.hideLoading();
                },
                error: function () {
                    $(e.currentTarget).toggleClass('active');
                    app.nomad.hideLoading();
                    //app.alert.show("data_fetch_error", {level: "error", messages: "Data fetch error!", autoClose: true});
                }
            });
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
