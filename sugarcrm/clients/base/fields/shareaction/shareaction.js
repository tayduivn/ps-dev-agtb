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
    /**
     * Share row action.
     *
     * This allows an user to share a record that is currently mapped with this
     * field context
     *
     * @class View.Fields.ShareActionField
     * @alias SUGAR.App.view.fields.ShareActionField
     * @extends View.Fields.EmailactionField
     */
    extendsFrom: 'EmailactionField',

    plugins: ['EmailClientLaunch'],

    /**
     * Share template for subject.
     * @see _initShareTemplates()
     */
    shareTplSubject: null,

    /**
     * Share template for body.
     * @see _initShareTemplates()
     */
    shareTplBody: null,

    /**
     * Share template for body in HTML format.
     * @see _initShareTemplates()
     */
    shareTplBodyHtml: null,

    /**
     * {@inheritDoc}
     *
     * Adds the share options for use when launching the email client
     */
    initialize: function(options) {
        this._super("initialize", [options]);
        this.type = 'emailaction';
        this._initShareTemplates();
        this._setShareOptions();
    },

    /**
     * Initializes the sharing feature templates.
     *
     * This will get the templates from either the current module (since we
     * might want to customize it per module) or from core templates.
     *
     * Please define your templates on:
     *
     * - `custom/clients/{platform}/view/share/subject.hbs`
     * - `custom/clients/{platform}/view/share/body.hbs`
     * - `custom/clients/{platform}/view/share/body-html.hbs`
     * - `{custom/,}modules/{module}/clients/{platform}/view/share/subject.hbs`
     * - `{custom/,}modules/{module}/clients/{platform}/view/share/body.hbs`
     * - `{custom/,}modules/{module}/clients/{platform}/view/share/body-html.hbs`
     *
     * @template
     * @protected
     */
    _initShareTemplates: function() {
        this.shareTplSubject = app.template.getView('share.subject', this.module) ||
            app.template.getView('share.subject');
        this.shareTplBody = app.template.getView('share.body', this.module) ||
            app.template.getView('share.body');
        this.shareTplBodyHtml = app.template.getView('share.body-html', this.module) ||
            app.template.getView('share.body-html');
    },

    /**
     * Set subject and body settings for the EmailClientLaunch plugin to use
     *
     * @protected
     */
    _setShareOptions: function() {
        var shareParams = this._getShareParams(),
            subject = this.shareTplSubject(shareParams),
            body = this.shareTplBody(shareParams),
            bodyHtml = this.shareTplBodyHtml(shareParams);

        this.addEmailOptions({
            subject: subject,
            html_body: bodyHtml || body,
            text_body: body
        });
    },

    /**
     * Get the params required by the templates defined on
     * {@link _initShareTemplates}.
     *
     * Override this if your templates need more information to be sent on the
     * share email.
     *
     * @template
     * @protected
     */
    _getShareParams: function() {
        var moduleString = app.lang.getAppListStrings('moduleListSingular');

        return _.extend({}, this.model.attributes, {
            module: moduleString[this.module] || this.module,
            appId: app.config.appId,
            url: window.location.href,
            name: this.model.attributes.name || this.model.attributes.full_name
        });
    }
})
