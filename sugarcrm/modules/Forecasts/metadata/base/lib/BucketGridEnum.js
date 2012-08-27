(function(app) {

    app.view.BucketGridEnum = function (field, view) {
        this.field = field;
        this.field.def.options = app.config.buckets_dom || [];
        this.view = view;
        return this.render();
    };

    app.view.BucketGridEnum.prototype.render = function() {

        this.field.enableOverFlow = function(){
            this.$el.parent().css('overflow', 'visible');
        };

        this.field.disableOverFlow = function(){
            this.$el.parent().css('overflow', 'hidden');
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'mouseenter': 'enableOverFlow',
            'mouseleave': 'disableOverFlow'
        });
        this.field.delegateEvents();

        return this.field;
    };

})(SUGAR.App);