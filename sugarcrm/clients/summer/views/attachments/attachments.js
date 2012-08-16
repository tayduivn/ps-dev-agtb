({

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    events: {
        'click [class*="orderBy"]': 'setOrderBy'
    },


    /**
     * Initializes view
     * @param options
     */
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(self, options);
        self.collection = new Backbone.Collection();
        self.collection.link = {
            bean: self.model,
            name: 'attachments'
        };
        self.collection.sync = app.BeanCollection.prototype.sync;

        self.collection.fetch({relate:true})
        console.log(self.collection);
        self.collection.on('change', self.render);

    },


    /**
     * Render template and bind drag and drop features
     * @private
     */
    _renderHtml: function() {
        var that = this;
        app.view.View.prototype._renderHtml.call(this);
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;
        this.makeDraggableElements();
        this.styleDropbox();

        // make elements dropbox for file drop
        $('.dropbox').on('dragover', that.dragOverDropbox);
        $('.dropbox').on('dragleave', that.dragLeaveDropbox);
        $('.dropbox').on('drop', that.dropOnDropbox);
    },


    /**
     *
     */
    makeDraggableElements: function () {
        $(".draggable").draggable({
            opacity: 1,
            revert: 'invalid',
            snapMode: 'inner',
            containment: 'document',
            stack: 'div',
            cursor: 'pointer',
            hoverClass: 'hover',
            cursorAt: {top: 0, left: 0},
            helper: function(event) {
                var original = $(event.currentTarget);
                original.css("background", "#FBF9EA");
                mirror = $('<div></div>').append(original.clone());
                $(mirror).css("border", "1px solid black");
                return mirror;
            },
            stop: function(event, ui) {
                $(this).removeAttr("style");
            }
        });
    },


    /**
     *
     */
    styleDropbox: function () {
        $('.dropbox').css({
            width: '300px',
            height: '30px',
            border: '3px dashed #ccc',
            'border-radius': '1px',
            'vertical-align': 'baseline',
            margin: '0 auto',
            'text-align': 'center',
            'text-shadow':'1px 1px 0 #fff',
            'background': '#C0C0C0'
        });

        $('.dropbox p').css({
            margin: '7px 0'
        });
    },


    /**
     *
     * @param event
     */
    dragOverDropbox: function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).css({"background": "#fff", "width": '320px', "height":'35px'});
        event.originalEvent.dataTransfer.dropEffect = 'copy';
    },


    /**
     *
     * @param event
     */
    dragLeaveDropbox: function (event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).css({"background": '#C0C0C0', "width": '300px', "height":'30px'});
    },


    /**
     *
     * @param event
     * @param ui
     */
    dropOnDropbox: function (event) {


        var ShareNote = function (event) {

            var self = this;

            this.quitFlag = false;

            this.file = event.originalEvent.dataTransfer.files[0] || event.dataTransfer.files[0];

            this.filename = this.file.name || this.file.fileName;

            this.filesize = this.file.size || this.file.fileSize;

            this.newNoteId = '';

            this.attachments_table = $('#attachments_table').find('tbody')[0];

            this.addedFileView = '';

            this.alertViews =  {

                // enable undo functionality for share success
                uploadSuccess: function (title, msg, params) {

                    if(!params || !params.undo) params.undo = false;

                    App.alert.show('uploadSuccess', {
                        level: "success",
                        title: title,
                        messages: [msg],
                        autoClose: true
                    });

                    $('#undo-upload-file').css('cursor', 'pointer');
                    $('#undo-upload-file').on('click', function (event) {
                        //TODO delete a the note
                        App.api.call('delete', '../rest/v10/Notes/' + self.newNoteId + '/file/filename', null, null, null);
                        App.api.call('delete', '../rest/v10/Notes/' + self.newNoteId, null, null, null);
                        self.removeNewFileView();
                        $('.close').click();
                    });
                },

                // error alert view
                uploadError: function (title, msg) {
                    App.alert.show('uploadError', {
                        level: "error",
                        title: title,
                        messages: [msg],
                        autoClose: true
                    });
                }
            };

        };

        /**
         *
         * @return {Boolean}
         */
        ShareNote.prototype.isValidFile = function () {
            var self = this;
            var attachmentList = $('#attachments_table').find('td');
            var attachmentNames = [];
            $.each(attachmentList, function (index, value) {
                attachmentNames.push($(value).text());
            });
            $.each(attachmentNames, function (index, value) {
                if (self.filename == value) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', 'cannot upload file with same name');
                }
            });
            return !self.quitFlag;
        };

        /**
         *
         * @return {Boolean}
         */
        ShareNote.prototype.createNote = function () {
            var self = this;
            App.api.call('create', '../rest/v10/Notes', null, {
                success: function (result) {
                    self.newNoteId = result.id;
                },
                error: function (msg) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', msg);
                }
            }, {async: false});
            return self.quitFlag ? false : true;
        };

        /**
         *
         * @param newNoteId
         * @param file
         * @return {Boolean}
         */
        ShareNote.prototype.uploadFileToNote = function (newNoteId, file) {
            var self = this;
            var file = this.file || file;
            //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
            App.api.uploadFileHtml5('create', {
                module: 'Notes',
                id: self.newNoteId || newNoteId,
                field: 'filename'
            },
            file, {
                success: function(o) {console.log(o);},
                error: function (msg) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', msg);
                }
            }, null);
            return self.quitFlag ? false : true;
        };

        /**
         *
         * @return {Boolean}
         */
        ShareNote.prototype.linkNoteToModel = function () {
            var self = this;
            var mainModule = app.controller.context.attributes.module;
            var mainModelId = app.controller.context.attributes.modelId;
            var mainBean = app.data.createBean(mainModule, {id: mainModelId});
            mainBean.fetch({
                success: function (model) {
                    try {
                        var _note = app.data.createRelatedBean(model, null, 'notes', {id: self.newNoteId});
                        _note.save(null, {relate: true});
                        self.alertViews.uploadSuccess('<p style="font-size:16px;text-align:center;">You have uploaded a file.  <a id="undo-upload-file"><strong>Undo</strong></a></p>', '', {undo: true});
                    } catch (err) {
                        self.quitFlag = true;
                        self.alertViews.uploadError('Upload Failed', err);
                    }
                },
                error: function (err) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', err);
                }
            });
            return self.quitFlag ? false : true;
        };

        /**
         *
         */
        ShareNote.prototype.showNewFile = function () {
            this.addedFileView = '<tr name="Notes_"' + this.newNoteId + '" class="draggable ui-draggable">';
            this.addedFileView += '<td>' + this.filename + '</td></tr>';
            $(this.attachments_table).append(this.addedFileView);
        };

        /**
         *
         */
        ShareNote.prototype.removeNewFileView = function () {
            console.log('delete');
            $(this.attachments_table).children("tr:last").remove();
        }


        // Main actions happens here
        var shareNote = new ShareNote(event);
        if (shareNote.file && shareNote.filename && shareNote.isValidFile()) {
            var result = shareNote.createNote() ? shareNote.uploadFileToNote() : false;
            if (result) {
                result = shareNote.linkNoteToModel() ? shareNote.showNewFile() : false;
            } else {
                return false;
            }
        }

    },


    /**
     * Sets order by on collection and view
     * @param {Object} event jquery event object
     */
    setOrderBy: function(event) {
        var orderMap, collection, fieldName, nOrder, options,
            self = this;
        //set on this obj and not the prototype
        self.orderBy = self.orderBy || {};

        //mapping for css
        orderMap = {
            "desc": "_desc",
            "asc": "_asc"
        };

        //TODO probably need to check if we can sort this field from metadata
        collection = self.collection;
        fieldName = self.$(event.target).data('fieldname');

        if (!collection.orderBy) {
            collection.orderBy = {
                field: "",
                direction: ""
            };
        }

        nOrder = "desc";

        // if same field just flip
        if (fieldName === collection.orderBy.field) {
            if (collection.orderBy.direction === "desc") {
                nOrder = "asc";
            }
            collection.orderBy.direction = nOrder;
        } else {
            collection.orderBy.field = fieldName;
            collection.orderBy.direction = "desc";
        }

        // set it on the view
        self.orderBy.field = fieldName;
        self.orderBy.direction = orderMap[collection.orderBy.direction];

        // Treat as a "sorted search" if the filter is toggled open
        options = self.filterOpened ? self.getSearchOptions() : {};

        // If injected context with a limit (dashboard) then fetch only that
        // amount. Also, add true will make it append to already loaded records.
        options.limit   = self.limit || null;
        options.success = function() {
            self.render();
        };

        // refetch the collection
        collection.fetch(options);
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
            this.collection.on("change", this.render, this);
        }
        if (this.model) {
            this.model.on("change", this.render, this);
        }
    }
})

