(function (app) {

    app.view.views.ListView = app.view.View.extend({
        MAX_PAGE_SIZE: 20,

        events: {
            'click  .show-more-top-btn': 'showMoreTopRecords',
            'click  .show-more-bottom-btn': 'showMoreBottomRecords',
            'click  article .grip': 'onClickGrip',
            'swipeLeft article': 'onSwipeLeftItem',
            'swipeRight article': 'onSwipeRightItem',
            'click .remove-item-btn': 'onRemoveItem',
            'click .unlink-item-btn': 'onUnlinkItem',
            'click .edit-item-btn': 'onEditItem',
            'click .menu-item':'onClickMenuItem'
        },

        initialize: function (options) {
            // Mobile shows only the first two fields
            options.meta.panels[0].fields.length = 2;

            // Default list item partial
            options.templateOptions = {
                partials: {
                    'list.item': app.template.get("list.item")
                }
            };
            app.view.View.prototype.initialize.call(this, options);

            this.activeArticle = null;

            this.unlinkVisible = true;
            // We must not allow a user to break a one-to-many relationship,
            // if the relate field is required for the relationship.
            var link = this.context.get("link");
            if (link) {
                var relatedField = app.data.getRelateField(this.context.get("parentModule"), link);
                this.unlinkVisible = relatedField && relatedField.required === true ? false : true;
            }
        },

        _renderSelf: function () {

            app.view.View.prototype._renderSelf.call(this);

            this.contextMenuEl = this.$('.context-menu');

            if (this.collection.next_offset === -1) {
                this.$('.show-more-bottom-btn').hide();
            }
        },

        search: function(query) {
            this.collection.fetch({
                query: query
            });
        },

        onItemAdded: function (model, collection, options) {
            var fieldId = app.view.getFieldId();

            var item = Handlebars.helpers.include('list.item', model, this, this.meta.panels[0].fields);

            if (options.addTop && this.$('.items').children().length) {
                this.$('.items').children().first().before(item.toString());
            } else {
                this.$('.items').append(item.toString());
            }

            for (var i = fieldId + 1; i <= app.view.getFieldId(); ++i) {
                this._renderField(this.fields[i]);
            }
        },

        onItemRemoved: function (model) {
            this.$("#" + this.module + model.id).remove();
        },

        bindDataChange: function () {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
                this.collection.on('add', this.onItemAdded, this);
                this.collection.on('remove', this.onItemRemoved, this);
            }
        },

        showMoreTopRecords: function () {
            this.showLoadingMsg('.show-more-top-btn',true);

            var offset = Math.max(this.collection.offset - this.collection.length - app.config.maxQueryResult, 0);

            this.collection.fetch({
                add: true,
                relate: !!this.context.get('link'),
                silent: true,
                offset: offset,
                max_num: app.config.maxQueryResult,
                fields: this.collection.fields,
                success: _.bind(function (collection, items) {
                    if (offset === 0) {
                        this.$('.show-more-top-btn').hide();
                    }

                    this.$('.show-more-bottom-btn').show();

                    var models = [];

                    _.each(items, function (item) {
                        var model = this.collection.get(item.id);
                        models.push(model);
                        this.onItemRemoved(model);
                        this.collection.remove(model, {silent: true});
                    }, this);

                    while (models.length) {
                        this.collection.add(models.pop(), {addTop: true, at: 0});
                    }

                    if (this.collection.length > this.getMaxPageSize()) {
                        while (this.collection.length > this.getMaxPageSize()) {
                            var model = this.collection.pop({silent: true});
                            this.onItemRemoved(model);
                        }
                    }

                    this.collection.offset += this.collection.length - app.config.maxQueryResult;

                    this.showLoadingMsg('.show-more-top-btn',false);

                }, this)});
        },

        showMoreBottomRecords: function () {
            this.showLoadingMsg('.show-more-bottom-btn',true);

            this.collection.paginate({add: true,
                relate: !!this.context.get('link'),
                success: _.bind(function () {
                    if (this.collection.length > this.getMaxPageSize()) {
                        this.$('.show-more-top-btn').show();

                        while (this.collection.length > this.getMaxPageSize()) {
                            var model = this.collection.shift({silent: true});
                            this.onItemRemoved(model);
                        }

                    }

                    this.showLoadingMsg('.show-more-bottom-btn',false);

                    if (this.collection.next_offset === -1) {
                        this.$('.show-more-bottom-btn').hide();
                    }

                }, this)});
        },

        showLoadingMsg: function (selector, isShow) {
            if (isShow) {
                this.$(selector + ' .show_more_posts').hide();
                this.$(selector + ' .loading-holder').show();
            } else {
                this.$(selector + ' .show_more_posts').show();
                this.$(selector + ' .loading-holder').hide();
            }
        },

        getMaxPageSize: function () {
            return Math.max(this.MAX_PAGE_SIZE, app.config.maxQueryResult);
        },

        onClickGrip: function (e) {
            var grip = $(e.target);
            var isActive = grip.hasClass('on');
            grip.closest('article').trigger(isActive ? 'swipeRight' : 'swipeLeft');
        },

        onSwipeLeftItem: function (e) {
            if (this.activeArticle) {
                this.activeArticle.trigger('swipeRight');
            }

            this.activeArticle = $(e.target);
            this.contextMenuEl.appendTo(this.activeArticle.find('.menu-container'));
            this.activeArticle.find('.grip').addClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').removeClass('hide').addClass('on');
        },

        onSwipeRightItem: function (e) {
            this.hideContextMenu();
        },

        hideContextMenu:function(){
            if (this.activeArticle) {
                this.activeArticle.find('.grip').removeClass('on');
                this.activeArticle.find('[id^=listing-action] .actions').addClass('hide').removeClass('on');
            }
        },

        onRemoveItem: function (e) {
            e.preventDefault();
            var self = this;
            // TODO: Localize
            app.nomad.showConfirm("Do you really want to delete this record?",
                function(index) {
                    self.hideContextMenu();
                    if (index == 2) {
                        var id = $(e.target).closest('article').attr('id').replace(self.module, '');
                        self.collection.get(id).destroy();
                    }
                },
                "Confirm", "Cancel,Delete"
            );
        },

        onUnlinkItem: function (e) {
            e.preventDefault();
            var self = this;
            // TODO: Localize
            app.nomad.showConfirm("Do you really want to unlink this record?",
                function(index) {
                    self.hideContextMenu();
                    if (index == 2) {
                        var id = $(e.target).closest('article').attr('id').replace(self.module, '');
                        self.collection.get(id).destroy({ relate: true });
                    }
                },
                "Confirm", "Cancel,Unlink"
            );
            this.hideContextMenu();
        },

        onEditItem: function (e) {
            e.preventDefault();
            var cid = $(e.target).closest('article').attr('id').replace(this.module, '');
            app.router.navigate(this.module + "/" + cid + "/edit", {trigger: true});
            this.hideContextMenu();
        },

        onClickMenuItem:function(e){
            var cid = $(e.target).closest('article').attr('id').replace(this.module, '');
            var item = this.collection.get(cid);
            this.trigger('menu:item:clicked',item);
            this.hideContextMenu();
        }
    });

})(SUGAR.App);