({
    extendsFrom: 'HeaderpaneView',

    initialize: function(options) {
        var moduleMeta = app.metadata.getModule(options.module),
            isBwcEnabled = (moduleMeta && moduleMeta.isBwcEnabled),
            additionalEvents = {};

        if (isBwcEnabled) {
            options = this._removeCreateButton(options);
        } else {
            additionalEvents['click .btn[name=create_button]'] = 'createAndSelect';
            this.events = _.extend({}, this.events, additionalEvents);
        }
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args:[options]});
    },

    _renderHtml: function() {
        var titleTemplate = Handlebars.compile(app.lang.getAppString("LBL_SEARCH_AND_SELECT")),
            moduleName = app.lang.get("LBL_MODULE_NAME", this.module);
        this.title = titleTemplate({module: moduleName});
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: '_renderHtml'});
    },

    /**
     * Open create inline modal with no dupe check
     * On save, set the selection model which will close the selection-list inline modal
     */
    createAndSelect: function() {
        app.drawer.open({
            layout: 'create-nodupecheck',
            context: {
                module: this.module,
                create: true
            }
        }, _.bind(function (context, model) {
            if (!model) {
                return;
            }
            this.context.set('selection_model', model);
        }, this));
    },

    /**
     * Remove the create button from the options metadata
     *
     * @param options
     * @returns {*}
     * @private
     */
    _removeCreateButton: function(options) {
        if (options && options.meta && options.meta.buttons) {
            options.meta.buttons = _.filter(options.meta.buttons, function(button) {
                return (button.name !== 'create_button');
            });
        }

        return options;
    }
})
