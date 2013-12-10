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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
describe("Planned Activities", function () {
    var moduleName = 'Home',
        app, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'tabbed-dashlet');
        SugarTest.loadComponent('base', 'view', 'history');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('planned-activities', {
            'tabs': [
                {
                    'module': 'Meetings',
                    'invitation_actions' : {
                        'name' : 'accept_status_users',
                        'type' : 'invitation-actions'
                    }
                }
            ]
        });

        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinon.collection.restore();
    });

    it('should instantiate an invitation collection if invitation_actions is set', function() {
        var meta = _.extend(app.metadata.getView(moduleName, 'planned-activities'), {
            'last_state': 'ignore'
        });

        view = SugarTest.createView('base', moduleName, 'planned-activities', meta);

        // stub out our test method
        view._createInvitationsCollection = sinon.collection.stub();

        // mock out the parent call on _initTabs
        view.dashletConfig = {
            tabs: view.meta.tabs
        };

        view._initTabs();
        expect(view._createInvitationsCollection.called).toBeTruthy();
        view.dispose();
    });

    it('should not instantiate an invitation collection if invitation_actions is not set', function() {
        var meta = {
            'tabs' : [
                {
                    'module': 'Meetings'
                }
            ],
            'last_state': 'ignore'
        };
        view = SugarTest.createView('base', moduleName, 'planned-activities', meta);

        //stub out our test method
        view._createInvitationsCollection = sinon.collection.stub();

        // mock out the parent call on _initTabs
        view.dashletConfig = {
            tabs: view.meta.tabs
        };

        view._initTabs();
        expect(view._createInvitationsCollection.called).toBeFalsy();
        view.dispose();
    });
});

