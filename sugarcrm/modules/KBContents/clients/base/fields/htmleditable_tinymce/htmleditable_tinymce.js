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
     * Additional override fieldSelector property from field's meta.
     */
    initialize: function(opts) {
        this._super('initialize', [opts]);
        if (!_.isUndefined(this.def.fieldSelector)) {
            this.fieldSelector = '[data-htmleditable=' + this.def.fieldSelector + ']';
        }
    },

    /**
     * {@inheritDoc}
     *
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
     * Need to strip tags for list and activity stream.
     */
    format: function(value) {
        var result;
        switch (this.view.tplName) {
            case 'audit':
            case 'list':
            case 'activitystream':
                result = this.stripTags(value);
                break;
            default:
                result = this._super('format', [value]);
                break;
        }
        return result;
    },

    /**
     * Strip HTML tags from text.
     * @param {string} value Value to strip tags from.
     * @return {string} Plain text.
     */
    stripTags: function(value) {
        var $el = $('<div/>').html(value),
            texts = $el.contents()
            .map(function() {
                if (this.nodeType === 1 && this.nodeName != 'STYLE' && this.nodeName != 'SCRIPT') {
                    return this.textContent.replace(/ +?\r?\n/g, ' ').trim();
                }
                if (this.nodeType === 3) {
                    return this.textContent.replace(/ +?\r?\n/g, ' ').trim();
                }
            });
        return _.filter(texts, function(value) {
            return (value.length > 0);
        }).join(' ');
    }
})
