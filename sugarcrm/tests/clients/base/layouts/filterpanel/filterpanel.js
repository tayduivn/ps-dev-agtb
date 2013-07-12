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

describe("Base.Layout.Filterpanel", function(){

    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Filter Panel", function() {
        var oLastState;
        beforeEach(function() {
            oLastState = app.user.lastState;
            app.user.lastState = {
                key: function(){},
                get: function(){},
                set: function(){},
                register: function(){}
            };
            layout = SugarTest.createLayout("base", "Accounts", "filterpanel");
        });
        afterEach(function () {
            app.user.lastState = oLastState;
        });
        it("should initialize", function() {
            var spy = sinon.spy();
            layout.off();
            layout.on('filterpanel:change:module', spy);
            layout.initialize(layout.options);
            expect(spy).toHaveBeenCalled();
        });

        describe('events', function(){
           it('should update current module and link on filter change', function(){
               layout.trigger('filterpanel:change:module','test','testLink');
               expect(layout.currentModule).toEqual('test');
               expect(layout.currentLink).toEqual('testLink');
           });
           it('should trigger filter reinit on filter create close and no id', function(){
               var spy = sinon.spy();
               layout.on('filter:reinitialize', spy);
               layout.trigger('filter:create:close','test',false);
               expect(spy).toHaveBeenCalled();
           });
        });
    });
});
