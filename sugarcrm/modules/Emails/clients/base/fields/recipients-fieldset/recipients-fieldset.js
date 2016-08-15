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
 * Recipients field group for handling expand to edit
 *
 * @class View.Fields.Base.Emails.RecipientsFieldsetField
 * @alias SUGAR.App.view.fields.BaseEmailsRecipientsFieldsetField
 * @extends View.Fields.Base.FieldsetField
 */
({
    extendsFrom: 'FieldsetField',

    FIELDSET_FIELD_SELECTOR: '.fieldset-field',
    FIELDSET_GROUP_SELECTOR: '.fieldset-group',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.templateName = 'detail';
        this.addressBookState = 'closed';
        this.events = _.extend({}, this.events, {
            'click [data-toggle-field]': '_handleRecipientOptionClick',
            'click .fieldset-field': '_focus'
        });
        this.context.on('address-book-state', _.bind(function(state) {
            this.addressBookState = state;
        }, this), this);
        $(document).on('click.email-recipients', _.bind(this._blur, this));
        this.context.on('tinymce:focus', _.bind(this._blur, this), this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var to = this.model.get('to');
        var cc = this.model.get('cc');
        var bcc = this.model.get('bcc');

        // Template name is defined here rather than in _loadTemplate so that we can
        // use it as a switch to set up the template vars and invoke post render methods.
        // If no recipient field value has been set, force edit mode.
        var isRecipientSet = (to && to.length) || (cc && cc.length) || (bcc && bcc.length);
        this.templateName = isRecipientSet ? this.templateName : 'edit';

        // Construct a string representing all recipients for insertion into detail template
        if (this.templateName === 'detail') {
            this._buildRecipientsList();
        }

        this._super('_render');

        // after fieldset fields are rendered, insert the recipients options partial
        // at the end of the "from" field and toggle the display state
        // of the "bcc" and "cc" depending on if they have values or not.
        if (this.templateName === 'edit') {
            this._addRecipientOptions();
        }
    },

    /**
     * @inheritdoc
     */
    _loadTemplate: function() {
        this._super('_loadTemplate');
        // stomp on the default view state
        this.template = app.template.getField('recipients-fieldset', this.templateName, this.module);
    },

    /**
     * Event handler for fieldset on click event.
     * Toggles field view to edit and rerenders.
     */
    _focus: function(evt) {
        // we need to stop event from triggering _blur
        evt.stopPropagation();
        // ignore if already in edit mode
        if (this.templateName === 'edit') {
            return;
        }
        this.templateName = 'edit';
        this.render();
    },

    /**
     * Event handler for outside fieldset on click event.
     * Toggles field view to detail and rerenders.
     */
    _blur: function(evt) {
        // ignore toggle if the address book is open, the field is disposed or view is already in detail mode
        if (this.addressBookState === 'open' || this.disposed || this.templateName === 'detail') {
            return;
        }
        this.templateName = 'detail';
        this.render();
    },

    /**
     * Construct a string representing all recipients
     * with indicators for Cc and Bcc.
     */
    _buildRecipientsList: function() {
        this.recipients = this.fields
            .filter(function(f) {
                // reject the from field
                return f.type === 'email-recipients';
            }).map(function(f) {
                // get values from the model, not the field value
                return {
                    name: f.name,
                    values: f.format(f.model.get(f.name)).map(function(n) {
                        return n.get('name') || n.get('email_address');
                    })
                };
            }).filter(function(f) {
                // reject the empty fields
                return f.values.length;
            }).map(function(f) {
                // construct string with type indicators
                var indicator = f.name === 'cc' ? 'Cc: ' :
                    f.name === 'bcc' ? 'Bcc: ' :
                    '';
                return indicator + f.values.join(', ');
            }).join('; ');
    },

    /**
     * Add Cc/Bcc toggle buttons
     * Initialize whether to show/hide fields and toggle show/hide buttons appropriately
     */
    _addRecipientOptions: function() {
        this._renderRecipientOptions('outbound_email_id');
        this._initRecipientOption('to');
        this._initRecipientOption('cc');
        this._initRecipientOption('bcc');
    },

    /**
     * Render the sender option buttons and place them in the given fieldname
     *
     * @param {string} fieldname Name of field that will contain the sender option buttons
     * @private
     */
    _renderRecipientOptions: function(fieldname) {
        var field = this.view.getField(fieldname);
        var $field;
        var recipientOptionsTemplate;

        if (field) {
            $field = field.$el.closest(this.FIELDSET_FIELD_SELECTOR);
            recipientOptionsTemplate = app.template.getField('recipients-fieldset', 'recipient-options', this.module);
            $(recipientOptionsTemplate({'module': this.module})).appendTo($field);
        }
    },

    /**
     * Check if the given field has a value
     * Hide the field if there is no value prepopulated
     *
     * @param {string} fieldName Name of the field to initialize active state on
     * @private
     */
    _initRecipientOption: function(fieldName) {
        var fieldValue = this.model.get(fieldName) || [];

        this.toggleRecipientOption(fieldName, (fieldName === 'to' || fieldValue.length > 0));
    },

    /**
     * Event Handler for toggling the Cc/Bcc options on the page.
     *
     * @param {Event} event click event
     * @private
     */
    _handleRecipientOptionClick: function(event) {
        var $toggleButton = $(event.currentTarget);
        var fieldName = $toggleButton.data('toggle-field');

        this.toggleRecipientOption(fieldName);
    },

    /**
     * Toggle the state of the given field
     * Sets toggle button state and visibility of the field
     *
     * @param {string} fieldName Name of the field to toggle
     * @param {boolean} [active] Whether toggle button active and field shown
     */
    toggleRecipientOption: function(fieldName, active) {
        var toggleButtonSelector = '[data-toggle-field="' + fieldName + '"]';
        var $toggleButton = this.$(toggleButtonSelector);

        // if explicit active state not set, toggle to opposite
        if (_.isUndefined(active)) {
            active = !$toggleButton.hasClass('active');
        }

        $toggleButton.toggleClass('active', active);
        this._toggleFieldVisibility(fieldName, active);
    },

    /**
     * Show/hide a field section on the form
     *
     * @param {string} fieldName Name of the field to show/hide
     * @param {boolean} show Whether to show or hide the field
     * @private
     */
    _toggleFieldVisibility: function(fieldName, show) {
        var field = this.view.getField(fieldName);

        if (field) {
            field.$el.closest(this.FIELDSET_GROUP_SELECTOR).toggleClass('hide', !show);
        }

        this.context.trigger('recipients-email:resize-editor');
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(document).off('click.email-recipients');
        this._super('_dispose');
    }
})
