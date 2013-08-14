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

    var app, layout, togglePanelStub;

    beforeEach(function () {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', "Cases", "panel", null, null);
        togglePanelStub = sinon.stub(layout, 'togglePanel');
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        togglePanelStub.restore();
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
        it("should toggle panel when collection reset", function() {
            layout.collection.reset([]);
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(false);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            layout.collection.reset([{id: 'test'}]);
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(true);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);
        });
        it("should toggle panel depending on last state", function() {
            var state = 'hide';
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get', function() {
                return state;
            });
            layout.collection.reset([{id: 'test'}]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(false);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            state = 'show';

            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(true);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            lastStateGetStub.restore();
        });
        it("should set last state when toggling panel", function() {
            togglePanelStub.restore();
            var lastStateSetStub = sinon.stub(app.user.lastState, 'set');

            layout.togglePanel(false, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.togglePanel(true, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.togglePanel(true);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('show');

            layout.togglePanel(false);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('hide');

            lastStateSetStub.restore();
        });
    });
});
