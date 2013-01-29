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
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=edit_button]': 'edit'
    },

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.set('subnavModel', new Backbone.Model());
        this.subnavModel = this.context.get('subnavModel');

        $(window).on("resize.subnav", _.bind(this.resize, this));
        if (this.meta && this.meta.label) {
            this.title = app.lang.get(this.meta.label, this.context.module);
        }
        this.context.on("subnav:set:title",function(title){
            this.title = title;
            this.render();
        }, this);
    },
    /**
     * Render and push down the view below
     * @private
     */
    _render: function() {
        var next, newMarginTop;

        app.view.View.prototype._render.call(this);

        //push down the view below by the subnav height
        next = this.$el.next();
        newMarginTop = parseInt(next.css('margin-top'), 10) + this.$el.find('.subnav').height();
        next.css('margin-top', newMarginTop + 'px');
    },

    /**
     * Handle click on the save button
     */
    save: function() {
        this.context.trigger("subnav:save");
    },

    /**
     * Handle click on the cancel button
     */
    cancel: function() {
        window.history.back();
    },

    /**
     * Handle click on the edit button
     */
    edit: function() {
        app.navigate(this.context, this.model, "edit", {trigger:true});
    },

    /**
     * Only re-render the view. Do not push down the view below.
     */
    bindDataChange: function() {
        var self = this;
        if (this.meta.field) {
            this.model.on(
                "change:"+this.meta.field,
                function() {
                    self.title = self.model.get(this.meta.field);
                    self.render();
                },
                this
          );
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
