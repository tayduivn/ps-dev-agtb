({
    className: 'businessrules',

    loadData: function (options) {
        this.br_uid = this.options.context.attributes.modelId;
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("businessRules:save:finish", null, this);
        this.context.on("businessRules:save:finish", this.saveBusinessRules, this);

        this.context.off("businessRules:save:save", null, this);
        this.context.on("businessRules:save:save", this.saveOnlyBusinessRules, this);

        this.context.off("businessRules:cancel:button", null, this);
        this.context.on("businessRules:cancel:button", this.cancelBusinessRules, this);

        this.myDefaultLayout = this.closestComponent('sidebar');

    },

    render: function () {
        app.view.View.prototype.render.call(this);
        renderBusinessRule(this.br_uid, this.myDefaultLayout);
    },

    saveBusinessRules: function() {
        saveAll(app.router);
    },

    saveOnlyBusinessRules: function() {
        saveOnly();
    },

    cancelBusinessRules: function () {
        cancelAction(app.router);
    }
})

