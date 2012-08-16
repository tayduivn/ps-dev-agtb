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
        if ($(this).data('filehover') == '0') {
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

        // define alert views for sharing success and error
        var alertViews =  {

            // enable undo functionality for share success
            shareSuccess: function (targetModule, targetId, draggableElModule, draggableElId) {
                App.alert.show('shareSuccess', {
                    level: "success",
                    title: '<p style="font-size: 16px; text-align: center;">You have shared with someone.  <a id="undo-link"><strong>Undo</strong></a></p>',
                    autoClose: true
                });
                $('#undo-link').css('cursor', 'pointer');
                $('#undo-link').on('click', function (event) {
                    //TODO unlink relationship, new api might eventually implemented (see sugarapi.js)
                    App.api.call('delete', '../rest/v10/' + targetModule + '/' + targetId + '/link/' + draggableElModule.toLowerCase() + '/' + draggableElId , null, null, null);
                    $('.close').click();
                });
            },

            // error alert view
            shareError: function (msg) {
                App.alert.show('shareError', {
                    level: "error",
                    title: "Error",
                    messages: [msg],
                    autoClose: true
                });
            }
        };

        // a draggable element inside browser, not a file from local desktop
        if (!event.originalEvent.dataTransfer) {
            var target = this;
            var targetModule = $(target).attr('name').split('_')[0];
            var targetId = $(target).attr('name').split('_')[1];
            var draggableEl = ui.draggable[0];
            var draggableElModule = $(draggableEl).attr('name').split('_')[0];
            var draggableElId = $(draggableEl).attr('name').split('_')[1];

            // establish relationship between beans
            var targetBean = app.data.createBean(targetModule, {id: targetId});
            targetBean.fetch({
                success: function (model) {
                    try {
                        var _relatedBean = app.data.createRelatedBean(model, null, draggableElModule.toLowerCase(), {id: draggableElId});
                        _relatedBean.save(null, {relate: true});
                        alertViews.shareSuccess(targetModule, targetId, draggableElModule, draggableElId);
                    } catch (err) {
                        alertViews.shareError(err);
                    }
                },
                error: function (msg) {
                    console.log('cannot relate draggable item to target');
                    alertViews.shareError(msg);
                }
            });

         // a file from local desktop, a note will be created
        } else {
            var target = this;
            var targetModule = $(target).attr('name').split('_')[0];
            var targetId = $(target).attr('name').split('_')[1];
            var file = event.originalEvent.dataTransfer.files[0]; // grab the file
            var newNoteId = '';

            // create a brand new Note
            App.api.call('create', '../rest/v10/Notes', null, {
                success : function (result) {
                    newNoteId = result.id;
                },
                error: function (msg) {
                    console.log('cannot create new record');
                    alertViews.shareError(msg);
                }
            }, {async: false});

            if (newNoteId) {
                //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
                App.api.uploadFileHtml5('create', {
                    module: 'Notes',
                    id: newNoteId,
                    field: 'filename'
                }, file, {success: function(o) {console.log(o)}}, null);

                // establish relationship between beans
                var targetBean = app.data.createBean(targetModule, {id: targetId});
                targetBean.fetch({
                    success: function (model) {
                        try {
                            var _note = app.data.createRelatedBean(model, null, "notes", {id: newNoteId});
                            _note.save(null, {relate: true});
                            alertViews.shareSuccess(targetModule, targetId, "Notes", newNoteId);
                        } catch (err) {
                            alertViews.shareError(err);
                        }
                    },
                    error: function (msg) {
                        console.log('cannot relate note with target');
                        alertViews.shareError(msg);
                    }
                });

                // establish relationship with main current module (won't alert any views if fail or succeed)
                var mainModule = app.controller.context.attributes.module;
                var mainModelId = app.controller.context.attributes.modelId;
                var mainBean = app.data.createBean(mainModule, {id: mainModelId});
                mainBean.fetch({
                    success: function (model) {
                        var _note = app.data.createRelatedBean(model, null, 'notes', {id: newNoteId});
                        _note.save(null, {relate: true});
                    },
                    error: function (msg) {
                        console.log('cannot relate file note to main module');
                    }
                });
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

