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
        'click #tour': 'showTutorial',
        'click #feedback': 'feedback',
        'click #support': 'support',
        'click #help': 'help'
    },
    tagName: 'span',
    handleViewChange: function(layout, params) {
        var module = params && params.module ? params.module : null;
        if (app.tutorial.hasTutorial(layout, module)) {
            this.enableTourButton();
        } else {
            this.disableTourButton();
        }
    },
    handleRouteChange: function(route, params) {
        this.routeParams = {'route': route, 'params': params};
    },
    enableTourButton: function() {
        this.$('#tour').removeClass('disabled');
        this.events['click #tour'] = 'showTutorial';
        this.undelegateEvents();
        this.delegateEvents();
    },
    disableTourButton: function() {
        this.$('#tour').addClass('disabled');
        delete this.events['click #tour'];
        this.undelegateEvents();
        this.delegateEvents();
    },
    initialize: function(options) {

        app.view.View.prototype.initialize.call(this, options);
        app.events.on('app:view:change', this.handleViewChange, this);
        var self = this;
        app.utils.doWhen(function() {
            return !_.isUndefined(app.router)
        }, function() {
            self.listenTo(app.router, 'route', self.handleRouteChange);
        });

    },
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    feedback: function() {
        window.open('http://www.sugarcrm.com/sugar7survey', '_blank');
    },
    support: function() {
        window.open('http://support.sugarcrm.com', '_blank');
    },
    help: function() {
        var serverInfo = app.metadata.getServerInfo();
        var lang = app.lang.getLanguage();
        var module = app.controller.context.get('module');
        var route = this.routeParams.route;
        var url = 'http://www.sugarcrm.com/crm/product_doc.php?edition=' + serverInfo.flavor + '&version=' + serverInfo.version + '&lang=' + lang + '&module=' + module + '&route=' + route;
        if (route == 'bwc') {
            var action = window.location.hash.match(/#bwc.*action=(\w*)/i);
            if (action && !_.isUndefined(action[1])) {
                url += '&action=' + action[1];
            }
        }
        app.logger.info("help URL: " + url);
        window.open(url);
    },
    showTutorial: function() {
        app.tutorial.resetPrefs();
        app.tutorial.show(app.controller.context.get('layout'), {module: app.controller.context.get('module')});
    }
})

