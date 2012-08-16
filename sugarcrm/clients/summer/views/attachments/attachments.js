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
        $('.dropbox').on('drop', function (event, ui) { that.dropOnDropbox(event, ui, that) });
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
    dropOnDropbox: function (event, ui, that) {
        if (!event.originalEvent.dataTransfer) {
            return;
        }
        console.log('drop file on dropbox');

        // define alert views for sharing success and error
        var alertViews =  {

            // enable undo functionality for share success
            uploadSuccess: function () {
                App.alert.show('shareSuccess', {
                    level: "success",
                    title: '<p style="font-size: 16px; text-align: center;">You have uploaded a file.</p>',
                    autoClose: true
                });
            },

            // error alert view
            uploadError: function (msg) {
                App.alert.show('shareError', {
                    level: "error",
                    title: "Error",
                    messages: [msg],
                    autoClose: true
                });
            }
        };

        // define exit this function for callback functions
        var exit = function () {
            return false;
        }

        var file = event.originalEvent.dataTransfer.files[0]; // grab the file
        var filename = file.name;
        var attachmentList = $('#attachments_table').find('td');
        var attachmentNames = [];
        $.each(attachmentList, function (index, value) {
            attachmentNames.push($(value).text());
        });

        // check if there is already a file with same name in attachment list
        console.log(filename);
        console.log(attachmentNames);
        $.each(attachmentNames, function (index, value) {
            if (filename === value) {
                alertViews.uploadError('cannot upload file with the same name');
                exit();
            }
        });

        var newNoteId = '';

        App.api.call('create', '../rest/v10/Notes', null, {
            success : function (result) {
                newNoteId = result.id;
            },
            error: function () {
                console.log('cannot create new record');
                alertViews.uploadError('cannot create a note for this attachment');
            }
        }, {async: false});


        // if a new note is successfully created
        if (newNoteId) {
            //TODO this uploadFileHtml5 might be changed (see sugarapi.js)
            App.api.uploadFileHtml5('create', {
                module: 'Notes',
                id: newNoteId,
                field: 'filename'
            }, file, {success: function(o) {console.log(o)}}, null);

            // establish the relationship between note with this current main module
            var mainModule = app.controller.context.attributes.module;
            var mainModelId = app.controller.context.attributes.modelId;
            var mainBean = app.data.createBean(mainModule, {id: mainModelId});
            mainBean.fetch({
                success: function (model) {
                    try {
                        var _note = app.data.createRelatedBean(model, null, 'notes', {id: newNoteId});
                        _note.save(null, {relate: true});
                        alertViews.uploadSuccess();
                    } catch (err) {
                        console.log('cannnot relate note to current module');
                        alertViews.uploadError('cannot upload the file');
                    }
                },
                error: function () {
                    console.log('cannot relate file to this module');
                    alertViews.uploadError('cannot relate note to current module');
                }
            })

            var tableBody = $('#attachments_table').find('tbody')[0];
            var addedEl = '<tr name="Notes_' + newNoteId + '" class="draggable ui-draggable">';
            addedEl += '<td>' + filename + '</td></tr>';
            $(tableBody).append(addedEl);
            that.makeDraggableElements();
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

