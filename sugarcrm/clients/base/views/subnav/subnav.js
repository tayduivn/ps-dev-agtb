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
    events: {
        'click [name=save_button]': 'saveModel'
    },
    /**
     * Listens to the app:view:change event and show or hide the subnav
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.set('subnavModel', new Backbone.Model());
        this.subnavModel = this.context.get('subnavModel');

        $(window).on("resize.subnav", _.bind(this.resize, this));
    },
    saveModel: function() {
        this.context.trigger("subnav:save");
    },

    bindDataChange: function() {
        if (this.subnavModel) {
            this.subnavModel.on("change", this.render, this);
        }
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        this.resize();
    },
    resize: function() {
        var self = this;
        //The resize event is fired many times during the resize process. We want to be sure the user has finished
        //resizing the window that's why we set a timer so the code should be executed only once
        if (self.resizeDetectTimer) {
            clearTimeout(this.resizeDetectTimer);
        }
        self.resizeDetectTimer = setTimeout(function() {
            var $el = self.$('h1');
            //Checks if the element has ellipsis
            if ($el[0].offsetWidth < $el[0].scrollWidth) {
                $el.attr({'data-original-title':$el.text(),'rel':'tooltip'}).tooltip({placement: "bottom"});
            }
            else {
                $el.removeAttr('data-original-title rel');
            }
        }, 250);
    },
    _dispose: function() {
        $(window).off("resize.subnav");
        app.view.View.prototype._dispose.call(this);
    }
})
