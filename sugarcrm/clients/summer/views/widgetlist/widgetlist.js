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

    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        var self = this;
        app.view.View.prototype._renderHtml.call(this);

        // Dashboard layout injects shared context with limit: 5.
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;

        // prevent the whole site from unexpecting behavior
        $(document).on('drop', function (event) {
            event.stopPropagation();
            event.preventDefault();
        });

        this.makeDraggableElements();

        // define droppable element for contacts
        this.makeDroppableElements();

        $('.filedrop').data('filehover', '0');
        // make elements file droppable
        $('.filedrop').on('dragover', self.dragOverFiledrop);

        // remove style form file droppable elements
        $('.filedrop').on('dragleave', self.dragLeaveFiledrop);

        // make action for droppable elements
        $('.filedrop').on('drop', self.dropOnFiledrop);

    },


    /**
     *
     * @param event
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
     * @param event
     */
    makeDroppableElements: function () {
        $('.droppable').droppable({
            accept: '.draggable',
            over: function(event, ui) {
                event.stopPropagation();
                event.preventDefault();
                $(this.children[0]).css({"border": "2px dashed blue", "background": "#C0C0C0"});
                $(this).tooltip({title: 'Share with', trigger: 'manual', placement: 'left'});
                $(this).tooltip('show');
            },
            out: function(event, ui) {
                $(this).tooltip('hide');
                $(this.children[0]).removeAttr("style");
            }
        });
    },


    /**
     * Handle drag over if dragging element is the file from local desktop
     * @param event
     */
    dragOverFiledrop: function (event) {
        $(this).tooltip({title: 'Share with', trigger: 'manual', placement: 'left'});
        event.stopPropagation();
        event.preventDefault();
        if ($(this).data('filehover') == '0') { // track mouse dragging file hovering
            $(this).tooltip('show');
            $(this).data('filehover', '1');
        }
        $(this.children[0]).css({"border": "2px dashed blue", "background": "#C0C0C0"});
        event.originalEvent.dataTransfer.dropEffect = 'copy';
    },


    /**
     * handle drag leave if dragging elements from local desktop leave
     * @param event
     */
    dragLeaveFiledrop: function (event) {
        event.stopPropagation();
        event.preventDefault();
        if ($(this).data('filehover') == '1') {
            $(this).tooltip('hide');
            $(this).data('filehover', '0');
        }
        $(this.children[0]).removeAttr("style");
    },


    /**
     *
     * @param event
     */
    dropOnFiledrop: function (event, ui) {
        event.stopPropagation();
        event.preventDefault();
        $(this).tooltip('hide');
        $(this.children[0]).removeAttr("style");


        var ShareManager = function (targetModule, targetId) {

            var self = this;

            this.targetModule = targetModule;

            this.targetId = targetId;

            this.targetBean = app.data.createBean(this.targetModule, {id: this.targetId});
        }

        /**
         *
         * @type {Object}
         */
        ShareManager.alertViews = {

            // alert sharing sucess view
            shareSuccess: function (title, msg, params) {

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

                App.alert.show('shareSuccess', {
                    level: "success",
                    title: title,
                    messages: [msg],
                    autoClose: true
                });

                // enable undo option (delete all the relationships)
                if (params.undo) {
                    console.log('undoing');
                    $('#undo-link').css('cursor', 'pointer');
                    $('#undo-link').on('click', function (event) {

                        //TODO unlink relationship, new api might eventually implemented (see sugarapi.js)
                        App.api.call('delete', '../rest/v10/' + targetModule + '/' + targetId + '/link/' + draggableModule.toLowerCase() + '/' + draggableId , null, null, null);

                        if (ShareManager.newNoteId) {
                            App.api.call('delete', '../rest/v10/Notes/' + ShareManager.newNoteId, null, null, null);
                            ShareManager.removeNewFileView();
                        }
                        $('.close').click();
                    });
                }

            },

            // error alert view
            shareError: function (title, msg) {
                App.alert.show('shareError', {
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
        ShareManager.prototype.linkModels = function (relatedModule, relatedId) {
            var self = this;
            this.targetBean.fetch({
                success: function (model) {
                    var _relatedBean = app.data.createRelatedBean(model, null, relatedModule.toLowerCase(), {id: relatedId});
                    _relatedBean.save(null, {relate: true});
                },
                error: function (msg) {
                    ShareManager.quitFlag = 1;
                    ShareManager.alertViews.shareError('Share Failed', 'cannot share relationship');
                }
            });
            return ShareManager.quitFlag ? false : true;
        };

        /**
         * create new note
         *
         * @return {String} new note id, otherwise empty string
         */
        ShareManager.prototype.createNote = function () {
            console.log('create new note');
            var self = this;
            App.api.call('create', '../rest/v10/Notes', null, {
                success : function (result) {
                    ShareManager.newNoteId = result.id;
                },
                error: function (msg) {
                    ShareManager.quitFlag = 1;
                    ShareManager.alertViews.shareError('Share Failed', 'cannot create a new note');
                }
            }, {async: false});
            return ShareManager.newNoteId;
        };

        /**
         * upload a file to a note
         *
         * @param {String} noteId - note id
         * @param {File} file - file to upload (if dragged from browser, obtain with event.originalEvent.dataTransfer[0]
         * @return {Boolean} true if success, false otherwise
         */
        ShareManager.prototype.uploadFileToNote = function (noteId, file) {
            var self = this;
            ShareManager.filename = file.name || file.fileName;

            //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
            App.api.uploadFileHtml5('create', {
                module: 'Notes',
                id: ShareManager.newNoteId || noteId,
                field: 'filename'
            },
            file, {
                success: function(o) {
                    console.log(o);
                },
                error: function () {
                    ShareManager.quitFlag = true;
                    ShareManager.alertViews.shareError('Share Failed', 'cannot create upload a file to note');
                }
            }, {async: false});
            return ShareManager.quitFlag ? false : true;
        };


        /**
         *
         */
        ShareManager.addNewFileView = function () {
            ShareManager.addFileView = '';
            ShareManager.addedFileView = '<tr name="Notes_"' + ShareManager.newNoteId + '" class="draggable ui-draggable">';
            ShareManager.addedFileView += '<td>' + ShareManager.filename + '</td></tr>';
            $(ShareManager.attachments_table).append(ShareManager.addedFileView);
        };

        /**
         *
         */
        ShareManager.removeNewFileView = function () {
            console.log('delete');
            $(ShareManager.attachments_table).children("tr:last").remove();
        };

        ShareManager.attachments_table = $('#attachments_table').find('tbody')[0];



        /*********** Main actions happening here **************/
        var targetTokens = $(this).attr('name').split('_');
        var shareManager = new ShareManager(targetTokens[0], targetTokens[1]);
        console.log(this);

        // a draggable element inside browser
        if (!event.originalEvent.dataTransfer) {

            var draggableModule = $(ui.draggable[0]).attr('name').split('_')[0];
            var draggableId = $(ui.draggable[0]).attr('name').split('_')[1];
            var result = shareManager.linkModels(draggableModule, draggableId);

         // a file from local desktop
        } else {

            var file = event.originalEvent.dataTransfer.files[0]; // grab the file
            var newNoteId = shareManager.createNote();
            if (newNoteId) {
                if (shareManager.uploadFileToNote(newNoteId, file) && shareManager.linkModels('Notes', newNoteId)) {
                    var shareManager2 = new ShareManager(app.controller.context.attributes.module, app.controller.context.attributes.modelId);
                    if (shareManager2.linkModels('Notes', newNoteId)) {
                        ShareManager.addNewFileView();
                    }
                    ShareManager.alertViews.shareSuccess('<p style="font-size: 16px; text-align: center;">You have shared with' + $(this).text() + '.  <a id="undo-link"><strong>Undo</strong></a></p>', '',
                        {undo: true, draggableModule: 'Notes', draggableId: newNoteId, targetModule: shareManager.targetModule, targetId: shareManager.targetId});
                }
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

    /**
     *
     */
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
        if (this.model) {
            this.model.on("change", this.render, this);
        }
    }
})

