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
 * @class View.Fields.Base.NotificationCenterAddressMultiselectField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterAddressMultiselectField
 * @extends View.Fields.Base.NotificationCenterAddressBaseField
 */
({
    extendsFrom: 'NotificationCenterAddressBaseField',

    /**
     * @property {string} Field selector.
      */
    fieldTag: 'input.select2',

    /**
     * @property {Object} attached event list.
     */
    events: {
        'click .btn[name=add]': 'addItem',
        'click .btn[name=remove]': 'removeItem'
    },

    /**
     * @property {string} selector for addresses values field
     */
    appendAddressTag: 'input[name=append_address]',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._currentIndex = 0;
    },

    /**
     * We are dealing with specially formatted model's helper-attribute 'selectedAddresses'.
     * So let's form this.value from that format.
     * @inheritdoc
     */
    getFormattedValue: function() {
        var value = null;
        if (this.model.get('selectedAddresses')[this.carrier]) {
            value = [];
            _.each(this.model.get('selectedAddresses')[this.carrier], function(val, key) {
                value.push({
                    id: val,
                    key: key
                });
            });
        }
        return this.format(value);
    },

    /**
     * Called to update value when a selection is made from options view dialog
     * @param {Object} model New value for address
     */
    setValue: function(model) {
        if (!model) {
            return;
        }
        var index = this._currentIndex;
        var address = this.value;
        address[index || 0].id = model.id;
        this._updateModelAndTriggerChange(address);
    },

    /**
     * @inheritdoc
     */
    format: function(value) {
        value = app.utils.deepCopy(value);
        // Place the add button as needed
        if (_.isArray(value) && value.length > 0) {
            _.each(value, function(address) {
                delete address.remove_button;
                delete address.add_button;
            });
            if (!value[this._currentIndex]) {
                value[this._currentIndex] = {};
            }
            value[value.length - 1].add_button = true;
            // number of valid addresses
            var numAddresses = _.filter(value, function(address) {
                return !_.isUndefined(address.id);
            }).length;
            // Show remove button for all unset combos and only set combos if there are more than one
            _.each(value, function(address) {
                if (_.isUndefined(address.id) || numAddresses > 1) {
                    address.remove_button = true;
                }
            });
        }
        return value;
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var self = this;
        if (!this.items || _.isEmpty(this.items)) {
            return;
        }

        var optionsKeys = _.isObject(this.items) ? _.keys(this.items) : [];

        this._super('_render');

        var select2Options = this.getSelect2Options(optionsKeys);
        var $el = this.$(this.fieldTag);

        $el.select2(select2Options)
            .on('change', function(e) {
                var attributes = {};
                var id = e.val;
                if (_.isUndefined(id)) {
                    return;
                }
                attributes.id = id;
                self.setValue(attributes);
            });

        var plugin = $el.data('select2');

        if (plugin && this.dir) {
            plugin.container.attr('dir', this.dir);
            plugin.results.attr('dir', this.dir);
        }

        //Setup selected value in Select2 widget
        if (!_.isUndefined(this.value)) {
            // To make pills load properly when autoselecting a string val
            // from a list val needs to be an array
            if (!_.isArray(this.value)) {
                this.value = [this.value];
            }

            this.$(this.fieldTag).each(function(index, el) {
                var plugin = $(el).data('select2');
                // If there is a plugin but no address index, set it
                if (!_.isUndefined(plugin) && _.isUndefined(plugin.setAddressIndex)) {
                    plugin.setAddressIndex = function() {
                        self._currentIndex = $(this).data('index');
                    };
                    plugin.opts.element.on('select2-open', plugin.setAddressIndex);
                }
            });
        }
    },

    /**
     * Set up select2 options and properties.
     * @param {Object} optionsKeys keys of items of the select.
     * @return {Object} options of select2 plugin. For detailed information see corresponding section of select2 docs.
     */
    getSelect2Options: function(optionsKeys) {
        var select2Options = {};
        select2Options.allowClear = _.indexOf(optionsKeys, '') >= 0;
        select2Options.transformVal = _.identity;

        select2Options.width = this.def.enum_width ? this.def.enum_width : '100%';
        select2Options.placeholder = app.lang.get('LBL_SELECT_SEND_ADDRESS', this.module);

        select2Options.minimumResultsForSearch = this.def.searchBarThreshold ? this.def.searchBarThreshold : 7;

        select2Options.initSelection = _.bind(this._initSelection, this);
        select2Options.query = _.bind(this._query, this);

        return select2Options;
    },

    /**
     * Callback for select2 `initSelection` property.
     *
     * @param {HTMLElement} el The select2 element that stores values.
     * @param {Function} callback select2 callback to initialize the plugin.
     * @private
     */
    _initSelection: function(el, callback) {
        var $el = $(el);
        var id = $el.val();
        var text = this.items[id];

        return callback({id: id, text: text});
    },

    /**
     * Adapted from eachOptions helper in hbt-helpers.js
     * Select2 callback used for loading the Select2 widget option list
     * @param {Object} query Select2 query object
     * @private
     */
    _query: function(query) {
        var options = _.isString(this.items) ? app.lang.getAppListStrings(this.items) : this.items;
        var data = {
            results: [],
            // only show one "page" of results
            more: false
        };
        if (_.isObject(options)) {
            _.each(options, function(element, index) {
                var text = '' + element;
                //additionally filter results based on query term
                if (query.matcher(query.term, text)) {
                    data.results.push({id: index, text: text});
                }
            });
        } else {
            options = null;
        }
        query.callback(data);
    },

    /**
     * Add item to list.
     * @param {Event} evt DOM event.
     */
    addItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');
        if (!index || this.value[index].id) {
            this.value.push({id: ''});
            this._currentIndex += 1;
            this._updateModelAndTriggerChange(this.value);
        }
    }, 0),

    /**
     * Remove item from list.
     * @param {Event} evt DOM event.
     */
    removeItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');
        if (_.isNumber(index)) {
            // Do not remove the last item.
            if (index === 0 && this.value.length === 1) {
                return;
            }
            if (this._currentIndex === this.value.length - 1) {
                this._currentIndex -= 1;
            }
            this.value.splice(index, 1);
            this._updateModelAndTriggerChange(this.value);
        }
    }, 0),

    /**
     * Set value to model and render the field.
     * @param {Object} value
     * @private
     */
    _updateModelAndTriggerChange: function(value) {
        var valueForModel = [];
        _.each(value, function(item) {
            valueForModel.push(item.id);
        });
        this.setSelectedAddresses(valueForModel);
        this.render();
    }
});
