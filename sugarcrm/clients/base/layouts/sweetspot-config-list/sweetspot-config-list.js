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
  * @class View.Layouts.Base.SweetspotConfigListLayout
  * @alias SUGAR.App.view.layouts.BaseSweetspotConfigListLayout
  * @extends View.Layout
  */
({
    events: {
        'click [data-sweetspot=add]': 'addRow'
    },

    // FIXME: Change this to 'UnsavedChanges' when SC-4167 gets merged. It won't
    // work until then, because 'Editable' can only be attached to a view.
    plugins: ['Editable'],

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._initRows();
        this._bindEvents();
    },

    /**
     * Initializes this layout by adding
     * {@link View.Views.Base.SweetspotConfigListRowView rows} of configured
     * hotkeys if they exist in user preferences.
     *
     * @protected
     * @return {undefined} Returns `undefined` if there are no configured
     *   hotkeys.
     */
    _initRows: function() {
        var data = app.user.getPreference('sweetspot');
        if (_.isEmpty(data)) {
            // Always add an empty row if we don't have anything configured.
            this.addRow();
            return;
        }

        _.each(data, function(row) {
            if (_.isArray(row.keyword)) {
                _.each(row.keyword, function(word) {
                    this._initRow(row, word);
                }, this);
            } else {
                this._initRow(row);
            }
        }, this);
    },

    /**
     * Adds a {@link View.Views.Base.SweetspotConfigListRowView row view} to the
     * layout, and sets the `keyword` and `action` attributes on the model of
     * the added row component.
     *
     * @param {Object} row The object containing row attributes.
     * @param {string} keyword The `keyword` attribute of the row.
     * @param {string} action The `action` attribute of the row.
     */
    _initRow: function(row, keyword, action) {
        action = action || row.action;
        keyword = keyword || row.keyword;

        var rowComponent = this.addRow();
        rowComponent.model.set('action', action);
        rowComponent.model.set('keyword', keyword);
    },

    /**
     * Binds the events that this layout uses.
     *
     * @protected
     */
    _bindEvents: function() {
        this.context.on('button:save_button:click', this.saveConfig, this);
        this.context.on('button:cancel_button:click', this.cancelConfig, this);
    },

    /**
     * @override
     */
    _placeComponent: function(component) {
        this.$('[data-sweetspot=actions]').append(component.el);
    },

    /**
     * Adds a {@link View.Views.Base.SweetspotConfigListRowView row view} to the
     * layout.
     */
    addRow: function() {
        var def = _.extend(
                {view: 'sweetspot-config-list-row'},
                app.metadata.getView(null, 'sweetspot-config-list-row')
            );
        var rowComponent = this.createComponentFromDef(def, this.context, this.module);

        this.addComponent(rowComponent, def);
        rowComponent.render();
        return rowComponent;
    },

    /**
     * Saves the sanitized Sweet Spot settings in user preferences and closes
     * the drawer.
     */
    saveConfig: function() {
        var data = this.collection.toJSON();
        data = this._formatData(data);

        if (_.isEmpty(data.sweetspot)) {
            this.cancelConfig();
            return;
        }

        app.alert.show('sweetspot', {
            level: 'process',
            title: app.lang.get('LBL_SAVING'),
            autoClose: false
        });

        app.user.updatePreferences(data, function(err) {
            app.alert.dismiss('sweetspot');
            if (err) {
                app.logger.error('Sweet Spot failed to update configuration preferences: ' + err);
            }
            app.drawer.close(this.collection);
            app.events.trigger('sweetspot:reset');
        });
    },

    /**
     * Closes the config drawer without saving changes.
     */
    cancelConfig: function() {
        app.drawer.close();
    },

    /**
     * Formatter method that sanitizes and prepares the data to be used by
     * {@link #saveConfig}. Also allows for multiple hotkeys to be associated
     * with a single action.
     *
     * @protected
     * @param {Array} data The unsanitized configuration data.
     * @return {Array} The formatted data.
     */
    _formatData: function(data) {
        var result = this._sanitizeConfig(data);
        result = this._joinMultipleKeywordConfigs(result);
        result = this._formatForUserPrefs(result);

        return result;
    },

    /**
     * This is a helper function that takes in the sanitized configuration data
     * and analyzes if there are actions being assigned to multiple keywords.
     *
     * If there are actions with more than one keyword, the corresponding
     * keywords are joined together in an array. For example:
     *
     *     [{action: '#Bugs', keyword: 'b1'}, {action: '#Bugs', keyword: 'b2'}]
     *
     * would be transformed to:
     *
     *     [{action: '#Bugs', keyword: ['b1', 'b2']}]
     *
     * This function assumes that the possible multiple keywords are all unique,
     * as handled by the {@link #_sanitizeConfig} method.
     *
     * @private
     * @param {Array} data The sanitized configuration data.
     * @return {Array} The configuration data, with multiple keywords per action
     *   stored in an array.
     */
    _joinMultipleKeywordConfigs: function(data) {
        var visited = {};

        _.each(data, function(obj, idx) {
            var action = obj.action;
            var visitedAt = visited[action];

            if (!_.isUndefined(visitedAt)) {
                // If we've visited this action before, merge the keywords.
                var originalKeyword = data[visitedAt].keyword;
                if (!_.isArray(originalKeyword)) {
                    if (originalKeyword !== obj.keyword) {
                        data[visitedAt].keyword = [originalKeyword, obj.keyword];
                    }
                } else {
                    data[visitedAt].keyword = _.uniq(data[visitedAt].keyword.concat(obj.keyword));
                }
                data.splice(idx, 1);
            } else {
                // Otherwise, store it in the frequency hash.
                visited[action] = idx;
            }
        });
        return data;
    },

    /**
     * Sanitizes the configuration data by removing empty/falsy values.
     *
     * @protected
     * @param {Array} data The unsanitized configuration data.
     * @return {Array} The sanitized configuration data.
     */
    _sanitizeConfig: function(data) {
        data = _.reject(data, function(row) {
            return !row.keyword || !row.action;
        });

        return data;
    },

    /**
     * This method prepares the attributes payload for the call to
     * {@link Core.User#updatePreferences}.
     *
     * @protected
     * @param {Array} data The unprepared configuration data.
     * @return {Array} The prepared configuration data.
     */
    _formatForUserPrefs: function(data) {
        return {'sweetspot': data};
    },

    /**
     * Compare with the user preferences and return true if the collection
     * contains changes.
     *
     * This method is called by {@link app.plugins.Editable}.
     *
     * @return {boolean} `true` if current collection contains unsaved changes,
     *   `false` otherwise.
     */
    hasUnsavedChanges: function() {
        var oldConfig = app.user.getPreference('sweetspot');
        var newConfig = this.collection.toJSON();
        var isChanged = !_.isEqual(oldConfig, newConfig);

        return isChanged;
    }
})
