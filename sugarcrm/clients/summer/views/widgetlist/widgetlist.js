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
     * Initializes widget list
     *
     * @param options
     */
    initialize: function (options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);

        this.ShareManager = function (targetModule, targetId) {

            this.quitFlag = false;

            this.targetModule = targetModule;

            this.targetId = targetId;

            this.targetBean = app.data.createBean(this.targetModule, {id: this.targetId});

        }

        /**
         * Define alert views after sharing
         *
         * @type {Object}
         */
        this.ShareManager.prototype.alertViews = {


            /**
             * success alert view
             *
             * @param {String} title
             * @param {String} msg
             * @param {String} a note id to undo, delete
             * @param {Object} params - specified undo options (see code below)
             */
            shareSuccess: function (shareManager,title, msg, newNoteId, params) {

                var targetModule, targetId, draggableModule, draggableId;

                if (!params) {
                    params.undo = false;
                } else {
                    params.undo = true;
                    draggableModule = params.draggableModule;
                    draggableId = params.draggableId;
                    targetModule = params.targetModule;
                    targetId = params.targetId;
                }

                app.alert.show('shareSuccess', {
                    level: "success",
                    title: title,
                    messages: [msg],
                    autoClose: true
                });

                // enable undo option (delete all the relationships)
                if (params.undo) {

                    $('#undo-link').css('cursor', 'pointer');
                    $('#undo-link').on('click', function (event) {

                        //TODO unlink relationship, new api might eventually implemented (see sugarapi.js)
                        app.api.call('delete', '../rest/v10/' + targetModule + '/' + targetId + '/link/' + draggableModule.toLowerCase() + '/' + draggableId , null, null, null);

                        if (newNoteId) {
                            app.api.call('delete', '../rest/v10/Notes/' + newNoteId, null, null, null);
                            shareManager.removeNewFileView();
                        }
                        $('.close').click();
                    });
                }

            },

            /**
             * error alert view when something goes wrong
             *
             * @param {String} title
             * @param {String} msg
             */
            shareError: function (title, msg) {
                app.alert.show('shareError', {
                    level: "error",
                    title: title,
                    messages: [msg],
                    autoClose: true
                });
            }
        }

        /**
         * create link relationship between the current bean and another bean
         *
         * @param {String} relatedModule - the name of another bean module
         * @param {String} relatedId - the id of another bean id
         * @return {Boolean} true if success, false otherwise
         */
        this.ShareManager.prototype.linkModels = function (relatedModule, relatedId) {
            var self = this;
            this.targetBean.fetch({
                success: function (model) {
                    var _relatedBean = app.data.createRelatedBean(model, null, relatedModule.toLowerCase(), {id: relatedId});
                    _relatedBean.save(null, {relate: true});
                },
                error: function (msg) {
                    self.quitFlag = true;
                    self.alertViews.shareError('Share Failed', 'cannot share relationship');
                }
            });
            return !self.quitFlag;
        };

        /**
         * create new note
         *
         * @return {String} new note id, otherwise empty string
         */
        this.ShareManager.prototype.createNote = function () {
            var self = this;
            app.api.call('create', '../rest/v10/Notes', null, {
                success : function (result) {
                    self.newNoteId = result.id;
                },
                error: function (msg) {
                    self.quitFlag = true;
                    self.alertViews.shareError('Share Failed', 'cannot create a new note');
                }
            }, {async: false});
            return self.newNoteId;
        };

        /**
         * upload a file to a note
         *
         * @param {String} noteId - note id
         * @param {File} file - file to upload (if dragged from browser, obtain with event.originalEvent.dataTransfer[0]
         * @return {Boolean} true if success, false otherwise
         */
        this.ShareManager.prototype.uploadFileToNote = function (noteId, file) {
            var self = this;
            self.filename = file.name || file.fileName;

            //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
            app.api.uploadFileHtml5('create', {
                    module: 'Notes',
                    id: self.newNoteId || noteId,
                    field: 'filename'
                },
                file, {
                    success: function(o) {

                    },
                    error: function () {
                        self.quitFlag = true;
                        self.alertViews.shareError('Share Failed', 'cannot create upload a file to note');
                    }
                }, {async: false});
            return !self.quitFlag;
        };


        /**
         * Real-time adding a new row with the new file just uploaded
         *
         */
        this.ShareManager.prototype.addNewFileView = function () {
            var addedFileView = '';
            addedFileView += '<tr name="Notes_"' + this.newNoteId + '" class="draggable ui-draggable">';
            addedFileView += '<td>' + this.filename + '</td></tr>';
            var table = $('#attachments_table').find('tbody')[0];
            $(table).append(addedFileView);
        };

        /**
         * Real-time removing the last row that has just been added
         *
         */
        this.ShareManager.prototype.removeNewFileView = function () {
            var table = $('#attachments_table').find('tbody')[0];
            $(table).children('tr:last').remove();
        };

        /**
         * check for the valid file
         *
         * @param {Boolean} true if already have this filename on attachment table, false otherwise
         */
        this.ShareManager.prototype.isValidFile = function (file) {
            var self = this;
            var filename = file.name || file.fileName;
            var attachmentList = $('#attachments_table').find('td');
            var attachmentNames = [];
            _.each(attachmentList, function (value) {
                attachmentNames.push($(value).text());
            });
            _.each(attachmentNames, function (value) {
                if (filename == value) {
                    self.quitFlag = true;
                    self.alertViews.shareError('Share Failed', 'cannot upload file with same name, please drag available file here to share');
                }
            });
            return !self.quitFlag;
        }


        _.bindAll(this);
    },

    /**
     * Render template and bind events
     *
     * @private
     */
    _renderHtml: function() {
        var self = this;
        app.view.View.prototype._renderHtml.call(this);

        // Dashboard layout injects shared context with limit: 5.
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;

        this.makeDraggableElements();

        // define droppable element for contacts
        this.makeDroppableElements();

        this.$('.filedrop').data('filehover', '0');
        this.$('.filedrop').on('dragover', self.dragOverFiledrop);
        this.$('.filedrop').on('dragleave', self.dragLeaveFiledrop);
        this.$('.filedrop').on('drop', self.dropOnFiledrop);

    },


    /**
     * Style the each row inside the table and make them draggable
     *
     * @param event
     */
    makeDraggableElements: function () {
        this.$(".draggable").draggable({
            opacity: 5,
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
                $(mirror).css({border: "1px solid black", 'font-weight': 'bold'});
                return mirror;
            },
            stop: function(event, ui) {
                $(this).removeAttr("style");
            }
        });
    },


    /**
     * Style each row inside the table and make them droppable
     *
     * @param event
     */
    makeDroppableElements: function () {
        this.$('.droppable').droppable({
            accept: '.draggable',
            over: function(event, ui) {
                event.stopPropagation();
                event.preventDefault();
                $(this).css({"border": "2px dashed blue", "background": "#C0C0C0"});
                $(this).tooltip({title: 'Share with', trigger: 'manual', placement: 'left'});
                $(this).tooltip('show');
            },
            out: function(event, ui) {
                $(this).tooltip('hide');
                $(this).removeAttr("style");
            }
        });
    },


    /**
     * Handle drag over if dragging element is the file from local desktop
     * @param event
     */
    dragOverFiledrop: function (event) {
        var targetRow = this.$(event.target).parent('tr')[0];
        this.$(targetRow).tooltip({title: 'Share with', trigger: 'manual', placement: 'left'});
        event.stopPropagation();
        event.preventDefault();
        if (this.$(targetRow).data('filehover') == '0') { // track mouse dragging file hovering
            this.$(targetRow).tooltip('show');
            this.$(targetRow).data('filehover', '1');
        }
        this.$(targetRow).css({"border": "2px dashed blue", "background": "#C0C0C0"});
        event.originalEvent.dataTransfer.dropEffect = 'copy';
    },


    /**
     * handle drag leave if dragging elements from local desktop leave
     * @param event
     */
    dragLeaveFiledrop: function (event) {
        var targetRow = this.$(event.target).parent('tr')[0];
        event.stopPropagation();
        event.preventDefault();
        if (this.$(targetRow).data('filehover') == '1') {
            this.$(targetRow).tooltip('hide');
            this.$(targetRow).data('filehover', '0');
        }
        this.$(targetRow).removeAttr("style");
    },


    /**
     * there is where all the action happens when a file / element dropped on a row
     *
     * @param event
     */
    dropOnFiledrop: function (event, ui) {
        var targetRow = this.$(event.currentTarget)[0];
        event.stopPropagation();
        event.preventDefault();
        this.$(targetRow).tooltip('hide');
        this.$(targetRow).removeAttr("style");

        /*********** Main actions happening here **************/

        // get the target module and associated id where the file is dropped (eg. contact)
        var targetTokens = this.$(targetRow).attr('name').split('_');
        var shareManager = new this.ShareManager(targetTokens[0], targetTokens[1]);

        // a draggable element inside browser
        if (!event.originalEvent.dataTransfer) {

            // get the 'Notes' module and associated id
            var draggableModule = $(ui.draggable[0]).attr('name').split('_')[0];
            var draggableId = $(ui.draggable[0]).attr('name').split('_')[1];
            var result = shareManager.linkModels(draggableModule, draggableId);
            if (result) {
                var successTitle = '<p style="font-size: 16px; text-align: center;">You have shared with' + $(targetRow).text() + '.  <a id="undo-link"><strong>Undo</strong></a></p>'
                shareManager.alertViews.shareSuccess(shareManager, successTitle, '', null,
                    {undo: true, draggableModule: draggableModule, draggableId: draggableId, targetModule: shareManager.targetModule, targetId: shareManager.targetId});
            }

         // a file from local desktop
        } else {

            var file = event.originalEvent.dataTransfer.files[0]; // grab the file
            var newNoteId = shareManager.createNote();

            if (newNoteId) {
                // the first manager is to link target (eg. contact) with a note+attachment
                if (shareManager.isValidFile(file) && shareManager.uploadFileToNote(newNoteId, file) && shareManager.linkModels('Notes', newNoteId)) {

                    // this 2nd share manager is to link current main model (eg. account) with note+attachment (subject to change depending on specs)
                    // alert error view will not be raised if fail this link action
                    var shareManager2 = new this.ShareManager(app.controller.context.attributes.module, app.controller.context.attributes.modelId);
                    if (shareManager2.linkModels('Notes', newNoteId)) {
                        shareManager.addNewFileView();
                    }

                    var successTitle = '<p style="font-size: 16px; text-align: center;">You have shared with' + $(targetRow).text() + '.  <a id="undo-link"><strong>Undo</strong></a></p>'
                    shareManager.alertViews.shareSuccess(shareManager, successTitle, '', newNoteId,
                        {undo: true, draggableModule: 'Notes', draggableId: newNoteId, targetModule: shareManager.targetModule, targetId: shareManager.targetId});
                }
            }

        }
        /*********** End of main action *************************/
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
        }

    }
})

