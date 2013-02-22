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
    fieldSelector: '.htmlareafield', //iframe selector

    /**
     * {@inheritdoc}
     *
     * The html area is always a readonly field.
     * (see htmleditable for an editable html field)
     */
    initialize: function(options) {
        options.def.readonly = true;
        app.view.Field.prototype.initialize.call(this, options);
    },

    /**
     * {@inheritdoc}
     *
     * Set the name of the field on the iframe as well as the contents
     *
     * @private
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);

        this._getFieldElement().attr('name', this.name);
        this.setViewContent();
    },

    /**
     * Sets read only html content in the iframe
     */
    setViewContent: function(){
        var value = this.value || this.def.default_value;
        var field = this._getFieldElement();
        if(field && !_.isEmpty(field.get(0).contentDocument)) {
            if(field.contents().find('body').length > 0){
                field.contents().find('body').html(value);
            }
        }
    },

    /**
     * Finds iframe element in the field template
     *
     * @return {HTMLElement} element from field template
     * @private
     */
    _getFieldElement: function() {
        return this.$el.find(this.fieldSelector);
    }

})
