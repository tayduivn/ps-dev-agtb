({
    initialize: function(options) {
        // Adds the metadata for the Error module
        app.metadata.set(this._metadata);
        app.data.declareModels();

        // Reprepare the context because it was initially prepared without metadata
        app.controller.context.prepare(true);

        // Attach the metadata to the view
        this.options.meta = this._metadata.modules[this.options.module].views[this.options.name].meta;
        app.view.View.prototype.initialize.call(this, options);

        // use modal template for the fields
        this.fallbackFieldTemplate = "modal";
    },
    render: function() {
        if(this.context.get('errorType')) {
            attributes = this.getErrorAttributes();
            this.model.set(attributes);
        }
        app.view.View.prototype.render.call(this);
    },
    getErrorAttributes: function() {
        var attributes = {};
        if(this.context.get('errorType') ==='404') {
            attributes = {
                title: 'ERR_HTTP_404_TITLE',
                type: 'ERR_HTTP_404_TYPE',
                message: 'ERR_HTTP_404_TEXT'
            };
        } else if(this.context.get('errorType') ==='500') {
           attributes = {
                title: 'ERR_HTTP_500_TITLE',
                type: 'ERR_HTTP_500_TYPE',
                message: 'ERR_HTTP_500_TEXT'
            };
        } else {
            attributes = {
                title: 'ERR_HTTP_DEFAULT_TITLE',
                type: 'ERR_HTTP_DEFAULT_TYPE',
                message: 'ERR_HTTP_DEFAULT_TEXT'
            };
        }
        return attributes;
    },

    _metadata : {
        _hash: '',
        "modules": {
            "Error": {
                "views": {
                    "error": {
                        "meta": {}
                    }
                },
                "layouts": {
                    "error": {
                        "meta": {
                            "type": "simple",
                            "components": [
                                {view: "error"}
                            ]
                        }
                    }
                }
            }
        }
    }
})