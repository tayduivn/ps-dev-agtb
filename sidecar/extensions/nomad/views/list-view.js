(function(app) {

    app.view.views.ListView = app.view.View.extend({
        events:{
            'click  .show-more-button':'showMoreRecords',
            'click  article .grip':'onClickGrip',
            'swipeLeft article':'swipeLeftArticle',
            'swipeRight article':'swipeRightArticle'
        },
        initialize: function(options) {
            // Mobile shows only the first two fields
            options.meta.panels[0].fields.length = 2;
            app.view.View.prototype.initialize.call(this, options);

            this.activeArticle = null;
        },

        addOne: function(model) {
            app.logger.debug('ADD ONE!');
            var fieldId = app.view.getFieldId();
            var item = Handlebars.helpers.listItem(model, this, this.meta.panels[0].fields);
            this.$('.items').append(item.toString());

            for(var i = fieldId + 1; i <= app.view.getFieldId(); ++i) {
                this._renderField(this.fields[i]);
            }
        },

        removeOne: function(model) {
            app.logger.debug('REMOVE ONE!');
            this.$("#" + model.module + model.id).remove();
        },

        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
                this.collection.on('add', this.addOne, this);
                this.collection.on('remove', this.removeOne, this);
            }
        },
        showMoreRecords:function() {
           this.collection.paginate({page:this.collection.page,add:true});
        },
        onClickGrip:function (e) {
            var grip = $(e.target);
            var isActive = grip.hasClass('on');
            grip.closest('article').trigger(isActive ? 'swipeRight' : 'swipeLeft');
        },
        swipeLeftArticle:function(e){
            if (this.activeArticle) {
                this.activeArticle.trigger('swipeRight');
            }

            this.activeArticle = $(e.target);
            this.activeArticle.find('.grip').addClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').removeClass('hide').addClass('on');

        },
        swipeRightArticle:function(e){
            this.activeArticle.find('.grip').removeClass('on');
            this.activeArticle.find('[id^=listing-action] .actions').addClass('hide').removeClass('on');
        }

    });

})(SUGAR.App);