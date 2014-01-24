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
    plugins: ['Editable'],

    events: {
        "click a[name=cancel_button]": "close",
        "click a[name=save_button]":   "save"
    },

    /**
     * Store the translated i18n label.
     * @type {String} Translated dashlet's title label.
     * @private
     */
    _translatedLabel: null,

    /**
     * {@inheritDoc}
     * Compare with the previous attributes and translated dashlet's label
     * in order to warn unsaved changes.
     *
     * @return {Boolean} true if the dashlet setting contains changes.
     */
    hasUnsavedChanges: function() {
        var previousAttributes = _.extend(this.model.previousAttributes(), {
            label: this._translatedLabel
        });
        return !_.isEmpty(this.model.changedAttributes(previousAttributes));
    },

    save: function() {
        this.context.trigger('dashletconfig:save', this.model);
    },

    close: function() {
        app.drawer.close();
    },

    /**
     * {@inheritdoc}
     *
     * Translate model label before render using model attributes.
     */
    _renderHtml: function() {
        var label;
        this.model = this.layout.context.get('model');
        label = app.lang.get(
            this.model.get('label'),
            this.model.get('module') || this.module,
            this.model.attributes
        );
        this._translatedLabel = label;
        this.model.set('label', label, {silent: true});
        app.view.View.prototype._renderHtml.call(this);
    }
})
