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
 * @class View.Fields.Base.Emails.EmailRecipientsField
 * @alias SUGAR.App.view.fields.BaseEmailsEmailRecipientsField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * @inheritdoc
     *
     * This field doesn't support `showNoData`.
     */
    showNoData: false,

    /**
     * The selector for accessing the Select2 field when in edit mode. The
     * Select2 field is where the recipients are displayed.
     *
     * @property {string}
     */
    fieldTag: 'input.select2',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var plugins = [
            'CollectionFieldLoadAll',
            'EmailParticipants',
            'DragdropSelect2',
            'ListEditable'
        ];

        this.plugins = _.union(this.plugins || [], plugins);
        this.events = _.extend({}, this.events, {
            'click .btn': '_showAddressBook'
        });
        this._super('initialize', [options]);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        if (this.model) {
            // Avoids a full re-rendering when editing. The current value of
            // the field is formatted and passed directly to Select2 when in
            // edit mode.
            this.listenTo(this.model, 'change:' + this.name, _.bind(function() {
                var $el = this.$(this.fieldTag);

                if (_.isEmpty($el.data('select2'))) {
                    this.render();
                } else {
                    $el.select2('data', this.getFormattedValue());
                    this._decorateInvalidRecipients();
                    this._enableDragDrop();
                }
            }, this));
        }
    },

    /**
     * @inheritdoc
     */
    bindDomChange: function() {
        var $el = this.$(this.fieldTag);

        $el.on('select2-selecting', _.bind(function(event) {
            var isDuplicate = !!this.model.get(this.name).get(event.choice);

            if (this.disposed || !this.hasLink(event.choice.get('_link')) || isDuplicate) {
                event.preventDefault();
            }
        }, this));

        $el.on('change', _.bind(function(event) {
            var collection;

            if (this.model && !this.disposed) {
                collection = this.model.get(this.name);

                if (!_.isEmpty(event.added)) {
                    collection.add(event.added, {merge: true});
                }

                if (!_.isEmpty(event.removed)) {
                    collection.remove(event.removed);
                }
            }
        }, this));
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
     */
    _render: function() {
        var $el;
        var options;

        this._super('_render');

        $el = this.$(this.fieldTag);

        if ($el.length > 0) {
            options = this.getSelect2Options();
            options = _.extend(options, {
                allowClear: true,
                multiple: true,
                containerCssClass: 'select2-choices-pills-close',

                /**
                 * Constructs a representation for a selected recipient to be
                 * displayed in the field.
                 *
                 * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
                 *
                 * @param {Data.Bean} recipient
                 * @return {string}
                 * @private
                 */
                formatSelection: _.bind(function(recipient) {
                    var template = app.template.getField(this.type, 'select2-selection', this.module);

                    return template({
                        cid: recipient.cid,
                        name: recipient.name || recipient.email_address,
                        email_address: recipient.email_address,
                        invalid: !app.utils.isValidEmailAddress(recipient.email_address)
                    });
                }, this),

                /**
                 * Constructs a representation for the recipient to be
                 * displayed in the dropdown options after a query.
                 *
                 * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
                 *
                 * @param {Data.Bean} recipient
                 * @return {string}
                 */
                formatResult: _.bind(function(recipient) {
                    var template = app.template.getField(this.type, 'select2-result', this.module);
                    var value = recipient.email_address;

                    if (recipient.name) {
                        value = '"' + recipient.name + '" <' + recipient.email_address + '>';
                    }

                    return template({
                        value: value,
                        module: recipient.module
                    });
                }, this),

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
            $el.select2(options).select2('val', []);

            if (this.isDisabled()) {
                $el.select2('disable');
            }

            this._decorateInvalidRecipients();
            this._enableDragDrop();
        }
    },

    /**
     * @inheritdoc
     * @return {Array}
     */
    format: function(value) {
        // Reset the tooltip.
        this.tooltip = '';

        if (value instanceof app.BeanCollection) {
            value = value.map(this.prepareModel, this);
            this.tooltip = _.map(value, this.formatForHeader).join(', ');
        }

        return value;
    },

    /**
     * Decorate any invalid recipients.
     *
     * @private
     */
    _decorateInvalidRecipients: function() {
        var self = this;
        var $invalidRecipients = this.$('.select2-search-choice [data-invalid="true"]');

        $invalidRecipients.each(function() {
            var $choice = $(this).closest('.select2-search-choice');
            $choice.addClass('select2-choice-danger');
            $(this).attr('data-title', app.lang.get('ERR_INVALID_EMAIL_ADDRESS', self.module));
        });
    },

    /**
     * Enable the user to drag and drop recipients between recipient fields.
     *
     * @private
     */
    _enableDragDrop: function() {
        var $el = this.$(this.fieldTag);

        if (!this.def.readonly) {
            this.setDragDropPluginEvents($el);
        }
    },

    /**
     * When in edit mode, the field includes an icon button for opening an
     * address book. Clicking the button will trigger an event to open the
     * address book, which calls this method does. The selected recipients are
     * added to this field upon closing the address book.
     *
     * @private
     */
    _showAddressBook: function() {
        app.drawer.open(
            {
                layout: 'compose-addressbook',
                context: {
                    module: 'Emails',
                    mixed: true
                }
            },
            _.bind(function(recipients) {
                if (recipients && recipients.length > 0) {
                    recipients.each(function(recipient) {
                        recipient.set('email_address', recipient.get('email'));
                        recipient.unset('email');
                    });
                    recipients = this.format(recipients);
                    this.model.get(this.name).add(recipients, {merge: true});
                }

                this.view.trigger('address-book-state', 'closed');
            }, this)
        );

        this.view.trigger('address-book-state', 'open');
    }
})
