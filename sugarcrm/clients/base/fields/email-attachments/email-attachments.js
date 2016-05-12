/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.EmailAttachmentsField
 * @alias SUGAR.App.view.fields.BaseEmailAttachmentsField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * The selector for accessing the Select2 field when in edit mode. The
     * Select2 field is where the attachments are displayed.
     *
     * @property {string}
     */
    fieldTag: 'input.select2',

    /**
     * The selector for accessing the file input field when in edit mode.
     *
     * @property {string}
     */
    _fileTag: 'input[type=file]',

    /**
     * Notes records that will be linked or unlinked as attachments of an
     * email. Models with `_action: create` will be linked as attachments.
     * Models with `_action: delete` are existing attachments that will be
     * unlinked. Models with `_action: placeholder` are shown in the UI while
     * the user waits for an attachment to be added asynchronously. Once the
     * real model is ready to be added, the placeholder model is removed.
     *
     * @property {Backbone.Collection}
     */
    _attachments: null,

    /**
     * Each placeholder model is assigned a unique number that can be used to
     * determine which request is associated with that placeholder.
     *
     * @property {number}
     */
    _placeholders: 0,

    /**
     * Keeps track of active requests so that they can be aborted if the user
     * cancels an action. The key of a request is the unique number of the
     * placeholder associated with that request, so that requests can be
     * singled out by their placeholder and removed from this object without
     * affecting other active requests.
     *
     * @property {Object}
     */
    _requests: null,

    /**
     * @inheritdoc
     *
     * Adds events for uploading a file when the file input changes and
     * downloading a file when a file link is clicked in detail mode.
     *
     * Adds listeners for the `email_attachments:file:pick`,
     * `email_attachments:document:pick`, and `email_attachments:template:add`
     * events that are triggered on the view to add attachments.
     * `email_attachments:file:pick` will launch the file picker dialog.
     * `email_attachments:document:pick` will launch a drawer for selecting a
     * Document. `email_attachments:template:add` will fetch the attachments
     * from a template, so that they can be copied to the email.
     *
     * Kicks off a fetch of the existing attachments when the model is not new.
     */
    initialize: function(options) {
        var events = {};

        events['change ' + this._fileTag] = '_uploadFile';
        events['click [data-action=download]'] = '_downloadFile';
        this.events = _.extend({}, this.events, options.def.events, events);

        this._super('initialize', [options]);

        // Must wrap listenTo callbacks in anonymous functions for stubbing.
        // https://stackoverflow.com/q/23823889
        this.listenTo(this.view, 'email_attachments:file:pick', function() {
            this._openFilePicker();
        });
        this.listenTo(this.view, 'email_attachments:document:pick', function() {
            this._openDocumentPicker();
        });
        this.listenTo(this.view, 'email_attachments:template:add', function(template) {
            this._fetchTemplateAttachments(template);
        });

        this._requests = {};
        this._attachments = new Backbone.Collection();

        if (!this.model.isNew()) {
            this._fetchExistingAttachments();
        }
    },

    /**
     * @inheritdoc
     *
     * Updates the model with the latest value when changes are made to
     * `_attachments`.
     */
    bindDataChange: function() {
        /**
         * Calculates the value for the model, which contains the attachments
         * to be linked and unlinked on save.
         *
         * @return {Object}
         */
        var value = _.bind(function() {
            var value = {};
            var link = this._attachments.where({_action: 'create'});
            var unlink = this._attachments.where({_action: 'delete'});

            if (link.length > 0) {
                value.create = _.map(link, function(attachment) {
                    return _.pick(attachment.attributes, '_file', 'name', 'filename', 'file_mime_type', 'file_source');
                });
            }

            if (unlink.length > 0) {
                value.delete = _.pluck(unlink, 'id');
            }

            return value;
        }, this);

        if (this.model) {
            this.listenTo(this.model, 'change:' + this.name, this._smartRender);
        }

        if (this._attachments) {
            this.listenTo(this._attachments, 'add remove reset', function() {
                this.model.set(this.name, value());
            });
        }
    },

    /**
     * @inheritdoc
     *
     * Prevents the Select2 dropdown from opening, as the Select2 field is used
     * as a container only.
     *
     * Removes an attachment when an item is removed from the Select2 field.
     */
    bindDomChange: function() {
        var $el = this.$(this.fieldTag);

        $el.on('select2-opening', function(event) {
            event.preventDefault();
        });

        $el.on('select2-removed', _.bind(function(event) {
            var add = this._attachments.reject(function(attachment) {
                return attachment.get('_file') === event.val;
            });
            var remove = this._attachments.where({_file: event.val});

            add = add.concat(this._prepareAttachmentsForRemoval(remove));
            this._attachments.reset(add, {merge: true});
        }, this));
    },

    /**
     * @inheritdoc
     *
     * Initializes Select2 when in edit mode and disables all but the delete
     * and backspace keys in the Select2 input field.
     */
    _render: function() {
        var select2Input;

        /**
         * Returns `true` when the event occurs for the delete and backspace
         * keys and `false` for all other keys.
         *
         * @param {Object} event DOM event.
         * @return {boolean}
         */
        var isDeleteKey = function(event) {
            return event.keyCode == 8 || event.keyCode == 46;
        };

        this._super('_render');

        this.$(this.fieldTag).select2({
            multiple: true,
            data: this.getFormattedValue(),
            containerCssClass: 'select2-choices-pills-close',
            containerCss: {
                width: '100%'
            },
            width: 'off',
            /**
             * Use `_file` as an choice's ID.
             *
             * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
             *
             * @param {Object} choice
             * @return {null/string/number}
             */
            id: function(choice) {
                return _.isEmpty(choice) ? null : choice._file;
            },
            /**
             * Formats an attachment object for rendering.
             *
             * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
             *
             * @param {Object} choice
             * @return {string}
             */
            formatSelection: function(choice) {
                var $selection = '<span data-id="' + choice._file + '">' + choice.name + '</span>';

                if (choice._action === 'placeholder') {
                    $selection += ' <i class="fa fa-refresh fa-spin"></i>';
                } else {
                    $selection += ' (' + app.utils.getReadableFileSize(choice.file_size) + ')';
                }

                return $selection;
            },
            /**
             * Don't escape a choice's markup since we built the HTML.
             *
             * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
             *
             * @param {string} markup
             * @return {string}
             */
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        select2Input = this.$('.select2-input');

        if (select2Input) {
            select2Input.keypress(isDeleteKey);
            select2Input.keyup(isDeleteKey);
            select2Input.keydown(isDeleteKey);
        }

        return this;
    },

    /**
     * Avoids a full re-rendering when editing. The current value of the field
     * is formatted and passed directly to Select2 when in edit mode.
     */
    _smartRender: function() {
        var $el = this.$(this.fieldTag);

        if (_.isEmpty($el.data('select2'))) {
            this.render();
        } else {
            $el.select2('data', this.getFormattedValue());
        }
    },

    /**
     * Returns the file input field.
     *
     * Used for mocking the file input field so that its value can be set
     * programmatically. Stubbing `this.$` for only the parameter
     * `this._fileTag` is not possible; it would cause `this.$` to be stubbed
     * for all calls.
     *
     * @returns {jQuery}
     * @private
     */
    _getFileInput: function() {
        return this.$(this._fileTag);
    },

    /**
     * @inheritdoc
     *
     * Select2 expects an array of objects to display. The attachments marked
     * for removal are discarded and the attributes of the remaining
     * attachments are returned.
     */
    format: function(value) {
        return this._attachments.filter(function(attachment) {
            return attachment.get('_action') !== 'delete';
        }).map(function(attachment) {
            return attachment.toJSON();
        });
    },

    /**
     * @inheritdoc
     *
     * Destroys the Select2 element.
     */
    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        this._super('unbindDom');
    },

    /**
     * @inheritdoc
     *
     * Aborts any active requests. Stops listening to events on the view.
     */
    _dispose: function() {
        _.each(this._requests, function(request) {
            if (request && request.uid) {
                app.api.abortRequest(request.uid);
            }
        });

        this._requests = null;
        this.stopListening(this.view);
        this._super('_dispose');
    },

    /**
     * Retrieves all existing attachments for an email. This is used when
     * editing or viewing an existing email.
     *
     * FIXME: This should go away once we can retrieve attachments as a part of the record.
     *
     * @private
     */
    _fetchExistingAttachments: function() {
        var def = [{
            //FIXME: email_type should be Emails
            email_id: {
                '$equals': this.model.get('id')
            }
        }];
        var notes = app.data.createBeanCollection('Notes');

        notes.fetch({
            filter: {
                filter: def
            },
            success: _.bind(this._handleExistingAttachmentsFetchSuccess, this)
        });
    },

    /**
     * Handles a successful response from the API for retrieving an email's
     * existing attachments. The models are converted to Backbone.Model objects
     * that are added to `_attachments`.
     *
     * @param {Object} data The data from a successful API response.
     * @param {Array} data.models The models retrieved from the API.
     * @private
     */
    _handleExistingAttachmentsFetchSuccess: function(data) {
        var added = [];

        if (this.disposed === true) {
            return;
        }

        _.each(data.models, function(model) {
            var urlAttributes = {
                module: 'Notes',
                id: model.get('id'),
                field: 'filename'
            };
            var urlOptions = {
                htmlJsonFormat: false,
                passOAuthToken: false,
                cleanCache: true,
                forceDownload: true
            };
            var file = new Backbone.Model({
                _url: app.api.buildFileURL(urlAttributes, urlOptions),
                _file: model.get('id'),
                name: model.get('filename'),
                filename: model.get('filename'),
                file_mime_type: model.get('file_mime_type'),
                file_source: model.get('file_source')
            });
            added.push(file);
        });

        this._attachments.reset(added, {merge: true});
    },

    /**
     * Makes a request to download the file based on the URL identified in the
     * attributes of the current target of the event.
     *
     * @param {Object} event DOM event.
     * @param {Object} event.currentTarget The current target of the event.
     * @private
     */
    _downloadFile: function(event) {
        var url;

        if (this.disposed === true) {
            return;
        }

        url = this.$(event.currentTarget).data('url');

        if (!_.isEmpty(url)) {
            app.api.fileDownload(url, {}, {iframe: this.getFieldElement()});
        }
    },

    /**
     * Launches the file picker dialog.
     *
     * @private
     */
    _openFilePicker: function() {
        if (this.disposed === true) {
            return;
        }

        this._getFileInput().click();
    },

    /**
     * Uploads the file selected from the file picker as a temporary file.
     *
     * A placeholder attachment is added to the Select2 field while the file is
     * being uploaded.
     *
     * @private
     */
    _uploadFile: function() {
        var $file = this._getFileInput();
        var ajaxParams = {
            temp: true,
            iframe: true,
            deleteIfFails: true,
            htmlJsonFormat: true
        };
        var note;
        var placeholder;
        var val = $file.val();

        if (_.isEmpty(val)) {
            return;
        }

        placeholder = this._addPlaceholderAttachment(val.split('\\').pop());

        note = app.data.createBean('Notes');
        this._requests[placeholder] = note.uploadFile('filename', $file, {
            success: _.bind(this._handleFileUploadSuccess, this),
            error: _.bind(this._handleFileUploadError, this),
            complete: _.bind(function() {
                // Clear the file input field.
                $file.val(null);
                this._handleRequestComplete(placeholder);
            }, this)
        }, ajaxParams);
    },

    /**
     * Handles a successful response from the API for uploading the file.
     *
     * The record from the response is converted to a Backbone.Model object
     * that is added to `_attachments`. An error is shown to the user if the
     * record does not have a GUID.
     *
     * @param {Object} data The data from a successful API response.
     * @param {Object} data.record The record representing the temporary Notes
     * object.
     * @param {string} data.record.id The GUID of the uploaded file.
     * @private
     */
    _handleFileUploadSuccess: function(data) {
        var file;
        var guid = data.record && data.record.id;

        if (this.disposed === true) {
            return;
        }

        if (!guid) {
            app.logger.error('Temporary file has no GUID.');
            app.alert.show('upload_error', {
                level: 'error',
                autoClose: true,
                messages: app.lang.get('ERROR_UPLOAD_FAILED')
            });
            return;
        }

        file = new Backbone.Model({
            _action: 'create',
            _url: null,
            _file: guid,
            name: data.record.filename,
            filename: data.record.filename,
            file_mime_type: data.record.file_mime_type,
            file_size: data.record.file_size,
            file_source: 'Uploaded'
        });
        this._attachments.add(file);
    },

    /**
     * Handles an error response from the API for uploading the file.
     *
     * If the error status is a 413, then an error is shown to the user
     * indicating that the error was due to exceeding the maximum filesize.
     * Otherwise, the error is handled by the framework.
     *
     * @param {HttpError} error AJAX error.
     * @private
     */
    _handleFileUploadError: function(error) {
        if (this.disposed === true) {
            return;
        }

        if (error && error.status == 413) {
            // Mark the error as having been handled so that it doesn't get
            // handled again.
            error.handled = true;
            app.alert.show(error.error, {
                level: 'error',
                autoClose: true,
                messages: app.lang.get('ERROR_MAX_FILESIZE_EXCEEDED')
            });
        }

        app.error.handleHttpError(error);
    },

    /**
     * Launches a selection drawer for choosing a Document to attach.
     *
     * @private
     */
    _openDocumentPicker: function() {
        var def = {
            layout: 'selection-list',
            context: {
                module: 'Documents'
            }
        };

        if (this.disposed === true) {
            return;
        }

        app.drawer.open(def, _.bind(this._handleDocumentSelection, this));
    },

    /**
     * Called when the Document selection drawer is closed. If a Document was
     * selected, then the Document is fetched.
     *
     * The Document must be fetched because it is unlikely that the model
     * retrieved for {@link SelectionListView} contains all of the data that is
     * needed. A placeholder attachment is added to the Select2 field while the
     * Document is being retrieved.
     *
     * @param {Object} [selection] The attributes from the selected Document.
     * @param {string} [selection.id] The ID of the selected Document.
     * @param {string} [selection.value] The name of the selected Document.
     * @private
     */
    _handleDocumentSelection: function(selection) {
        var doc;
        var placeholder;
        var placeholderName;

        if (selection) {
            // `value` is not a real attribute.
            doc = app.data.createBean('Documents', _.omit(selection, 'value'));

            placeholderName = app.utils.getRecordName(doc) || selection.value || app.lang.getModuleName(doc.module);
            placeholder = this._addPlaceholderAttachment(placeholderName);

            this._requests[placeholder] = doc.fetch({
                success: _.bind(this._handleDocumentFetchSuccess, this),
                complete: _.bind(function() {
                    this._handleRequestComplete(placeholder);
                }, this)
            });
        }
    },

    /**
     * Handles a successful response from the API for fetching the Document.
     *
     * The fetched model is converted to a Backbone.Model object that is added
     * to `_attachments`.
     *
     * @param {Object} doc The fetched record.
     * @private
     */
    _handleDocumentFetchSuccess: function(doc) {
        var file;

        if (this.disposed === true) {
            return;
        }

        file = new Backbone.Model({
            _action: 'create',
            _url: null,
            _file: doc.get('document_revision_id'),
            name: doc.get('filename'),
            filename: doc.get('filename'),
            file_mime_type: doc.get('file_mime_type'),
            file_source: 'Document'
        });
        this._attachments.add(file, {merge: true});
    },

    /**
     * Retrieves all of an email template's attachments so they can be added to
     * the email.
     *
     * A single placeholder attachment -- representing all of an email
     * template's attachments -- is added to the Select2 field while the
     * attachments are being retrieved.
     *
     * @param {Data.Bean} template The email template whose attachments are to
     * be added.
     * @private
     */
    _fetchTemplateAttachments: function(template) {
        var def;
        var notes = app.data.createBeanCollection('Notes');
        var placeholder;

        if (this.disposed === true) {
            return;
        }

        placeholder = this._placeholders++;
        def = [{
            //FIXME: email_type should be EmailTemplates
            email_id: {
                '$equals': template.get('id')
            }
        }];
        this._requests[placeholder] = notes.fetch({
            filter: {
                filter: def
            },
            success: _.bind(this._handleTemplateAttachmentsFetchSuccess, this),
            complete: _.bind(function() {
                this._handleRequestComplete(placeholder);
            }, this)
        });
    },

    /**
     * Handles a successful response from the API for retrieving an email
     * template's attachments. The models are converted to Backbone.Model
     * objects that are added to `_attachments`.
     *
     * Before adding the new attachments to `_attachments`, all existing
     * attachments that came from another email template are removed.
     *
     * @param {Object} data The data from a successful API response.
     * @param {Array} data.models The models retrieved from the API.
     * @private
     */
    _handleTemplateAttachmentsFetchSuccess: function(data) {
        var add = [];
        var existing;

        if (this.disposed === true) {
            return;
        }

        existing = this._attachments.groupBy('file_source');

        _.each(existing, function(attachments, source) {
            if (source === 'Template') {
                // Remove all existing attachments that came from an email
                // template. The returned attachments are to be merged so they
                // can be unlinked.
                add = add.concat(this._prepareAttachmentsForRemoval(attachments));
            } else {
                // Keep attachments that are not from an email template. The
                // ones that are to be removed will still be unlinked on save.
                add = add.concat(attachments);
            }
        }, this);

        // Add the attachments from the new email template.
        _.each(data.models, function(model) {
            var file = new Backbone.Model({
                _action: 'create',
                _url: null,
                _file: model.get('id'),
                name: model.get('filename'),
                filename: model.get('filename'),
                file_mime_type: model.get('file_mime_type'),
                file_size: model.get('file_size'),
                file_source: 'Template'
            });
            add.push(file);
        });

        this._attachments.reset(add, {merge: true});
    },

    /**
     * When a request completes, the request is no longer tracked in
     * `_requests` and the associated placeholder attachment is removed.
     *
     * @param {number} placeholder The unique ID for the request associated
     * with the placeholder attachment.
     * @private
     */
    _handleRequestComplete: function(placeholder) {
        delete this._requests[placeholder];
        this._removePlaceholderAttachment(placeholder);
    },

    /**
     * Prepares the specified attachments to be removed via a reset.
     *
     * Attachments with `_action: create` have not yet been linked, so they can
     * be safely removed. Attachments with `_action: placeholder` are removed
     * with {@link #_removePlaceholderAttachment}. Attachments with
     * `_action:delete` left in a state to be unlinked. Attachments without an
     * `_action` attribute are marked to be unlinked.
     *
     * @param {Array} attachments
     * @return {Array} The attachments that are to be unlinked. These must be
     * merged when `_attachments` is reset.
     * @private
     */
    _prepareAttachmentsForRemoval: function(attachments) {
        var unlink = [];

        _.each(attachments, function(attachment) {
            var action = attachment.get('_action');

            switch (action) {
                case 'delete':
                    // No change. Leave it to be unlinked.
                    unlink.push(attachment);
                    break;
                case 'placeholder':
                    this._removePlaceholderAttachment(attachment.get('_file'));
                    break;
                case 'create':
                default:
                    // Exclude this attachment from the merge and the reset
                    // operation will remove it.
                    break;
            }

            // An attachment that has already been linked does not have an
            // action. The attachment must be updated to add the "delete"
            // action so that it will be unlinked.
            if (_.isEmpty(action)) {
                attachment.set('_action', 'delete');
                unlink.push(attachment);
            }
        }, this);

        return unlink;
    },

    /**
     * Adds a placeholder attachment to the Select2 field.
     *
     * Adding a placeholder attachment does not trigger a change on the model.
     * So a {@link #_smartRender render} is forced to make the placeholder
     * appear.
     *
     * @param {string} name The display name for the placeholder attachment.
     * @return {number} A unique ID for the placeholder attachment.
     * @private
     */
    _addPlaceholderAttachment: function(name) {
        var id = this._placeholders++;
        var file = new Backbone.Model({
            _action: 'placeholder',
            _file: id,
            _url: null,
            name: name
        });

        this._attachments.add(file);
        this._smartRender();

        return id;
    },

    /**
     * Removes a placeholder attachment from the Select2 field and aborts the
     * request associated with the placeholder, if it is active.
     *
     * Removing a placeholder attachment does not trigger a change on the
     * model. So a {@link #_smartRender render} is forced to make the
     * placeholder disappear.
     *
     * @param {number} placeholder The unique ID for the placeholder
     * attachment.
     * @private
     */
    _removePlaceholderAttachment: function(placeholder) {
        var attachment = this._attachments.where({
            _action: 'placeholder',
            _file: placeholder
        });
        var request;

        if (attachment.length > 0) {
            this._attachments.remove(attachment);
            this._smartRender();
        }

        // Abort the request if it is still active.
        request = this._requests[placeholder];

        if (request && request.uid) {
            app.api.abortRequest(request.uid);
            delete this._requests[placeholder];
        }
    }
})
