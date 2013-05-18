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
    extendsFrom: 'FieldsetField',

    /**
     * {@inheritdoc}
     */
    _render: function() {
        if (_.isEmpty(this.fields)) {
            this._createFields();
            this._renderNewFields();
        } else {
            this._renderExistingFields();
        }

        // Adds classes to the component based on the metadata.
        if(this.def && this.def.css_class) {
            this.getFieldElement().addClass(this.def.css_class);
        }

        return this;
    },

    /**
     * Load fieldset template and create fields
     * @private
     */
    _createFields: function() {
        this._loadTemplate();
        this.$el.html(this.template(this));
    },

    /**
     * Render fields that have not been rendered previously
     * @private
     */
    _renderNewFields: function() {
        _.each(this.def.fields, function(fieldDef) {
            var field = this.view.getField(fieldDef.name);
            this.fields.push(field);
            field.setElement(this.$("span[sfuuid='" + field.sfId + "']"));
            field.render();
        }, this);
    },

    /**
     * Re-render fields
     * @private
     */
    _renderExistingFields: function() {
        _.each(this.fields, function(field) {
            field.render();
        }, this);
    },

    /**
     * {@inheritdoc}
     */
    getPlaceholder: function() {
        return app.view.Field.prototype.getPlaceholder.call(this);
    },

    /**
     * {@inheritdoc}
     */
    setMode: function(name) {
        this.tplName = name;
        app.view.invokeParent(this, {type: 'field', name: 'fieldset', method: 'setMode', args:[name]});
    }
})
