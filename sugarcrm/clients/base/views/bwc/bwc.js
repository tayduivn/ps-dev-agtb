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
    tagName: 'iframe',
    className: 'bwc-frame',
    // TODO check if we need to support multiple bwc views
    id: 'bwc-frame',
    // Precompiled regex (note-regex literal causes errors but RegExp doesn't)
    moduleRegex: new RegExp("module=([^&]*)"),
    idRegex: new RegExp("record=([^&]*)"),
    actionRegex: new RegExp("action=([^&]*)"),

    initialize: function (options) {
        this.$el.attr('src', options.context.get('url') || 'index.php?module=' + this.options.module + '&action=index');
        app.view.View.prototype.initialize.call(this, options);
    },

    /**
     * Render the iFrame and listen for content changes on it.
     *
     * Every time there is an update on the iFrame, we:
     * <li>update the controller context to mach our bwc module (if exists)</li>
     * <li>update our url to match the current iFrame location in bwc way</li>
     * <li>rewrite links for sidecar modules</li>
     * <li>rewrite links that go for new windows</li>
     *
     * @private
     */
    _renderHtml: function () {
        var self = this;

        app.view.View.prototype._renderHtml.call(this);

        this.$el.load(function () {
            var module = self.moduleRegex.exec(this.contentWindow.location.search);
            module = (_.isArray(module)) ? module[1] : null;
            if (module) {
                // update bwc context
                var app = window.parent.SUGAR.App;
                app.controller.context.set('module', module);
                app.events.trigger('app:view:change');
            }

            window.parent.location.hash = '#bwc/index.php' + this.contentWindow.location.search;

            if (this.contentWindow.$ === undefined) {
                // no jQuery available, graceful fallback
                return;
            }
            self._rewriteLinksForSidecar(this.contentWindow);
            self._rewriteNewWindowLinks(this.contentWindow);

        });
    },

    /**
     * Gets the sidecar url based on a given bwc hyperlink.
     * @param {String} href the bwc hyperlink.
     * @return {String} the new sidecar hyperlink (empty string if unable to convert).
     */
    convertToSidecarUrl: function (href) {
        var module = this.moduleRegex.exec(href),
            id = this.idRegex.exec(href),
            action = this.actionRegex.exec(href);

        module = (_.isArray(module)) ? module[1] : null;
        if (!module) {
            return '';
        }
        id = (_.isArray(id)) ? id[1] : null;
        action = (_.isArray(action)) ? action[1] : '';
        // fallback to sidecar detail view
        if (action.toLowerCase() === 'detailview') {
            action = '';
        }

        if (!id && action.toLowerCase() === 'editview') {
            action = 'create';
        }

        return app.router.buildRoute(module, id, action);
    },

    /**
     * Rewrite old links on the frame given to the new sidecar router.
     *
     * This will match all hrefs that contain "module=" on it and if the module
     * isn't blacked listed, then rewrite into sidecar url.
     *
     * @see Bwc::_blackList() for the list of modules not sidecar ready.
     *
     * @param {Window} frame the contentWindow of the frame to rewrite links on.
     * @private
     */
    _rewriteLinksForSidecar: function (frame) {
        var self = this;

        var notSidecar = self._getBlackList();
        frame.$('a[href*="module="]').each(function (i, elem) {
            var $elem = $(elem),
                href = $elem.attr('href'),
                module = self.moduleRegex.exec(href);

            if (!_.isArray(module) || _.isEmpty(module[1]) ||
                _.indexOf(notSidecar, module[1]) !== -1
                ) {
                return;
            }

            var sidecarUrl = self.convertToSidecarUrl(href);
            $elem.attr('href', app.config.siteUrl + '/#' + sidecarUrl);
            $elem.data('sidecarProcessed', true);

            if ($elem.attr('target') === '_blank') {
                return;
            }

            $elem.click(function (e) {
                if (e.button !== 0 || e.ctrlKey || e.metaKey) {
                    return;
                }
                e.stopPropagation();
                parent.SUGAR.App.router.navigate(sidecarUrl, {trigger: true});
                return false;
            });
        });
    },

    /**
     * Rewrite new window links (target=_blank) on the frame given to the new
     * sidecar with bwc url.
     *
     * This will match all "target=_blank" links that aren't already pointing to
     * sidecar already and make them sidecar bwc compatible. This will assume
     * that all links to sidecar modules are already rewritten.
     *
     * @param {Window} frame the contentWindow of the frame to rewrite links on.
     * @private
     */
    _rewriteNewWindowLinks: function (frame) {

        frame.$('a[target="_blank"]').not('[href^="http"]').each(function (i, elem) {
            var $elem = $(elem);
            if ($elem.data('sidecarProcessed')) {
                return;
            }
            $elem.attr('href', app.config.siteUrl + '/#bwc/' + $elem.attr('href'));
        });
    },

    /**
     * TODO move this to config.js or somewhere where partners/customers can change.
     * @see https://docs.google.com/a/sugarcrm.com/spreadsheet/ccc?key=0AhZMJewalakadERxWlA3UnByT0dLTC04Q2Z5eGJHbFE#gid=6
     * @private
     */
    _getBlackList: function () {
        return [
            'ACLFields',
            'ACLRoles',
            'ACLActions',
            'Administration',
            'Audit',
            'Calendar',
            'Calls',
            'CampaignLog',
            'Campaigns',
            'CampaignTrackers',
            'Charts',
            'Configurator',
            'Contracts',
            'ContractTypes',
            'Connectors',
            'Currencies',
            'CustomQueries',
            'DataSets',
            'DocumentRevisions',
            'Documents',
            'EmailAddresses',
            'EmailMarketing',
            'EmailMan',
            'Emails',
            'EmailTemplates',
            'Employees',
            'Exports',
            'Expressions',
            'Groups',
            'History',
            'Holidays',
            'iCals',
            'Import',
            'InboundEmail',
            'KBContents',
            'KBDocuments',
            'KBDocumentRevisions',
            'KBTags',
            'KBDocumentKBTags',
            'KBContents',
            'Manufacturers',
            'Meetings',
            'MergeRecords',
            'ModuleBuilder',
            'MySettings',
            'Notifications',
            'OAuthKeys',
            'OptimisticLock',
            'OutboundEmailConfiguration',
            'PdfManager',
            'ProductBundleNotes',
            'ProductBundles',
            'ProductCategories',
            'ProductTypes',
            'Project',
            'ProjectResources',
            'ProjectTask',
            'ProspectLists',
            'Quotes',
            'QueryBuilder',
            'Relationships',
            'Releases',
            'ReportMaker',
            'Reports',
            'Roles',
            'SavedSearch',
            'Schedulers',
            'SchedulersJobs',
            'Shippers',
            'SugarFavorites',
            'SugarFeed',
            'TaxRates',
            'TrackerSessions',
            'TrackerPerfs',
            'TrackerQueries',
            'Teams',
            'TeamMemberships',
            'TeamSets',
            'TeamSetModules',
            'TeamNotices',
            'TimePeriods',
            'Trackers',
            'UserPreferences',
            'Users',
            'vCals',
            'vCards',
            'Versions',
            'WorkFlow',
            'WorkFlowActions',
            'WorkFlowActionShells',
            'WorkFlowAlerts',
            'WorkFlowAlertShells',
            'WorkFlowTriggerShells'
        ];
    }
})
