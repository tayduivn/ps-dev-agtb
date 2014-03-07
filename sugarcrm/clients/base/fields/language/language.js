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
    extendsFrom: 'EnumField',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        if (!this.model.has(this.name)) {
            this._setToDefault();
        }
    },

    /**
     * Ensure we load enum templates
     *
     * @override
     * @private
     */
    _loadTemplate: function() {
        this.type = 'enum';
        app.view.Field.prototype._loadTemplate.call(this);
        this.type = 'language';
    },

    /**
     * {@inheritDoc}
     * If no value, set the application default language as default value.
     * If edit mode, set the application default language on the model.
     */
    format: function(value) {
        if (!this.items[value]) {
            value = this._getDefaultOption();
            this._setToDefault();
        }

        return value;
    },

    /**
     * {@inheritdoc}
     *
     * @returns {String}  The default language as the default value
     */
    _getDefaultOption: function(optionsKeys) {
        return app.lang.defaultLanguage;
    },

    /**
     * Sets the default value for the field.
     */
    _setToDefault: function() {
        var defaultValue = this._getDefaultOption();
        this.model.set(this.name, defaultValue, {silent: true});
        //Forecasting uses backbone model (not bean) for custom enums so we have to check here
        if (_.isFunction(this.model.setDefaultAttribute)) {
            this.model.setDefaultAttribute(this.name, defaultValue);
        }
    }
})
