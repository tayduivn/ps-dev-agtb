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
    lastMode: null,
    plugins: ['EllipsisInline'],

    events: {
        'click .show-more-text': 'toggleMoreText'
    },
    format: function(value) {
        //We mutate this.value to match whatever is appropriate given we're in a
        //"more" or "less" state (original is always in model.get(this.name))
        //So, here, we try to return whatever we've set this.value to first.
        return this.value || value || '';
    },
    _render: function() {
        //Attempt to pick up css class from defs but fallback
        this.def.css_class = this.def.css_class || 'textarea-text';

        //Figure out if we need to display the show more link. Also handle value
        //to make sure we can actually get a length from it.
        var value = this.model.get(this.name),
            hasLength = !_.isUndefined(value) && !_.isNull(value);

        if (hasLength && (value.length > this.maxDisplayLength)) {
            this.isTooLong = true;
        } else {
            this.isTooLong = false;
            this.lastMode = null;
            this.value = value;
        }

        //Check if we've blur'd out from textarea edit mode. If so, we check "last mode"
        //we were in before entering the edit mode. We show more or less based on that.
        if (this.lastMode && this.tplName === 'edit') {
            if (this.lastMode === 'more') {
                this.showMore();
                return;
            }
            this.showLess();
            return;
        }

        app.view.Field.prototype._render.call(this);
        //Dynamically add the appropriate css class to this.$el (avoids extra spans)
        this.$el.addClass(this.def.css_class);

        //More|less not appropriate for list views (they use "overflow ellipsis")
        if (this._notListView()) {

            if (this.tplName !== 'edit') {
                if (this.isTooLong) {
                    this.showLess();
                }
                if(this.tplName === 'disabled') {
                    this.$(this.fieldTag).attr("disabled", "disabled");
                }
            } else {
                // Ensure that when we go to edit more textarea we have full text
                this.value = value;
                // Re render w/full text. tplName will not change to 'edit' until
                // after we _render (chicken / egg); so if we don't do this and we're
                // in the 'show more' state, we could get truncated text in our textarea!
                app.view.Field.prototype._render.call(this);
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
        if (this.isTruncated) {
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
        var displayValue, newLinkLabel, originalValue;
        originalValue = this.model.get(this.name);
        if (mode === "more") {
            displayValue = originalValue.trim() + '...';
            this.isTruncated = false;
            newLinkLabel = app.lang.get('LBL_LESS', this.module).toLocaleLowerCase();
        } else {
            displayValue = originalValue.substring(0, this.maxDisplayLength).trim() + '...';
            this.isTruncated = true;
            newLinkLabel = app.lang.get('LBL_MORE', this.module).toLocaleLowerCase();
        }
        this.value = displayValue;
        this.$el.empty();
        app.view.Field.prototype._render.call(this);
        this.$('.show-more-text').text(newLinkLabel);
        this.lastMode = mode;
    }

})
