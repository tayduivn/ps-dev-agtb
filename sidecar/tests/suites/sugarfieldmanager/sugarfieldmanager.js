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
        });

        afterEach(function () {
           this.sugarFieldManager.reset();
        });

        it("should sync all sugar fields from server", function () {
                SUGAR.App.sugarFieldsSync = function () {
                };
                var stub = sinon.stub(SUGAR.App, "sugarFieldsSync", function (that, callback){
                    var ajaxResponse = sugarFieldsFixtures;
                    var result= callback(that, ajaxResponse);
                    return result;
                });
                var syncResult=this.sugarFieldManager.syncFields();
                expect(syncResult).toBeTruthy();
            }
        );

        it("should reset if asked", function () {
                SUGAR.App.sugarFieldsSync = function () {
                };
                var stub = sinon.stub(SUGAR.App, "sugarFieldsSync");
                stub.returns(sugarFieldsFixtures);
                var result = this.sugarFieldManager.syncFields();
                expect(result).toBeTruthy();
                var result = this.sugarFieldManager.reset();
                expect(this.sugarFieldManager.fieldsObj).toEqual({});
                expect(this.sugarFieldManager.fieldsHash).toEqual('');
            }
        );

        it("should get a sugar fields", function () {
                SUGAR.App.sugarFieldsSync = function () {
                };
                var stub = sinon.stub(SUGAR.App, "sugarFieldsSync", function (that, callback){
                    var ajaxResponse = sugarFieldsFixtures;
                    var result= callback(that, ajaxResponse);
                    return result;
                });
                var syncResult=this.sugarFieldManager.syncFields();
                expect(syncResult).toBeTruthy();

                var result = this.sugarFieldManager.getField('varchar','editView');
                console.log(JSON.stringify(result.template));
                expect(result.type).toEqual('basic');
                expect(result.template).toEqual(' <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input01\">{{label}}</label>\n\n        <div class=\"controls\">\n            <input type=\"text\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n            <p class=\"help-block\">{{help}}</p>\n        </div>\n    </div>\n');
            }
        );

        it("should retun an object of sugar fields", function () {
                var stubbedFields = sugarFieldsFixtures;

                SUGAR.App.sugarFieldsSync = function () {
                };
                var stub = sinon.stub(SUGAR.App, "sugarFieldsSync", function (that, callback){
                    var ajaxResponse = sugarFieldsFixtures;
                    var result= callback(that, ajaxResponse);
                    return result;
                });
                var syncResult=this.sugarFieldManager.syncFields();
                expect(syncResult).toBeTruthy();

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