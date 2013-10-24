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
    linkModules: [],
    events: {
        'click label[for=relationship]': 'setFocus'
    },
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.linkModules = this.context.get("linkModules");
    },
    setFocus: function (e) {
        this.$("#relationship").select2("open");
    },
    _renderHtml: function (ctx, options) {
        var self = this;
        app.view.View.prototype._renderHtml.call(this, ctx, options);
        this.$(".select2").select2({
            width: '100%',
            allowClear: true,
            placeholder: app.lang.get("LBL_SEARCH_SELECT")
        }).on("change", function (e) {
            if (_.isEmpty(e.val)) {
                self.context.trigger("link:module:select", null);
            } else {
                var meta = self.linkModules[e.val];
                self.context.trigger("link:module:select", {link: meta.link, module: meta.module});
            }
        });
    },
    _dispose: function() {
        this.$(".select2").select2('destroy');
        app.view.View.prototype._dispose.call(this);
    }
})
