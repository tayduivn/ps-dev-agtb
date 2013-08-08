describe('Base.View.Profileactions', function() {
    var view, app;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Home', 'profileactions', null, null);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('when displaying the admin menu', function() {

        it('should show users that are a developer for a module', function() {
            var aclStub = sinon.collection.stub(app.user, 'getAcls').returns({
                Accounts : {
                    admin : "no",
                    developer : "no"
                },
                //Is developer for contacts
                Contacts : {
                    admin : "no"
                }
            });
            expect(view._isAdminOrDevForAnyModule()).toBeTruthy();
            aclStub.restore();
        });

        it('should show users that are an admin for a module', function() {
            var aclStub = sinon.collection.stub(app.user, 'getAcls').returns({
                //Is admin for Accounts
                Accounts : {
                    developer : "no"
                },

                Contacts : {
                    admin : "no",
                    developer : "no"
                }
            });
            expect(view._isAdminOrDevForAnyModule()).toBeTruthy();
            aclStub.restore();
        });

        it('should show users that are a global admin', function() {
            var aclStub = sinon.collection.stub(app.user, 'getAcls').returns({
                //Is admin for Accounts
                Accounts : { },
                Contacts : { }
            });
            expect(view._isAdminOrDevForAnyModule()).toBeTruthy();
            aclStub.restore();
        });

        it('should not show users that are a not developers or admins for a module', function() {
            var aclStub = sinon.collection.stub(app.user, 'getAcls').returns({
                Accounts : {
                    admin : "no",
                    developer : "no"
                },
                //Is developer for contacts
                Contacts : {
                    admin : "no",
                    developer : "no"
                }
            });
            expect(view._isAdminOrDevForAnyModule()).toBeFalsy();
            aclStub.restore();
        });
    });
});
