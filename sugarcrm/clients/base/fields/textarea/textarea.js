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
    fieldTag : "textarea",
    maxDisplayLength: 450,
    isTruncated: false,

    events: {
        'click .show-more-text': 'toggleMoreText'
    },

    format: function(value) {
        return value || '';
    },

    _render: function() {
        //figure out if we need to display the show more link
        var value = this.model.get(this.name);
        if (value.length > this.maxDisplayLength) {
            this.isTooLong = true;
        }

        app.view.Field.prototype._render.call(this);

        if (this.isTooLong) {
            this.showLess();
        }
    },

    toggleMoreText: function() {
        var self = this;
        if (self.isTruncated) {
            this.showMore();
        } else {
            this.showLess();
        }
    },

    showMore: function() {
        this._toggleTextLength('more');
    },

    showLess: function() {
        this._toggleTextLength('less');
    },

    _toggleTextLength: function(mode) {
        var displayValue,
            newLinkLabel;

        if (mode === "more") {
            displayValue = this.value;
            this.isTruncated = false;
            newLinkLabel = app.lang.get('LBL_LESS', this.module).toLocaleLowerCase();
        } else {
            displayValue = this.value.substring(0, this.maxDisplayLength) + '...';
            this.isTruncated = true;
            newLinkLabel = app.lang.get('LBL_MORE', this.module).toLocaleLowerCase();
        }
        this.$(".textarea-text").text(displayValue);
        this.$(".show-more-text").text(newLinkLabel);
    }

})
