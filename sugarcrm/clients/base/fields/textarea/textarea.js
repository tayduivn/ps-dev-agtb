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
        //attempt to pick up css class from defs but fallback 
        this.def.css_class = this.def.css_class || 'textarea-text';
        //figure out if we need to display the show more link
        var value = this.model.get(this.name);

        if ((!_.isUndefined(value)) && (value.length > this.maxDisplayLength)) {
            this.isTooLong = true;
        }
        app.view.Field.prototype._render.call(this);

        //Dynamically add the appropriate css class to this.$el (avoids extra spans)
        this.$el.addClass(this.def.css_class);

        //More|less not appropriate for list views (they use "overflow ellipsis")
        if (this._notListView()) {
            if (this.isTooLong) {
                this.showLess();
            }
            if(this.tplName === 'disabled') {
                this.$(this.fieldTag).attr("disabled", "disabled");
            }
        }
    },
    _notListView: function() {
        if (this.view.name !== 'list' || (this.view.meta && this.view.meta.type !== 'list')) {
            return true;
        }
        return false;
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
            displayValue = this.value + '...';
            this.isTruncated = false;
            newLinkLabel = app.lang.get('LBL_LESS', this.module).toLocaleLowerCase();
        } else {
            displayValue = this.value.substring(0, this.maxDisplayLength) + '...';
            this.isTruncated = true;
            newLinkLabel = app.lang.get('LBL_MORE', this.module).toLocaleLowerCase();
        }
        //Repopulate the field with our updated text and append the more/less link
        this.$el.text(displayValue)
            .append('<a href="javascript:void(0)" class="show-more-text">'+newLinkLabel+'</a>');
        this.delegateEvents();
    }

})
