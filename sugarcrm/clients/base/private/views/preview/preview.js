({
/**
 * View that displays a model pulled from the activities stream.
 * @class View.Views.PreviewView
 * @alias SUGAR.App.view.views.PreviewView
 * @extends View.View
 */
    _meta: {
        "panels": [
            {
                "label": "Preview",
                "fields": [
                    {
                        "name": "name",
                        "default": true,
                        "enabled": true,
                        "sorting": true,
                        "width": 35,
                        "type": "text",
                        "label": "LBL_SUBJECT"
                    },
                    {
                        "name": "description",
                        "default": true,
                        "enabled": true,
                        "sorting": true,
                        "width": 35,
                        "type": "textarea",
                        "label":"LBL_DESCRIPTION"
                    },
                    {
                        "name": "date_entered",
                        "default": true,
                        "enabled": true,
                        "sorting": true,
                        "width": 35,
                        "type": "datetime",
                        "label": "LBL_DATE_ENTERED"
                    },
                    {
                        "name": "created_by_name",
                        "default": true,
                        "enabled": true,
                        "sorting": true,
                        "width": 35,
                        "type": "relate",
                        "label": "LBL_CREATED"
                    },
                    {
                        "name": "modified_by_name",
                        "default": true,
                        "enabled": true,
                        "sorting": true,
                        "width": 35,
                        "type": "relate",
                        "label": "LBL_MODIFIED_NAME"
                    }
                ]
            }
        ]
    },
    events: {
        'click .closeSubdetail': 'closePreview'
    },
    initialize: function(options) {
        this.options.meta = this._meta;
        app.view.View.prototype.initialize.call(this, options);
    },
    _renderSelf: function() {
        // Fires on shared parent layout .. nice alternative to app.events for relatively simple page 
        this.layout.layout.off("search:preview", null, this);
        this.layout.layout.on("search:preview", this.togglePreview, this);

        this.$el.parent().parent().addClass("container-fluid tab-content").attr("id", "folded");
    },
    togglePreview: function(model) {
        if(model) {
            this.model.set(model);
            app.view.View.prototype._renderSelf.call(this);
        }
    },
    closePreview: function() {
        this.model.clear();
        this.$el.empty();
        $("li.search").removeClass("on");
    }

})

