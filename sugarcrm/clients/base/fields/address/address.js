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
    unformat: function(value) {
        return value;
    },
    format: function(value, fieldName) {
        value = {
            street: this.model.get(this.name),
            city: this.model.get(this.formatFieldName('city')),
            postalcode: this.model.get(this.formatFieldName('postalcode')),
            state: this.model.get(this.formatFieldName('state')),
            country: this.model.get(this.formatFieldName('country'))
        };
        return value;
    },
    bindDomChange: function() {
        var self = this;
        var model = this.model;
        var fieldName = this.name;
        var street = this.$('.address_street');
        var city = this.$('.address_city');
        var country = this.$('.address_country');
        var postalcode = this.$('.address_postalcode');
        var state = this.$('.address_state');
        street.on('change', function() {
            model.set(fieldName, self.unformat(street.val()));
        });
        city.on('change', function() {
            model.set(self.formatFieldName('city'), self.unformat(city.val()));
        });
        postalcode.on('change', function() {
            model.set(self.formatFieldName('postalcode'), self.unformat(postalcode.val()));
        });
        state.on('change', function() {
            model.set(self.formatFieldName('state'), self.unformat(state.val()));
        });
        country.on('change', function() {
            model.set(self.formatFieldName('country'), self.unformat(country.val()));
        });
    },
    formatFieldName: function(attribute) {
        var endFieldName = '';
        var arrFieldName = this.name.split('_');
        if (arrFieldName[arrFieldName.length - 1] == 'c') {
            endFieldName = '_c';
            arrFieldName.pop();
        }
        if (arrFieldName[arrFieldName.length - 1] == 'street') arrFieldName.pop();
        var rootFieldName = arrFieldName.join('_');
        return rootFieldName + "_" + attribute + endFieldName;
    }
})