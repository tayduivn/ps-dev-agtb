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
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinon.collection.restore();
    });

    it("should instantiate an invitation collection if invitation_actions is set", function() {
        view = SugarTest.createView('base', moduleName, 'planned-activities');

        // stub out our test method
        view._createInvitationsCollection = sinon.collection.stub();

        // mock out the parent call on _initTabs
        view.dashletConfig = {
            tabs: view.meta.tabs
        }

        view._initTabs();
        expect(view._createInvitationsCollection.called).toBeTruthy();
    });

    it("should not instantiate an invitation collection if invitation_actions is not set", function() {
        var meta = {
            'tabs' : [
                {
                    'module': 'Meetings'
                }
            ]
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
    });
});

