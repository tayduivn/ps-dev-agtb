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
    fields: null,

    /**
     * Initializes the fieldset field component.
     *
     * Initializes the fields property.
     *
     * @param {Object} options
     *
     * @see app.view.Field.initialize
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);

        this.fields = [];
    },

    /**
     * {@inheritdoc}
     */
    getPlaceholder: function() {

        var placeholder = app.view.Field.prototype.getPlaceholder.call(this);
        var $container = $(placeholder.toString());

        _.each(this.def.fields, function(fieldDef) {
            var field = app.view.createField({
                def: fieldDef,
                view: this.view,
                viewName: this.options.viewName,
                model: this.model
            });
            this.fields.push(field);
            field.parent = this;
            $container.append(field.getPlaceholder().toString());
        }, this);

        return new Handlebars.SafeString($container.get(0).outerHTML);
    },

    /**
     * {@inheritdoc}
     *
     * We only render the child fields for this fieldset and for now there is no
     * support for templates on fieldset widgets.
     */
    _render: function() {

        _.each(this.fields, function(field) {
            field.render();
        }, this);

        // Adds classes to the component based on the metadata.
        if(this.def && this.def.css_class) {
            this.getFieldElement().addClass(this.def.css_class);
        }

        return this;
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect the nested fields that have the
     * same `fieldTag` of this fieldset due the usage of `find()` method.
     */
    bindDomChange: function() {
    },

    /**
     * {@inheritdoc}
     *
     * Keep empty because you cannot set a value of a type `fieldset`.
     */
    bindDataChange: function() {
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect the nested fields that have the
     * same `fieldTag` of this fieldset due the usage of `find()` method.
     */
    unbindDom: function() {
    }
})
