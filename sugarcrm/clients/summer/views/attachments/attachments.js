({
    events: {
        'click [class*="orderBy"]': 'setOrderBy'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.collection = new Backbone.Collection();
        this.collection.link = {
            bean: this.model,
            name: 'attachments'
        };

        this.collection.sync = app.BeanCollection.prototype.sync;

        this.collection.fetch({relate:true})

        this.collection.on('change', this.render);

        /**
         * Sharing Manager
         *
         * @param event
         * @constructor
         */
        this.ShareNote = function (event) {

            var self = this;

            this.quitFlag = false;
            this.file = event.originalEvent.dataTransfer.files[0] || event.dataTransfer.files[0];
            this.filename = this.file.name || this.file.fileName;
            this.filesize = this.file.size || this.file.fileSize;
            this.newNoteId = '';

            /**
             * Alert views definitions
             *
             * @type {Object}
             */
            this.alertViews =  {

                /**
                 * upload success alert view
                 *
                 * @param {String} title
                 * @param {String} msg
                 * @param {Object} params - undo options
                 */
                uploadSuccess: function (title, msg, params) {

                    if(!params || !params.undo) params.undo = false;

                    app.alert.show('uploadSuccess', {
                        level: "success",
                        title: title,
                        messages: [msg],
                        autoClose: true
                    });

                    $('#undo-upload-file').css('cursor', 'pointer');
                    $('#undo-upload-file').on('click', function (event) {
                        //TODO delete a the note
                        app.api.call('delete', '../rest/v10/Notes/' + self.newNoteId, null, null, null);
                        self.removeNewFileView();
                        $('.close').click();
                    });
                },

                /**
                 * upload error alert view
                 *
                 * @param title
                 * @param msg
                 */
                uploadError: function (title, msg) {
                    app.alert.show('uploadError', {
                        level: "error",
                        title: title,
                        messages: [msg],
                        autoClose: true
                    });
                }
            };
        };

        /**
         * checking if the file is valid to upload
         *
         * @return {Boolean} true if not the same file that has been uploaded in attachment list, false otherwise
         */
        this.ShareNote.prototype.isValidFile = function () {
            var self = this;
            var attachmentList = $('#attachments_table').find('td');
            var attachmentNames = [];
            _.each(attachmentList, function (value) {
                attachmentNames.push($(value).text());
            });
            _.each(attachmentNames, function (value) {
                if (self.filename.trim() == value.trim()) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', 'cannot upload file with same name');
                    return;
                }

            });
            return !self.quitFlag;
        };

        /**
         * create a new note and set new note id into the current object
         *
         * @return {Boolean} true if succeeding creating a note
         */
        this.ShareNote.prototype.createNote = function () {
            var self = this;
            app.api.call('create', '../rest/v10/Notes', null, {
                success: function (result) {
                    self.newNoteId = result.id;
                },
                error: function (msg) {
                    self.quitFlag = true;
                    self.alertViews.uploadError('Upload Failed', msg);
                }
            }, {async: false});
            return !self.quitFlag;
        };

        /**
         * upload a file to a note
         *
         * @param {String} newNoteId - pass in the new note id in this case
         * @param {File} file - obtained with event.originalEvent.dataTransfer.file[0]
         * @return {Boolean} true if succeeding uploading file
         */
        this.ShareNote.prototype.uploadFileToNote = function (newNoteId, file) {
            var self = this;
            var file = this.file || file;
            //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
            app.api.uploadFileHtml5('create', {
                    module: 'Notes',
                    id: self.newNoteId || newNoteId,
                    field: 'filename'
                },
                file, {
                    success: function(o) {},
                    error: function (msg) {
                        self.quitFlag = true;
                        self.alertViews.uploadError('Upload Failed', msg);
                    }
                }, null);
            return !self.quitFlag;
        };

        /**
         * Relate the current model to a note
         *
         */
        this.ShareNote.prototype.linkNoteToModel = function (callbacks) {
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
                        callbacks(self.newNoteId, self.filename);
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
        };

        /**
         * Real-time added a new row that contains file just uploaded
         */
        this.ShareNote.prototype.showNewFile = function (newNoteId, filename) {
            var addedFileView = '<tr name="Notes_"' + newNoteId + '" class="draggable ui-draggable">';
            addedFileView += '<td style="color:red;">' + filename + '</td></tr>';
            var table = $('#attachments_table').find('tbody')[0];
            $(table).append(addedFileView);
        };

        /**
         * Real-time remove the new row that contains file just uploaded
         */
        this.ShareNote.prototype.removeNewFileView = function () {
            var table = $('#attachments_table').find('tbody')[0];
            $(table).children("tr:last").remove();
        };

        _.bindAll(this);
    },


    /**
     * Render template and bind drag and drop features
     * @private
     */
    render: function() {
        var self = this;
        app.view.View.prototype.render.call(this);
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;
        this.makeDraggableElements();
        this.styleDropbox();

        // make elements dropbox for file drop
        this.$('.dropbox').on('dragover', self.dragOverDropbox);
        this.$('.dropbox').on('dragleave', self.dragLeaveDropbox);
        this.$('.dropbox').on('drop', self.dropOnDropbox);
    },

    /**
     * make the rows of attachment table draggable
     */
    makeDraggableElements: function () {
        this.$('.draggable').draggable({
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
                $(mirror).css({"border": "1px solid black", "font-weight": "bold", "background": "#FBF9EA"});
                return mirror;
            },
            stop: function(event, ui) {
                $(this).removeAttr("style");
            }
        });
    },


    /**
     * intializes style of the dropbox
     */
    styleDropbox: function () {
        this.$('.dropbox').css({
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

        this.$('.dropbox p').css({
            margin: '7px 0'
        });
    },


    /**
     * style the drop box when hovering over
     *
     * @param event
     */
    dragOverDropbox: function (event) {
        event.stopPropagation();
        event.preventDefault();
        this.$('.dropbox').css({"background": "#fff", "width": '320px', "height":'35px'});
        event.originalEvent.dataTransfer.dropEffect = 'copy';
    },


    /**
     * stle the drop box when mouse leaves
     *
     * @param event
     */
    dragLeaveDropbox: function (event) {
        event.stopPropagation();
        event.preventDefault();
        this.$('.dropbox').css({"background": '#C0C0C0', "width": '300px', "height":'30px'});
    },


    /**
     * This is where all the actions happen when a file is dropped on the drop box
     *
     * @param event
     * @param ui
     */
    dropOnDropbox: function (event) {
        event.stopPropagation();
        event.preventDefault();
        this.$('.dropbox').css({"background": '#C0C0C0', "width": '300px', "height":'30px'});

        //*********************** Main actions happens here *********************//
        var shareNote = new this.ShareNote(event);

        if (shareNote.file && shareNote.filename && shareNote.isValidFile()) {
            var result = shareNote.createNote() ? shareNote.uploadFileToNote() : false;
            if (result) {
                var callbacks = shareNote.showNewFile;
                shareNote.linkNoteToModel(callbacks);
            }
        }
        //********************** End of Main actions ****************************//

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

    /**
     *
     */
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
            this.collection.on("change", this.render, this);
        }

    }

})

