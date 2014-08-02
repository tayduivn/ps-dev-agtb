/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.MeetingsParticipantsField
 * @alias SUGAR.App.view.fields.BaseMeetingsParticipantsField
 * @extends View.Field
 */
({
    fieldTag: 'input.select2',

    plugins: ['EllipsisInline', 'SearchForMore', 'Tooltip'],

    events: {
        'click button[data-action=addRow]': 'addRow',
        'click button[data-action=removeRow]:not(.disabled)': 'removeRow',
        'click button[data-action=previewRow]:not(.disabled)': 'previewRow'
    },

    placeholder: 'LBL_SEARCH_SELECT',

    /**
     * @inheritdoc
     *
     * View.Fields.Base.MeetingsParticipantsField#placeholder can be overridden
     * via options.
     *
     * Adds a delay to the View.Fields.Base.MeetingsParticipantsField#addRow,
     * View.Fields.Base.MeetingsParticipantsField#removeRow,
     * View.Fields.Base.MeetingsParticipantsField#previewRow, and
     * View.Fields.Base.MeetingsParticipantsField#search methods so that these
     * event handlers do not execute too frequently.
     *
     * Initializes the participants collection on the {@link Bean model} and
     * fetches the collection if the model is not new.
     *
     * The current user is added to the collection if the model is new. The
     * delta for this model is set to 0 because the server will automatically
     * link the current user anyway. Setting the delta to 0 allows
     * {@link Bean#revertAttributes} and {@link Bean#changedAttributes} to
     * behave as if this initialization did not dirty the collection.
     */
    initialize: function(options) {
        var currentUser;

        this._super('initialize', [options]);

        // translate the placeholder
        this.placeholder = app.lang.get(this.def.placeholder || this.placeholder, this.module);

        this.addRow = _.debounce(this.addRow, 200);
        this.removeRow = _.debounce(this.removeRow, 200);
        this.previewRow = _.debounce(this.previewRow, 200);
        this.search = _.debounce(this.search, app.config.requiredElapsed || 500);

        this.model.trigger('collection:initialize', this.name, {modules: this.def.module_list});

        if (this.model.isNew()) {
            currentUser = app.data.createBean('Users', {id: app.user.id});
            currentUser.once('sync', function(model) {
                try {
                    model.set('delta', 0);
                    this.getFieldValue().add(model);
                } catch (e) {
                    app.logger.warn(e);
                }
            }, this);
            currentUser.fetch();
        } else {
            try {
                this.getFieldValue().fetch();
            } catch (e) {
                app.logger.warn(e);
            }
        }
    },

    /**
     * @inheritdoc
     */
    getFieldElement: function() {
        return this.$(this.fieldTag);
    },

    /**
     * Returns the collection stored for this field.
     *
     * @throws An exception when the value is not a collection
     * @return {LinkField}
     */
    getFieldValue: function() {
        var value = this.model.get(this.name);

        if (!(value instanceof app.BeanCollection)) {
            throw 'the value must be a BeanCollection';
        }

        return value;
    },

    /**
     * Overrides Field#bindDataChange to render the field anytime the
     * collection is changed.
     */
    bindDataChange: function() {
        this.model.on('change:' + this.name, this.render, this);
    },

    /**
     * Overrides Field#bindDomChange to add the selected record to the
     * collection.
     */
    bindDomChange: function() {
        var onChange = _.bind(function(event) {
            try {
                this.getFieldValue().add(event.added.attributes);
            } catch (e) {
                app.logger.warn(e);
            }
        }, this);

        this.getFieldElement().on('change', onChange);
    },

    /**
     * @inheritdoc
     *
     * Destroys the Select2 element.
     */
    unbindDom: function() {
        this._super('unbindDom');
        this.getFieldElement().select2('destroy');
    },

    /**
     * @inheritdoc
     *
     * Renders the select2 widget and hides it so it is not shown by default.
     *
     * @chainable
     * @private
     */
    _render: function() {
        var $el;

        this._super('_render');

        $el = this.getFieldElement();
        $el.select2({
            allowClear: false,
            formatInputTooShort: '',
            formatSearching: app.lang.get('LBL_LOADING', this.module),
            minimumInputLength: 1,
            query: _.bind(this.search, this),
            selectOnBlur: false
        });
        this.addSearchForMoreButton($el);

        this.$('[name=newRow]').hide();

        return this;
    },

    /**
     * @inheritdoc
     *
     * Converts the models found in the collection to ones that can be used in
     * the templates.
     *
     * @param {LinkField} value
     * @return {Object} Array of models with view properties defined
     * @return {String} return.Object.accept_status The translated string
     * indicating the model's meeting status
     * @return {String} return.Object.accept_class The CSS class representing
     * the model's meeting status per Twitter Bootstrap's label component
     * @return {String} return.Object.avatar The URL where the model's avatar
     * can be downloaded or undefined if one does not exist
     * @return {Boolean} return.Object.deletable Whether or not the model can
     * be removed from the collection
     * @return {Boolean} return.Object.last Whether or not the model is the
     * last one in the collection
     * @return {Boolean} return.Object.preview.enabled Whether or not preview
     * is enabled for the model
     * @return {String} return.Object.preview.label The tooltip to be shown for
     * the model when hovering over the preview button
     */
    format: function(value) {
        var acceptStatus, deletable, i, participants, preview, rows, self;

        self = this;

        acceptStatus = function(participant) {
            var status = {};

            switch (participant.get('accept_status_meetings')) {
                case 'accept':
                    status.label = 'LBL_RESPONSE_ACCEPT';
                    status.css_class = 'success';
                    break;
                case 'decline':
                    status.label = 'LBL_RESPONSE_DECLINE';
                    status.css_class = 'important';
                    break;
                case 'tentative':
                    status.label = 'LBL_RESPONSE_TENTATIVE';
                    status.css_class = 'warning';
                    break;
                default:
                    status.label = 'LBL_RESPONSE_NONE';
                    status.css_class = '';
            }

            return status;
        };

        deletable = function(participant) {
            var undeletable = [
                app.user.id,
                self.model.get('assigned_user_id')
            ];

            return !_.contains(undeletable, participant.id);
        };

        preview = function(participant) {
            var isBwc, moduleMetadata, preview;

            isBwc = false;
            preview = {
                enabled: true,
                label: 'LBL_PREVIEW'
            };

            moduleMetadata = app.metadata.getModule(participant.module);
            if (moduleMetadata) {
                isBwc = moduleMetadata.isBwcEnabled;
            }

            if (isBwc) {
                preview.enabled = false;
                preview.label = 'LBL_PREVIEW_BWC_TOOLTIP';
            } else if (_.isEmpty(participant.module) || _.isEmpty(participant.id)) {
                preview.enabled = false;
                preview.label = 'LBL_PREVIEW_DISABLED_NO_RECORD';
            } else if (!app.acl.hasAccess('view', participant.module)) {
                preview.enabled = false;
                preview.label = 'LBL_PREVIEW_DISABLED_NO_ACCESS';
            }

            return preview;
        };

        try {
            participants = this.getFieldValue().filter(function(participant) {
                return participant.get('delta') > -1;
            });

            i = 1;
            rows = participants.length;
            participants = participants.map(function(participant) {
                var attributes;

                attributes = {
                    accept_status: acceptStatus(participant),
                    deletable: deletable(participant),
                    last: (rows === i++),
                    preview: preview(participant)
                };

                if (!_.isEmpty(participant.get('picture'))) {
                    attributes.avatar = app.api.buildFileURL({
                        module: participant.module,
                        id: participant.id,
                        field: 'picture'
                    });
                }

                return _.extend(attributes, participant.attributes);
            });
        } catch (e) {
            app.logger.warn(e);
            participants = [];
        }

        return participants;
    },

    /**
     * Displays the search and select to add a new participant.
     *
     * Hides the [+] button.
     *
     * @param {Event} event
     */
    addRow: function(event) {
        this.$('[name=newRow]').css('display', 'table');
        $(event.currentTarget).hide();
    },

    /**
     * Removes the row where the [-] button was clicked.
     *
     * The participant is removed from the collection if it is an participant
     * row. Otherwise, the search and select row is hidden and the [+] is shown
     * again.
     *
     * @param {Event} event
     */
    removeRow: function(event) {
        var id = $(event.currentTarget).data('id');

        if (id) {
            try {
                this.getFieldValue().remove(id);
            } catch (e) {
                app.logger.warn(e);
            }
        } else {
            this.$('[name=newRow]').hide();
            this.$('button[data-action=addRow]').show();
        }
    },

    /**
     * Shows or hides the preview of the participant.
     *
     * @param {Event} event
     */
    previewRow: function(event) {
        var data, model, success;

        success = _.bind(function(model) {
            model.module = data.module;
            app.events.trigger('preview:render', model);
        }, this);

        data = $(event.currentTarget).data();
        if (data && data.module && data.id) {
            model = app.data.createBean(data.module, {id: data.id});
            model.fetch({
                showAlerts: true,
                success: success
            });
        }
    },

    /**
     * Searches for more participants that match the query.
     *
     * Matches that already exist in the collection are suppressed. See
     * [Select2](http://ivaynberg.github.io/select2/) for documentation on
     * using the query function.
     *
     * The search is limited to ten results and pagination is disabled.
     *
     * @param {Object} query
     * @param {String} query.term The search term
     * @param {Function} query.callback The callback where data should be
     * passed once it has been loaded
     */
    search: function(query) {
        var data, participants, success;

        data = {
            results: [],
            more: false
        };

        success = function(result) {
            result.each(function(record) {
                var participant = participants.get(record.id);

                if (participant && participant.get('delta') > -1) {
                    app.logger.debug(record.module + '/' + record.id + ' is already in the collection');
                } else {
                    data.results.push({
                        id: record.id,
                        text: record.get('name'),
                        attributes: record.attributes
                    });
                }
            });
        };

        try {
            participants = this.getFieldValue();
            participants.search({
                limit: 10,
                query: query.term,
                success: success,
                complete: function() {
                    query.callback(data);
                }
            });
        } catch (e) {
            app.logger.warn(e);
            query.callback(data);
        }
    }
})
