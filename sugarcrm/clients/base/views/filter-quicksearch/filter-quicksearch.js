/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * View for doing a quick search.
     * Part of BaseFilterLayout
     *
     * @class BaseFilterQuicksearchView
     * @extends View
     */

    events: {
        'keyup': 'throttledSearch',
        'paste': 'throttledSearch'
    },

    plugins: ['QuickSearchFilter'],

    // Defining tagName, className and attributes allows us to avoid a template and an extra element
    tagName: 'input',
    className: 'search-name',
    attributes: {
        'type': 'text'
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        this.listenTo(this.layout, 'filter:clear:quicksearch', this.clearInput);
        this.listenTo(this.layout, 'filter:change:module', this.updatePlaceholder);
    },

    /**
     * Fire quick search
     * @param {Event} e
     */
    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$el.val();
        if(this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.layout.trigger('filter:apply', newSearch);
        }
    }, 400),

    /**
     * Retrieve the field labels
     *
     * @param {String} moduleName
     * @param {Array} field names
     * @returns {Array} field labels
     */
    getFieldLabels: function(moduleName, fields) {
        var moduleMeta = app.metadata.getModule(moduleName);
        var labels = [];
        _.each(fields, function(fieldName) {
            var fieldMeta = moduleMeta.fields[fieldName];
            labels.push(app.lang.get(fieldMeta.vname, moduleName).toLowerCase());
        });
        return labels;
    },

    /**
     * Update quick search placeholder to Search by Field1, Field2, Field3 when the module changes
     * @param string linkModuleName
     * @param string linkModule
     */
    updatePlaceholder: function(linkModuleName, linkModule) {
        var label;
        this.toggleInput();
        if (!this.$el.hasClass('hide') && linkModule !== 'all_modules') {
            var fields = this.getModuleQuickSearchFields(linkModuleName),
                fieldLabels = this.getFieldLabels(linkModuleName, fields);
            label = app.lang.get('LBL_SEARCH_BY') + ' ' + fieldLabels.join(', ') + '...';
        } else {
            label = app.lang.get('LBL_BASIC_QUICK_SEARCH');
        }
        this.$el.attr('placeholder', label);
    },

    /**
     * Hide input if on Activities
     */
    toggleInput: function() {
        this.$el.toggleClass('hide', !!this.layout.showingActivities);
    },

    /**
     * Clear input
     */
    clearInput: function() {
        this.toggleInput();
        this.$el.val('');
        this.currentSearch = '';
        this.layout.trigger('filter:apply');
    }
})
