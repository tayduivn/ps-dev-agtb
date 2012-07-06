(function(app) {

    app.view.views.ListAssociateView = app.view.views.ListView.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            this.template = app.template.get("list"); //todo:remove it later

            // This view behaves like the list view
            this.meta = app.metadata.getView(this.module, "list");
            this.fallbackFieldTemplate = "list";

            this.parentModule = this.context.get("toModule");
            this.parentId = this.context.get("toId");
            this.link = this.context.get("viaLink");
            this.parentBean = app.data.createBean(this.parentModule, { id: this.parentId });

            // Flag indicating if the list is multi-select or not
            this.multiselect = app.data.canHaveMany(this.parentModule, this.link);

            app.logger.debug("Record(s) are to be associated with " +
                this.parentBean + " via link " + this.link);
            app.logger.debug("Multiselect: " + this.multiselect);

            this.headerHeight = 144; //TODO: refactor it
        },

        save: function() {
            var source = this;

            var q = async.queue(function (task, callback) {
                task.relateBean.save(null, {
                    relate: true,
                    fieldsToValidate:{}, //TODO: remove it
                    success: function() {
                        callback();
                    }
                });
            }, 4);

            q.drain = function() {
                var depth = parseInt(source.context.get("depth")) || 1;
                app.router.go(-depth);
            };

            this.$('.selecterd-flag:checked').each(function() {
                var cid = $(this).closest('article').attr('id').replace(source.module, '');

                q.push({relateBean: app.data.createRelatedBean(source.parentBean, source.collection.get(cid), source.link)});
            });
        }
    });

})(SUGAR.App);