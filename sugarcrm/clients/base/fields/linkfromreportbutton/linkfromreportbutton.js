/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * LinkFromReportButton allows user to select a report and relate records to
 * primary record.
 *
 * @class View.Fields.Base.LinkfromreportbuttonField
 * @alias SUGAR.App.view.fields.BaseLinkfromreportbuttonField
 * @extends View.Fields.Base.StickyRowactionField
 */
({
    extendsFrom: 'StickyRowactionField',
    events: {
        'click a[name=select_button]': 'openSelectDrawer'
    },

    /**
     * Event handler for the select button that opens a link selection dialog in a drawer for linking
     * an existing record
     * @override
     */
    openSelectDrawer: function() {
        if (this.isDisabled()) {
            return;
        }

        var filterOptions = new app.utils.FilterOptions().config(this.def).format();

        app.drawer.open({
            layout: 'selection-list',
            context: {
                module: 'Reports',
                filterOptions: filterOptions,
                parent: this.context
            }
        }, _.bind(this.selectDrawerCallback, this));
    },

    /**
     * Process the report that was selected by the user.
     * @param {object} model
     */
    selectDrawerCallback: function(model) {
        if (!model || _.isEmpty(model.id)) {
            return;
        }

        if (model.module != this.context.get('module')) {
            app.alert.show('listfromreport-warning', {
                level: 'warning',
                messages: app.lang.getAppString('LBL_LINK_FROM_REPORT_WRONG_MODULE'),
                autoClose: true
            });
            return;
        }

        var recordListUrl = app.api.buildURL('Reports', 'record_list', {id: model.id}),
            self = this;

        app.alert.show('listfromreport_loading', {level: 'process', title: app.lang.getAppString('LBL_LOADING')});

        app.api.call(
            'create',
            recordListUrl,
            null,
            {
                success: _.bind(self.linkRecordList, self),
                error: function(error) {
                    app.alert.dismiss('listfromreport_loading');
                    app.alert.show('server-error', {
                        level: 'error',
                        title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'),
                        messages: app.lang.getAppString('ERR_HTTP_500_TEXT')
                    });
                }
            }
        );
    },

    /**
     * Links records from a report to the parent record
     * @param {object} response
     */
    linkRecordList: function(response) {
        var parentModel = this.context.get('parentModel'),
            parentModule = parentModel.get('module') || parentModel.get('_module'),
            link = this.context.get('link'), action = 'link/' + link + '/add_record_list',
            url = app.api.buildURL(
                parentModule,
                action,
                {
                    id: parentModel.get('id'),
                    relatedId: response.id
                }
            );

        app.api.call('create', url, null, {
            success: _.bind(this.linkSuccessCallback, this),
            error: _.bind(this.linkErrorCallback, this),
            complete: function(data) {
                app.alert.dismiss('listfromreport_loading');
            }
        });
    },

    /**
     * Success callback function for api call
     * @param {object} results
     */
    linkSuccessCallback: function(results) {
        var message, messageLevel;
        if (results.related_records.success.length > 0) {
            messageLevel = 'success';
            message = app.lang.get('LBL_LINK_FROM_REPORT_SUCCESS', null, {
                reportCount: results.related_records.success.length
            });
        } else {
            messageLevel = 'warning';
            message = app.lang.get('LBL_LINK_FROM_REPORT_NO_DATA');
        }

        app.alert.show('server-success', {
            level: messageLevel,
            messages: message,
            autoClose: true
        });

        this.context.resetLoadFlag();
        this.context.set('skipFetch', false);
        this.context.loadData();
    },

    /**
     * Error callback function for api call
     * @param {object} error
     */
    linkErrorCallback: function(error) {
        app.alert.show('server-error', {
            level: 'error',
            title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'),
            messages: app.lang.getAppString('ERR_HTTP_500_TEXT')
        });
    },

    /**
     * Returns false if current user does not have access to Reports module - ACL checks
     * @return {Boolean} true if allow access, false otherwise
     * @override
     */
    isDisabled: function() {
        return !app.acl.hasAccess('view', 'Reports');
    }
})
