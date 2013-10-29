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
     * @extends View.Fields.RowactionField
     */
    extendsFrom: 'RowactionField',

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
     * Adds the share on click event to call the share action.
     */
    initialize: function(options) {
        options.def = options.def || {};

        this.events = _.extend({}, this.events, options.def.events || {}, {
            'click a[name="share"][data-event="true"]': 'share'
        });

        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'initialize', args: [options]});
        this._initShareTemplates();

        // FIXME this preference shouldn't be a string
        if (app.user.getPreference('use_sugar_email_client') !== 'true') {
            options.def.href = this._shareWithMailTo();
        }
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
    },

    /**
     * Share button event triggered.
     *
     * Check if we can use email compose (from within Sugar) or else use the
     * `mailto` default browser feature to deliver a pre-filled email message
     * (subject and body), based on the templates initialized in
     * {@link _initShareTemplates}.
     *
     * @see _shareWithSugarEmailClient()
     * @see _shareWithMailTo()
     */
    share: function() {
        this._shareWithSugarEmailClient();
    },

    /**
     * Share a record using internal SugarEmailClient.
     *
     * This will try to use the bodyHtml template and if its empty then it will
     * fallback to body template.
     *
     * @private
     */
    _shareWithSugarEmailClient: function() {
        var subject = this.shareTplSubject(this._getShareParams()),
            body = this.shareTplBody(this._getShareParams()),
            bodyHtml = this.shareTplBodyHtml(this._getShareParams());

        app.drawer.open({
            layout: 'compose',
            context: {
                create: true,
                module: 'Emails',
                model: app.data.createBean('Emails', {
                    subject: subject,
                    html_body: bodyHtml || body
                })
            }
        });
    },

    /**
     * Share a record by using the default `mailto` browser feature.
     *
     * This will not use the bodyHtml template, since it isn't supported by the
     * `mailto` feature.
     *
     * @private
     */
    _shareWithMailTo: function() {
        var subject = this.shareTplSubject(this._getShareParams()),
            body = this.shareTplBody(this._getShareParams());

        return 'mailto:?' + [
            'subject=' + encodeURIComponent(subject),
            'body=' + encodeURIComponent(body)
        ].join('&');
    }
})
