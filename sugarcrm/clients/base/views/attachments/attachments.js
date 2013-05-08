({

    plugins: ['Dashlet', 'timeago'],
    _dataFetched: false,
    events : {
        'click a.preview-attachment' : 'previewAttachment',
        'click a.attachments-refresh' : 'loadData',
        'click [name=show_more_button]' : 'showMore',
        'click .btn[name=create_button]': 'openCreateDrawer',
        "click .btn[name=select_button]": "openSelectDrawer"
    },
	initialize : function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },
    initDashlet: function(viewName) {
        this.viewName = viewName;

        if(viewName === "config") {
            app.view.views.RecordView.prototype._buildGridsFromPanelsMetadata.call(this, this.meta.panels);
        } else if(this.context.get("collection")) {

            this.context.set("limit", this.model.get("display_rows"));
            this.context.get("collection").once("reset", function(){
                this._dataFetched = true;
            }, this);
        }
    },
    _render : function() {
        var self = this,
            svgIconTemplate = app.template.get('attachments.svg-icon');
        app.view.View.prototype._render.call(this);
        var mimeType = this.model.get("file_mime_type") || '';
        this.$('span[class^="filetype-"]').each(function(){
            var filetype = self._getFileType(mimeType);
            $(this).attr('data-filetype', filetype).html(svgIconTemplate());
        });

    },
    loadData : function(options) {
        if(this.viewName === "config") {
            return;
        }
        app.view.View.prototype.loadData.call(this, options);
    },
    previewAttachment : function(e) {
        var $sender = $(e.currentTarget),
            model = this.collection.where({id: $sender.data('model-id')}).shift();

        if (_.isEmpty(model.get('file_mime_type')) || this._isImage(model.get('file_mime_type'))) {
            var $modal = this.$('.modal');
            $('.modal-header h3', $modal).html(model.get('name'));
            $('.modal-body p.attachment-description', $modal).html(model.get('description'));

            if (model.get('filename')) {
                $('.modal-body img.attachment-image', $modal).attr('src', app.api.buildFileURL({
                    module: model.module,
                    id: model.id,
                    field: 'filename'
                })).hide().on('load', function(){
                    $(this).show();
                });
            } else {
                $('.modal-body img.attachment-image', $modal).attr('src', null).hide();
            }
            $modal.modal('show');
        } else {
            app.router.navigate(app.router.buildRoute(model.module, model.id), {trigger: true});
        }
    },

    _getFileType: function(mimeType) {
        var filetype = mimeType.substr(mimeType.lastIndexOf('/')+1).toUpperCase();
        return filetype ? filetype : this.meta.defaultType.toUpperCase();
    },

    _isImage: function(mimeType) {
        return _.contains(this.meta.supportedImageExtensions, mimeType);
    },
    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },
    showMore: function() {
        var options = {};
        this.context.set("limit", parseInt(this.context.get("limit"), 10) + parseInt(this.model.get("display_rows"), 10));
        this.context.resetLoadFlag();
        this.context.set('skipFetch', false);
        this.layout.loadData();
    },
    openSelectDrawer: function() {
        var parentModel = this.model.parentModel,
            linkModule = this.context.get("module"),
            link = this.context.get("link"),
            self = this;

        app.drawer.open({
            layout: 'link-selection',
            context: {
                module: linkModule
            }
        }, function(model) {
            if(!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function(model) {
                        self.context.resetLoadFlag();
                        self.context.set('skipFetch', false);
                        self.context.loadData();
                    },
                    error: function(error) {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: 'ERR_GENERIC_SERVER_ERROR',
                            autoClose: false
                        });
                    }
                };
            relatedModel.save(null, options);
        });
    },
    openCreateDrawer: function() {
        var parentModel = this.model.parentModel,
            link = this.context.get("link"),
            model = app.data.createRelatedBean(parentModel, null, link),
            relatedFields = app.data.getRelateFields(parentModel.module, link);

        if(!_.isUndefined(relatedFields)) {
            _.each(relatedFields, function(field) {
                model.set(field.name, parentModel.get(field.rname));
                model.set(field.id_name, parentModel.get("id"));
            }, this);
        }
        var self = this;
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, function(model) {
            if(!model) {
                return;
            }

            self.context.resetLoadFlag();
            self.context.set('skipFetch', false);
            self.context.loadData();
        });
    }
})
