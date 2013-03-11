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

describe("The forecasts title view", function () {

    var app, view, testMethodStub, context, viewController, stubs = [];

    beforeEach(function () {
        app = SugarTest.app;
        viewController = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsTitle", "forecastsTitle", "js", function (d) {
            return eval(d);
        });
        context = app.context.getContext({
            url:"someurl",
            module:"Forecasts"
        });

        view = SugarTest.createComponent("View", {
            context:context,
            name:"forecastsTitle",
            controller:viewController
        });
    });

    afterEach(function () {
        _.each(stubs, function (stub) {
            stub.restore();
        })
    });

    describe("should set full name", function () {

        beforeEach(function () {
            testMethodStub = sinon.stub(app.user, "get", function (property) {
                var user = {
                    full_name:"Test user"
                };
                return user[property];
            });
        });

        afterEach(function () {
            testMethodStub.restore();
            view.fullName = '';
        });

        it("from method call", function () {
            view.setFullNameFromUser(app.user);
            expect(view.fullName).toEqual(app.user.get('full_name'));
        });

        it("from change:selectedUser listener", function () {
            localUserStub = new Backbone.Model();
            localUserStub.set({full_name:"New User"});

            context.set('selectedUser', localUserStub);
            expect(view.fullName).toEqual(localUserStub.get('full_name'));

            delete localUserStub;
        });

    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(view, 'render');

            view.context.set({selectedUser: {id: 'newId'}});
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            view.disposed = true;
            view.context.set({selectedUser: {id: 'newId2'}});
            expect(renderStub).not.toHaveBeenCalled();
        });
    });
});
