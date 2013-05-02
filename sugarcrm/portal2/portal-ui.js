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
(function(app) {

    /**
     * Overrides View::_renderHtml() to enable bootstrap widgets after the element has been added to the DOM
     */
    var __superViewRender__ = app.view.View.prototype._renderHtml;
    app.view.View.prototype._renderHtml = function() {

        __superViewRender__.call(this);

        // do this if greater than 768px page width
        if ($(window).width() > 768) {
            this.$("[rel=tooltip]").tooltip({ placement: "bottom" });
        }
        //popover
        this.$("[rel=popover]").popover();
        this.$("[rel=popoverTop]").popover({placement: "top"});

        if ($.fn.timeago) {
            $("span.relativetime").timeago({
                logger: SUGAR.App.logger,
                date: SUGAR.App.date,
                lang: SUGAR.App.lang,
                template: SUGAR.App.template
            });
        }
        /**
         * Fix placeholder on global search on IE and old browsers
         */
        if($.fn.placeholder){
            this.$("input[placeholder]").placeholder();
        }
    };

    /**
     * Overrides View::initialize() to remove the bootstrap widgets element from all the page
     * The widget is actually bind to an element that will be removed from the DOM when the view changes. So we need to
     * manually remove elements automatically created by the widget.
     */
    var __superViewInit__ = app.view.View.prototype.initialize;
    app.view.View.prototype.initialize = function(options) {
        __superViewInit__.call(this, options);
        $('.popover, .tooltip').remove();
    };

    /**
     * Overrides Field::_render() to fix placeholders on IE and old browsers
     */
    var __superFieldRender__ = app.view.Field.prototype._render;
    app.view.Field.prototype._render = function() {
        _.each(this.$('[rel="tooltip"]'), function(element) {
            $(element).tooltip('hide');
        })
        __superFieldRender__.call(this);
        if($.fn.placeholder){
            this.$("input[placeholder]").placeholder();
        }
    };


})(SUGAR.App);
