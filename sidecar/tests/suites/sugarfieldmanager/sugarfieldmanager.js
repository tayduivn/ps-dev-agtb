/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 2/2/12
 * Time: 5:15 PM
 * To change this template use File | Settings | File Templates.
 */
describe("SugarFieldManager", function () {

        // setup to be run before every test
        beforeEach(function () {
            this.sugarFieldManager = SUGAR.App.sugarFieldManager;
            this.api = SUGAR.Api.getInstance();
            this.server = sinon.fakeServer.create();
        });

        afterEach(function () {
           //this.sugarFieldManager.reset();
            this.server.restore();
        });

        it("should reset if asked", function () {
                var result = this.sugarFieldManager.reset();
                expect(this.sugarFieldManager.fieldsObj).toEqual({});
                expect(this.sugarFieldManager.fieldsHash).toEqual('');
            }
        );

        it("should sync all sugar fields from server", function () {
                var callspy = sinon.spy(this.api, 'call');
                var callbackSpy = sinon.spy(this.sugarFieldManager, 'handleResponse');
                this.sugarFieldManager.reset();
                this.server.respondWith("GET", "/rest/v10/sugarFields/?md5=",
                                [200, {  "Content-Type":"application/json"},
                                    JSON.stringify(sugarFieldsFixtures)]);

                var syncResult=this.sugarFieldManager.syncFields();

                this.server.respond(); //tell server to respond to pending async call
                expect(callspy).toHaveBeenCalledOnce();
                expect(callbackSpy).toHaveBeenCalledOnce();
                expect(syncResult).toBeTruthy();
                this.api.call.restore();
                this.sugarFieldManager.handleResponse.restore();
                this.sugarFieldManager.reset();
            }
        );

        it("should get a sugar field", function () {
                this.sugarFieldManager.reset();
                this.server.respondWith("GET", "/rest/v10/sugarFields/?md5=",
                                [200, {  "Content-Type":"application/json"},
                                    JSON.stringify(sugarFieldsFixtures)]);


                var syncResult=this.sugarFieldManager.syncFields();

                this.server.respond(); //tell server to respond to pending async call
                expect(syncResult).toBeTruthy();

                var result = this.sugarFieldManager.getField('varchar','editView');

            }
        );

        it("should return an object of sugar fields", function () {
                this.sugarFieldManager.reset();
                this.server.respondWith("GET", "/rest/v10/sugarFields/?md5=",
                                [200, {  "Content-Type":"application/json"},
                                    JSON.stringify(sugarFieldsFixtures)]);


                var syncResult=this.sugarFieldManager.syncFields();

                this.server.respond(); //tell server to respond to pending async call

                var stubbedFieldList = [
                    {name:"text",view:"editView"},
                    {name:"text", view:"detailView"},
                    {name:"password", view:"editView"},
                    {name:"button_save", view:"default"},
                    {name:"textarea", view: "editView"},
                    {name:"textarea", view: "detailView"},
                    {name:"asdfasd", view: "asdf"}
                ];

                var result = this.sugarFieldManager.getFields(stubbedFieldList);

                expect(result).toEqual(sugarFieldsGetFieldsResponse);
            }
        );


    }
);