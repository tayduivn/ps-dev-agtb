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
            var rowComponent = this.addRow();
            rowComponent.model.set('action', row.action);
            rowComponent.model.set('keyword', row.keyword);
        }, this);
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
     *
     * @param {Event} [evt] The `click` event.
     */
    addRow: function(evt) {
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
        });

        app.drawer.close(this.collection);
        app.events.trigger('sweetspot:reset');
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
        result = this._formatForUserPrefs(result);

        // TODO: multiple hotkey association.

        return result;
    },

    /**
     * Sanitizes the configuration data by removing empty/falsy values.
     *
     * @protected
     * @param {Array} data The unsanitized configuration data.
     * @return {Array} The sanitized configuration data.
     */
    _sanitizeConfig: function(data) {
        // Throw out empty values.
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
