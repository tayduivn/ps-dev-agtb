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
    fieldTag: "select",
    _render: function() {

        var optionsKeys = [];
        if(_.isString(this.def.options)) {
            optionsKeys = _.keys(app.lang.getAppListStrings(this.def.options));
        } else if(_.isObject(this.def.options)) {
            optionsKeys = _.keys(this.def.options);
        }

        //After rendering the dropdown, the selected value should be the value set in the model,
        //or the default value. The default value fallbacks to the first option if no other is selected.
        //The chosen plugin displays it correctly, but the value is not set to the select and the model.
        //Below the workaround to save this option to the model manually.
        if (_.isUndefined(this.model.get(this.name))) {
            var defaultValue = _.first(optionsKeys);
            if (defaultValue) {
                this.$(this.fieldTag).val(defaultValue);
                this.model.set(this.name, defaultValue);
            }
        }

        var chosenOptions = {};
        var emptyIdx = _.indexOf(optionsKeys, "");
        if (emptyIdx !== -1) {
            chosenOptions.allow_single_deselect = true;
            // if the blank option isn't at the top of the list we have to add it manually
            if (emptyIdx > 1) {
                this.hasBlank = true;
            }
        }

        /*
         The forecasts module requirements indicate that the search bar only shows up for fields with 5 or more values,
         this adds the ability to specify that threshold in metadata.
          */
        chosenOptions.disable_search_threshold = this.def.searchBarThreshold?this.def.searchBarThreshold:0;

        app.view.Field.prototype._render.call(this);

        this.$(this.fieldTag).chosen(chosenOptions);
        this.$(".chzn-container").addClass("tleft");
        return this;
    },
    unformat:function(value) {
        return value;
    },
    format:function(value) {
        var newval = '', optionsObject, optionLabels;

        if(this.def.isMultiSelect && this.view.name !== 'edit') {
            // Gets the dropdown options e.g. {foo:foolbl, bar:barlbl ...}
            optionsObject = app.lang.getAppListStrings(this.def.options);

            // value are selected option keys .. grab corresponding labels
            _.each(value, function(p) {
                if(_.has(optionsObject, p)) {
                    newval += optionsObject[p]+', ';
                }
            });
            newval = newval.slice(0, newval.length - 2); // strips extra ', '
        } else {
            // Normal dropdown, just get selected
            newval = this.model.get(this.name);
        }
        // dropdown with default string so convert it to something we understand
        if(this.def.isMultiSelect && this.view.name === 'edit' && this.def.default && typeof newval ==='string') {
            newval = this.convertMultiSelectDefaultString(newval);
        }
        return newval;
    },
    /**
     * Converts multiselect default strings into array of option keys for template
     * @param {String} defaultString string of the format "^option1^,^option2^,^option3^"
     * @return {Array} of the format ["option1","option2","option3"]
     */
    convertMultiSelectDefaultString: function(defaultString) {
        var result = defaultString.split(",");
        _.each(result, function(value, key) {
            result[key] = value.replace(/\^/g,"");
        })
        return result;
    }
})
