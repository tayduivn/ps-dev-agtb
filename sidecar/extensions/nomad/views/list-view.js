(function(app) {

    app.view.views.ListView = app.view.View.extend({
        ITEM_TYPE_DELAY:400,
        MAX_PAGE_SIZE:20,
        events:{
            'click  .show-more-top-btn':'showMoreTopRecords',
            'click  .show-more-bottom-btn':'showMoreBottomRecords',
            'click  article .grip':'onClickGrip',
            'swipeLeft article':'onSwipeLeftItem',
            'swipeRight article':'onSwipeRightItem',
            'click .remove-item-btn':'onRemoveItem',
            'keydown .search-query':'onKyeDown'
        },
        initialize: function(options) {
            // Mobile shows only the first two fields
            options.meta.panels[0].fields.length = 2;
            app.view.View.prototype.initialize.call(this, options);

            this.activeArticle = null;

            this.timerId = null;
        },
        render:function(){
            app.view.View.prototype.render.call(this);

            this.contextMenuEl = this.$('.context-menu');
        },
        onKyeDown:function(){
            if(this.timerId){
                window.clearTimeout(this.timerId);
            }

            this.timerId = window.setTimeout(_.bind(this.search,this),this.ITEM_TYPE_DELAY);
        },
        search:function() {
            //TODO fix list rendering
          this.collection.fetch();
        },
        addOne: function(model,collection,options) {
            app.logger.debug('ADD ONE!');
            var fieldId = app.view.getFieldId();
            var item = Handlebars.helpers.listItem(model, this, this.meta.panels[0].fields);

            if (options.addTop && this.$('.items').children().length) {
                this.$('.items').children().first().before(item.toString());
            } else {
                this.$('.items').append(item.toString());
            }

            for(var i = fieldId + 1; i <= app.view.getFieldId(); ++i) {
                this._renderField(this.fields[i]);
            }
        },
        removeOne: function(model) {
            app.logger.debug('REMOVE ONE!');
            this.$("#" + this.module + model.id).remove();
        },

        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
                this.collection.on('add', this.addOne, this);
                this.collection.on('remove', this.removeOne, this);
            }
        },
        showMoreTopRecords:function () {
            var offset = Math.max(this.collection.offset - this.collection.length - app.config.maxQueryResult,0);

            if (offset === 0) {
                this.$('.show-more-top-btn').hide();
            }

            this.$('.show-more-bottom-btn').show();

            this.collection.fetch({add:true,
                silent:true,
                offset:offset,
                max_num:app.config.maxQueryResult,
                fields:this.collection.fields,
                success:_.bind(function (collection,items) {
                    var models = [];

                    _.each(items,function(item){
                        var model = this.collection.get(item.id);
                        models.push(model);
                        this.removeOne(model);
                        this.collection.remove(model,{silent:true});
                    },this);

                    while(models.length){
                        this.collection.add(models.pop(),{addTop:true,at:0});
                    }

                    if (this.collection.length > this.getMaxPageSize()) {

                        while (this.collection.length > this.getMaxPageSize()) {
                            var model = this.collection.pop({silent:true});
                            this.removeOne(model);
                        }
                    }

                    this.collection.offset += this.collection.length - app.config.maxQueryResult;

                }, this)});
        },
        showMoreBottomRecords:function () {
            this.collection.paginate({add:true,
                success:_.bind(function () {
                    if (this.collection.length > this.getMaxPageSize()) {
                        this.$('.show-more-top-btn').show();

                        while (this.collection.length > this.getMaxPageSize()) {
                            var model = this.collection.shift({silent:true});
                            this.removeOne(model);
                        }

                    }
                    if (this.collection.next_offset === -1) {
                        this.$('.show-more-bottom-btn').hide();
                    }

                }, this)});
        },
        getMaxPageSize:function(){
            return Math.max(this.MAX_PAGE_SIZE, app.config.maxQueryResult);
        },
        onClickGrip:function (e) {
            var grip = $(e.target);
            var isActive = grip.hasClass('on');
            grip.closest('article').trigger(isActive ? 'swipeRight' : 'swipeLeft');
        },
        onSwipeLeftItem:function(e){
            if (this.activeArticle) {
                this.activeArticle.trigger('swipeRight');
            }

            this.activeArticle = $(e.target);
            this.contextMenuEl.appendTo(this.activeArticle.find('.menu-container'));
            this.activeArticle.find('.grip').addClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').removeClass('hide').addClass('on');
        },
        onSwipeRightItem:function(e){
            this.activeArticle.find('.grip').removeClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').addClass('hide').removeClass('on');
        },
        onRemoveItem:function(e){
            var isOk = confirm(app.lang.getAppString('MSG_CONFIRM_DELETE'));

            if (isOk) {
                var cid = $(e.target).closest('article').attr('id').replace(this.module, '');
                var model = this.collection.get(cid);
                model.destroy();
            }
        },
        onEditItem:function(){
            app.logger.debug('EDIT ONE!');
        }
    });

})(SUGAR.App);