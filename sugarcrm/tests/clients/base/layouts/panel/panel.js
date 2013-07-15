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

describe("Base.Layout.Panel", function () {

    var app, layout, triggerStub;

    beforeEach(function () {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', "Cases", "panel", null, null);
        triggerStub = sinon.stub(layout, 'trigger');
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        triggerStub.restore();
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Toggle Show/Hide", function() {
        it("should retrieve last state when collection reset", function() {
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get');
            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            lastStateGetStub.restore();
        });
        it("should trigger hide when collection reset", function() {
            layout.collection.reset([]);
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub.lastCall.args[0]).toBe('hide');
            expect(triggerStub.lastCall.args[1]).toBe(false);

            layout.collection.reset([{id: 'test'}]);
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub.lastCall.args[0]).toBe('hide');
            expect(triggerStub.lastCall.args[1]).toBe(true);
        });
        it("should trigger hide when last state exists", function() {
            var state = 'hide';
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get', function() {
                return state;
            });
            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub.lastCall.args[0]).toBe('hide');
            expect(triggerStub.lastCall.args[1]).toBe(false);

            state = 'show';

            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub.lastCall.args[0]).toBe('hide');
            expect(triggerStub.lastCall.args[1]).toBe(true);

            lastStateGetStub.restore();
        });
        it("should set last state when trigger hide", function() {
            triggerStub.restore();
            var lastStateSetStub = sinon.stub(app.user.lastState, 'set');

            layout.trigger('hide', true, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.trigger('hide', false, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.trigger('hide', true);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('show');

            layout.trigger('hide', false);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('hide');

            lastStateSetStub.restore();
        });
    });
});
