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
        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
            app.cache.cutAll();
        });
        it("should return undefined", function(){
            expect(bean.isValid()).toBeUndefined();
        });

        it("should trigger a 'validation:success' before the 'validation:complete' event on a valid bean", function(){
            var stub = sinon.stub(bean, "trigger", function(event){
                if(stub.calledOnce){
                    expect(event).toEqual("validation:success");
                } else {
                    expect(event).toEqual("validation:complete")
                }
            });
            runs(function(){
                expect(bean.isValid()).toBeUndefined();
            });
            waits(1);
            runs(function(){
                expect(stub.calledTwice).toBe(true);
                stub.restore();
            });
        });

        it("should trigger a 'validation:complete' event even on invalid bean", function(){
            bean.fields = {field: {required: true, name: 'field'}};
            bean.set("field", "");
            var stub = sinon.stub(bean, "trigger", function(event){
                expect(event === "error:validation:field" || event === "error:validation" || event === "validation:complete").toBeTruthy();
            });
            runs(function(){
                expect(bean.isValid()).toBeUndefined();
            });
            waits(1);
            runs(function(){
                expect(stub.calledThrice).toBe(true);
                expect(stub.thirdCall.args[0]).toEqual("validation:complete");
                stub.restore();
            });
        });

        it("should perform uniqueness check only if portal_name attribute has changed", function(){
            bean.fields = {portal_name: {required: true, name: 'portal_name'}, field: {required: true, name: 'field'}};
            var stub = sinon.stub(app.api, 'records', function(){});

            bean.set("field", "abc");
            expect(bean.isValid()).toBeUndefined();
            expect(stub.called).toBe(false);

            bean.set("portal_name", "abc");
            expect(bean.isValid()).toBeUndefined();
            expect(stub.called).toBe(true);

            stub.restore();
        });

        it("should trigger validation error on portal_name field when there is another Contact with the same portal_name", function(){
            bean.fields = {portal_name: {name: 'portal_name'}};
            var stub = sinon.stub(app.api, 'records', function(method, module, data, params, callbacks){
                // Fake having 1 match for "abc" as portal_name
                expect(params.filter[0].portal_name).toEqual("abc");
                callbacks.success({records:[{id: "123", portal_name: "abc"}]});
                callbacks.complete();
            });
            var errorSpy = sinon.spy();
            bean.on("error:validation:portal_name", errorSpy);
            bean.set("portal_name", "abc");
            expect(bean.isValid()).toBeUndefined();
            expect(stub.called).toBe(true);
            expect(errorSpy.called).toBe(true);

            bean.off(null, errorSpy);
            stub.restore();
        });
    });

});
