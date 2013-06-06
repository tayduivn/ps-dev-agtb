//FILE SUGARCRM flav=ent ONLY
describe("ContactsBean", function() {
    describe("isValid", function(){
        var app, bean;
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
            SugarTest.app.data.declareModels();
            app = SugarTest.app;
            app.router = {};
            app.router.start = function(){};
            app.events.trigger('app:sync:complete');
            bean = app.data.createBean("Contacts");

            SugarTest.clock.restore();
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            app.cache.cutAll();
        });

        it("should perform uniqueness check only if portal_name attribute has changed", function(){
            bean.fields = {portal_name: {required: true, name: 'portal_name'}, field: {required: true, name: 'field'}};
            var stub = sinon.stub(app.api, 'records'),
                stub2 = sinon.stub(bean, "trigger");

            bean.set("field", "abc");
            runs(function(){
                bean._doValidatePortalName(bean.fields, {}, function() {});
            });
            waitsFor(function() {
                return stub2.calledTwice;
            }, 'trigger should have been called but timeout expired', 1000);
            runs(function(){
                expect(stub.called).toBe(false);

                stub2.reset();

                bean.set("portal_name", "abc");
                bean._doValidatePortalName(bean.fields, {}, function() {});
            });
            waitsFor(function() {
                return stub2.calledTwice;
            }, 'trigger should have been called but timeout expired', 1000);
            runs(function(){
                expect(stub.called).toBe(true);
                stub.restore();
                stub2.restore();
            });
        });

        it("should return an error on portal_name field when there is another Contact with the same portal_name", function(){
            bean.fields = {portal_name: {name: 'portal_name'}};
            var stub = sinon.stub(app.api, 'records', function(method, module, data, params, callbacks){
                // Fake having 1 match for "abc" as portal_name
                expect(params.filter[0].portal_name).toEqual("abc");
                callbacks.success({records:[{id: "123", portal_name: "abc"}]});
                callbacks.complete();
            });
            var stub2 = sinon.stub(bean, "trigger");
            var callbackSpy = sinon.spy();

            bean.set("portal_name", "abc");

            runs(function(){
                bean._doValidatePortalName(bean.fields, {}, callbackSpy);
            });
            waitsFor(function() {
                return stub2.callCount > 0;
            }, 'trigger should have been called but timeout expired', 1000);
            runs(function(){
                expect(stub.called).toBe(true);
                expect(callbackSpy.called).toBe(true);
                expect(callbackSpy.lastCall.args[2]["portal_name"]).toBeDefined();

                stub.restore();
                stub2.restore();
            });
        });
    });

});
