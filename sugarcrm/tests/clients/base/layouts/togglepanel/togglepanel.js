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

describe("Base.Layout.Togglepanel", function () {

    var app, layout;

    beforeEach(function () {
        app = SugarTest.app;
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Toggle Panel", function () {
        var oLastState;
        beforeEach(function () {
            var meta = {
            }
            oLastState = app.user.lastState;
            app.user.lastState = {
                key: function(){},
              get: function(){},
                set: function(){},
                register: function(){}
            };
            var stub = sinon.stub(app.user.lastState);
            layout = SugarTest.createLayout("base", "Accounts", "togglepanel", meta);
        });
        afterEach(function () {
            app.user.lastState = oLastState;
        });
        it("should initialize", function () {
            var showSpy = sinon.stub(layout, 'showComponent', function () {
            });
            var processToggleSpy = sinon.stub(layout, 'processToggles', function () {
            });
            layout.initialize(layout.options);
            expect(layout.toggleComponents).toEqual([]);
            expect(layout.componentsList).toEqual({});
            expect(showSpy).toHaveBeenCalled;
            expect(processToggleSpy).toHaveBeenCalled();
        });
        it("should process toggles", function () {
            var meta = {
                'availableToggles': [
                    {
                        'name': 'test1',
                        'label': 'test1',
                        'icon': 'icon1'
                    },
                    {
                        'name': 'test2',
                        'label': 'test2',
                        'icon': 'icon2'
                    },
                    {
                        'name': 'test3',
                        'label': 'test3',
                        'icon': 'icon3'
                    }
                ],
                'components': {
                    'c1': {
                        'view': 'test1'
                    },
                    'c2': {
                        'layout': 'test2'
                    },
                    'c3': {
                        'layout': {
                            'name': 'test3'
                        }
                    }
                }
            }
            layout.options.meta = meta;
            layout.processToggles();
            expect(layout.toggles).toEqual([
                {
                    class: 'icon1',
                    title: 'test1',
                    toggle: 'test1'
                },
                {
                    class: 'icon2',
                    title: 'test2',
                    toggle: 'test2'
                },
                {
                    class: 'icon3',
                    title: 'test3',
                    toggle: 'test3'
                }
            ]);
        });
        it('should place toggle components and add them to the togglable component lists', function () {
            var mockComponent = new Backbone.View();
            mockComponent.name = 'test1';
            mockComponent.dispose = function () {
            };
            layout.options.meta.availableToggles = [
                {
                    'name': 'test1',
                    'label': 'test1',
                    'icon': 'icon1'
                }
            ];
            layout._placeComponent(mockComponent);

            expect(layout.toggleComponents).toEqual([mockComponent]);
            expect(layout.componentsList[mockComponent.name]).toEqual(mockComponent);
        });
    });
});
