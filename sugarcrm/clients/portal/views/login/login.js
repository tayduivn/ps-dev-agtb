/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (''License'') which can be viewed at
 * http://www.sugarcrm.com/crm/mastersubscriptionagreement
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
 * by SugarCRM are Copyright (C) 20042012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    plugins: ['ErrorDecoration', 'Tooltip'],
    fallbackFieldTemplate: "edit",
    /**
     * Login form view.
     * @class View.Views.LoginView
     * @alias SUGAR.App.view.views.LoginView
     * @extends View.View
     */
    events: {
        'click [name=login_button]': 'login',
        'click [name=signup_button]': 'signup',
        'keypress': 'handleKeypress'
    },
    /**
     * Hide "forgot password" tooltip when clicking anywhere outside the link.
     * @param options
     */
    initialize: function(options) {
        var self = this;

        this._super('initialize', [options]);

        $(document).on('click.login', function(event) {
            var $forgotPassword = self.$('#forgot-password'),
                forgotPassword = $forgotPassword.get(0);
            if (!$.contains(forgotPassword, event.target)) {
                app.utils.tooltip.hide(forgotPassword);
            }
        });
    },
    /**
     * Sign Up button
     */
    signup : function() {
        app.router.navigate('#signup');
        app.router.start();
    },
    /**
     * @Override
     */
    postLogin: function(){
        app.$contentEl.show();
    },
    /**
     * Remove event handler for hiding "forgot password" tooltip.
     * @private
     */
    _dispose: function() {
        $(document).off('click.login');
        this._super('_dispose');
    }
})
