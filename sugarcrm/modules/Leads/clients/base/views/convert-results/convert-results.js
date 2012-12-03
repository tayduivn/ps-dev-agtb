({
    events:{
        'click .preview-list-item':'previewRecord'
    },
    bindDataChange: function() {
        this.model.on("change", this.populateResults, this);
    },
    populateResults: function() {
        app.view.View.prototype.render.call(this);

        var newTitle, moduleSingular = app.lang.getAppListStrings("moduleListSingular");


        newTitle = app.lang.get(
            'LBL_CONVERT_MODULE_ASSOCIATED',
            this.module,
            {'moduleName': moduleSingular['Contacts']}
        );
        this.$('.converted-results .contacts .title').text(newTitle);

        newTitle = app.lang.get(
            'LBL_CONVERT_MODULE_ASSOCIATED',
            this.module,
            {'moduleName':  moduleSingular['Accounts']}
        );
        this.$('.converted-results .accounts .title').text(newTitle);

        newTitle = app.lang.get(
            'LBL_CONVERT_MODULE_ASSOCIATED',
            this.module,
            {'moduleName':  moduleSingular['Opportunities']}
        );
        this.$('.converted-results .opportunities .title').text(newTitle);

    },
    previewRecord: function(e) {
        var self = this,
            el = this.$(e.currentTarget),
            data = el.data(),
            module = data.module,
            id = data.id,
            model = app.data.createBean(module);

        model.set("id", id);
        model.fetch({
            success: function(model) {
                model.set("_module", module);

                if( _.isUndefined(self.context._callbacks) ) {
                    // Clicking preview on a related module, need the parent context instead
                    self.context.parent.trigger("togglePreview", model, self.collection);
                }
                else {
                    self.context.trigger("togglePreview", model, self.collection);
                }
            }
        });
    },
})