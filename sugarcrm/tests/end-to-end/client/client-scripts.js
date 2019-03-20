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
};
