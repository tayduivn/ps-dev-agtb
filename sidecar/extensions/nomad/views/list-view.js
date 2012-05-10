(function(app) {

    app.view.views.ListView = app.view.View.extend({
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

            this.topItemsModels = [];
            this.timerId = null;
            this.ITEM_TYPE_DELAY = 400;
        },
        onKyeDown:function(){
            if(this.timerId)
            {
                window.clearTimeout(this.timerId);
            }

            this.timerId = window.setTimeout(_.bind(this.search,this),this.ITEM_TYPE_DELAY);
        },
        search:function(){
          this.collection.fetch();
        },
        addOne: function(model,collection,options) {
            app.logger.debug('ADD ONE!');
            var fieldId = app.view.getFieldId();
            var item = Handlebars.helpers.listItem(model, this, this.meta.panels[0].fields);

            if (options.addTop) {
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
            var count = Math.min(app.config.maxQueryResult,this.topItemsModels.length);

            for (var i = 0; i < count; i++) {
                this.collection.add(this.topItemsModels.pop(),{addTop:true,at:0});
            }
            this.showMoreItems();
        },
        showMoreBottomRecords:function () {
            this.collection.paginate({add:true, success:_.bind(function () {
                this.showMoreItems(true);
            }, this)});
        },
        showMoreItems:function (isBottomShift) {
            var model = null;
            while (app.config.maxQueryResult < this.collection.length) {

                if(!isBottomShift){
                    model = this.collection.pop({silent:true});
                }else{
                    model = this.collection.shift({silent:true});
                    this.topItemsModels.push(model);
                }
                this.removeOne(model);
            }

            if (this.topItemsModels.length) {
                this.$('.show-more-top-btn').show();
            }else{
                this.$('.show-more-top-btn').hide();
            }

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
            this.activeArticle.find('.grip').addClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').removeClass('hide').addClass('on');

        },
        onSwipeRightItem:function(e){
            this.activeArticle.find('.grip').removeClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').addClass('hide').removeClass('on');
        },
        onRemoveItem:function(e){
            //need confirm!!!!
            var cid = $(e.target).closest('article').attr('id').replace(this.module,'');
            var model = this.collection.get(cid);
            model.destroy();
        },
        onEditItem:function(){
            app.logger.debug('EDIT ONE!');
        }
    });

})(SUGAR.App);