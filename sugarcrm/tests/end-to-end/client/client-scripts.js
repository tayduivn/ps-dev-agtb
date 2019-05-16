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
module.exports = {

    /**
     * Scroll inside divs element to specific selector
     *
     * @param elementSelector
     */
    scrollToSelector: function(elementSelector) {
        $(elementSelector).get(0).scrollIntoView(false);
    },

    /**
     * Set value for TinyMCE field
     *
     * @param {string }mceId
     * @param {string} fieldValue
     */
    setValueForTinyMCE: function(mceId, fieldValue) {
        tinyMCE.get(mceId).execCommand('mceInsertContent', false, fieldValue);
    },

    /**
     * Get value of TinyMCE field
     */
    getValueForTinyMCE: function(mceId) {
        return tinyMCE.get(mceId).getContent({format: 'raw'});
    },
    /**
     * Get field label from the field name
     *
     * @param {string} fieldName
     * @param {string} moduleName
     */
    getLabelByFieldName: function(fieldName, moduleName) {
        return App.lang.get(App.metadata.getField({module: moduleName, name: fieldName}).vname, moduleName);
    },

    /**
     * Get field object
     *
     * @param {string} fieldName
     * @param {string} moduleName
     */
    getFieldDef: function(fieldName, moduleName) {
        return App.metadata.getField({module: moduleName, name: fieldName});
    },

    /**
     * Get html value from iFrame
     *
     */
    getiFrameValue: function(iFrameName) {
        var oFrame = document.querySelector('[data-name=' + iFrameName + '] iframe');
        return oFrame.contentDocument.body.innerHTML;
    },

    /**
     * Get current module
     *
     */
    getCurrentModule: function() {
        return App.controller.context.get('module');
    },

    /**
     * Get record id from the record name
     *
     * @param {string} recordName Record Name
     * @param {string} module Module name
     * @param {Function} cb Callback function
     */
    getRecordIDByName: function(recordName, module, cb) {
        App.api.call(
            'read',
            App.api.buildURL(
                module,
                'read',
                null,
                {fields: 'id, name', filter: [{name: {'$equals': recordName}}]}
            ),
            null,
            null,
            {
                success: function(r) {
                    cb(r.records[0].id);
                }
            }
        );
    },

    /**
     * Get most recent created record
     *
     * @param {string} module Module name
     * @param {Function} cb Callback function
     */
    getMostRecentlyCreatedRecord: function(module, cb) {
        App.api.call(
            'read',
            App.api.buildURL(
                module,
                'read',
                null,
                {fields: 'id, name', order_by: 'date_entered:desc'}
            ),
            null,
            null,
            {
                success: function(r) {
                    cb(r.records[0].id);
                }
            }
        );
    },

    /**
     * Get the URL fragment
     *
     * Note: This is mostly needed for pmse_Inbox module which has different list view
     * based on the URL routing:
     *
     */
    getRouter: function() {
        return App.router.getFragment();
    }
};
