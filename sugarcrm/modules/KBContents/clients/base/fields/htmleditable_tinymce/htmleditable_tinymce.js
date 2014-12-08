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

({
    extendsFrom: 'Htmleditable_tinymceField',

    /**
     * {@inheritDoc}
     * Apply document css style to editor.
     */
    getTinyMCEConfig: function() {
        var config = this._super('getTinyMCEConfig'),
            content_css = [];

        _.each(document.styleSheets, function(style) {
            if (style.href) {
                content_css.push(style.href);
            }
        });
        config.content_css = content_css;
        config.body_class = 'kbdocument-body';

        config.file_browser_callback = _.bind(this.tinyMCEFileBrowseCallback, this);

        return config;
    },

    /**
     * {@inheritDoc}
     */
    setViewContent: function(value) {
        var editable = this._getHtmlEditableField();
        if (editable && !_.isUndefined(editable.get(0)) && !_.isEmpty(editable.get(0).contentDocument)) {
            if (editable.contents().find('body').length > 0) {
                editable.contents().find('body').html(value);
            }
        } else if (editable) {
            editable.html(value);
        }
    }
})
