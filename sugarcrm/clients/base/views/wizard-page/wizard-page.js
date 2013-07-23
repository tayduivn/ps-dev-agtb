/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (''License'') which can be viewed at
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
 *  (i) the ''Powered by SugarCRM'' logo and
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

    /**
     * An abstract WizardPageView.  Wizard pages should extend this and provide
     * field metadata, custom logic, etc.  This view is detached from Wizard
     * layout when it is not the current page.  When it becomes the current
     * page it is appended to Wizard layout and render is called.
     *
     * If you want to use the default Wizard template, you'll need to load it in initialize.
     * For example,
     *
     * <pre>
     * initialize: function(options){
     *   //Load the default wizard page template, if you want to.
     *   options.template = app.template.getView("wizard-page");
     *   app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
     * },
     * </pre>
     *
     * @class View.Views.WizardPageView
     * @alias SUGAR.App.view.views.WizardPageView
     */
    events: {
        'click [name=previous_button]': 'previous',
        'click [name=next_button]': 'next'
    },

    /**
     * Current progress through wizard, updated automatically on each render.
     */
    progress: null,

    /**
     * Additionally update current progress and button status during a render.
     *
     * @override
     * @private
     */
    _render: function(){
        this.progress = this.layout.getProgress();
        app.view.View.prototype._render.call(this);
        this.updateButtons();
    },
    /**
     * Called after render to update status of next/previous buttons.
     */
    updateButtons: function(){
        var prevBtn = this.getField("previous_button");
        if(prevBtn){
            if(this.progress && this.progress.page > 1){
                prevBtn.show();
            } else {
                prevBtn.hide();
            }
        }
        var nextBtn = this.getField("next_button");
        if(nextBtn){
            nextBtn.setDisabled(!this.isPageComplete());
        }
    },
    /**
     * Called after initialization of the wizard page but just before it gets
     * added as a component to the Wizard layout.  Allows implementers to
     * control when a wizard page is included. Default implementation hides
     * page if it will not render because of ACL checks.
     *
     * @returns {boolean} TRUE if this page should be included in wizard
     */
    showPage: function(){
        return app.acl.hasAccessToModel(this.action, this.model);
    },
    /**
     * We can advance the page once we know it is complete. Wizard page's
     * should override this function to provide custom validation logic.
     *
     * @returns {boolean} TRUE if this page is complete
     */
    isPageComplete: function(){
        return true;
    },

    /**
     * Next button pressed
     */
    next: function() {
        if(this.progress.page !== this.progress.lastPage){
            this.progress = this.layout.nextPage();
        } else {
            this.finish();
        }
    },

    /**
     * Previous button pressed
     */
    previous: function(){
        this.progress = this.layout.previousPage();
    },

    /**
     * Next button pressed and this is the last page. Implementers should
     * override this and are responsible for removing Wizard layout.
     */
    finish: function(){

    }

})
