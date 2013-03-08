({
    extendsFrom: 'RowactionField',
    linkModules: [],
    initialize: function(options) {
        this.events = _.extend({}, this.events, options.def.events || {}, {
            'click a': 'actionClicked'
        });
        app.view.fields.RowactionField.prototype.initialize.call(this, options);
        this.type = 'rowaction';

        this.linkModules = [];
        var subpanel = app.metadata.getLayout(this.module, 'subpanel');

        _.each(subpanel.components, function(metadata){
            if(!metadata.context.link) {
                return;
            }
            var linkedModule = app.data.getRelatedModule(this.module, metadata.context.link);
            if(app.acl.hasAccess('create', linkedModule)) {
                this.linkModules.push({
                    link: metadata.context.link,
                    module: linkedModule,
                    label: metadata.name || linkedModule
                });
            }
        }, this);

        if(_.isEmpty(this.linkModules)) {
            this.isHidden = true;
        }
    },
    actionClicked: function(evt) {
        var self = this;
        app.drawer.open({
            layout: 'link-create',
            context: {
                model: this.model,
                module: this.module,
                linkModules: this.linkModules
            }
        }, function(model) {
            if(!model) {
                return;
            }

            var linkContext = _.find(self.context.children, function(context) {
                return context.get('link') === model.link.name;
            }, this);
            linkContext.resetLoadFlag();
            linkContext.loadData();
        });
    }
})
