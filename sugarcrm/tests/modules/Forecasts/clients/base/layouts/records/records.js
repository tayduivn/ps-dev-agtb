//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("forecasts_layout_records", function(){

    var app, layout, stubs;

    beforeEach(function() {
        var options = {
            context: new Backbone.Model(),
            meta: {
                type: 'records',
                components: {}
            }
        };
        app = SugarTest.app;
        var recordsController = SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/records", "records", "js", function(d) {
            return eval(d);
        });

        stubs = [];
        app.viewModule = "Forecasts";
        app.initData = {};
        app.defaultSelections = {
            timeperiod_id: {},
            group_by: {},
            dataset: {},
            selectedUser: {}
        };

        stubs.push(sinon.stub(app.metadata, "getLayout", function(){
            return {
                forecasts: {
                    meta: {
                        components: {}
                    }
                },
                componentsMeta: {}
            };
        }));
        stubs.push(sinon.stub(app.metadata, "getModule", function(){
            return {
                config : {
                    hello : 'world'
                }
            };
        }));
        stubs.push(sinon.stub(app.view.Layout.prototype, "initialize", function () {}));

        stubs.push(sinon.stub(app.api, "call"));

        layout = SugarTest.createComponent('Layout', {
            name: "records",
            module: "Forecasts",
            context: options.context,
            meta : options.meta,
            controller: recordsController
        });
    });

    afterEach(function() {
        // restore the local stubs
        _.each(stubs, function(stub) {
            stub.restore();
        });
        layout = '';
    });

    describe("_placeComponent function", function() {
        it("should place a view in the correct div in the DOM if that div exists", function() {
            var name = "testComp",
                testEl = '<div id="test">' + name + '</div>',
                testComp = {
                    name:name,
                    $el: $(testEl)
                };
            layout.$el = $('<div class="outer"><div class="view-' + name + '"></div></div>');
            layout._placeComponent(testComp);
            expect(testComp.$el.parent().html()).toEqual(testEl);
        });

        it("should place a layout in the correct div in the DOM if the layout has a name and the div exists", function() {
            var name = "testComp",
                testEl = '<div id="test">' + name + '</div>',
                testComp = {
                    meta: {
                        name:name
                    },
                    $el: $(testEl)
                };

            layout.$el = $('<div class="outer"><div class="view-' + name + '"></div></div>');
            layout._placeComponent(testComp);
            expect(testComp.$el.parent().children().last()[0]).toEqual(testComp.$el[0]);
        });

        it("should append a view to the end of the sidecar DOM hierarchy if the div does not exist", function() {
            var name = "testComp",
                testEl = '<div id="test">' + name + '</div>',
                testComp = {
                    name:name,
                    $el: $(testEl)
                };

            layout.$el = $('<div class="outer"></div>');
            layout._placeComponent(testComp);
            expect(layout.$el.children().last()[0]).toEqual(testComp.$el[0]);
        });

        it ("should append a layout to the end of the sidecar DOM hierarchy if the div does not exist", function() {
            var name = "testComp",
                testEl = '<div id="test">' + name + '</div>',
                testComp = {
                    meta: {
                        name:name
                    },
                    $el: $(testEl)
                };

            layout.$el = $('<div class="outer"></div>');
            layout._placeComponent(testComp);
            expect(layout.$el.children().last()[0]).toEqual(testComp.$el[0]);
        });

        it("should not place components that have placeInLayout set to false in their view metadata", function() {
            var name = "testComp",
                testEl = '<div id="test">' + name + '</div>',
                testComp = {
                    meta: {
                        name:name,
                        placeInLayout: false
                    },
                    $el: $(testEl)
                };

            layout.$el = $('<div class="outer"></div>');
            layout._placeComponent(testComp);
            // component should not be placed so html() should be an empty string
            expect(layout.$el.html()).toBe('');
        });
    });
});
