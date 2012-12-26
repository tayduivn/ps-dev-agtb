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

describe("forecast historyLog field", function() {

    describe("test handleDeferredRender", function() {
        it("should trigger _render when both deferred objects are resolved", function() {
            var fieldDef = {
                "name": "forecastHistoryLog",
                "type": "historyLog",
                "view": "historyLog"
            };
            this.field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef);
            var renderSpy = sinon.stub(this.field, "_render", function() { });
            this.field.handleDeferredRender();
            this.field.mDeferred.resolve();
            this.field.wDeferred.resolve();
            expect(renderSpy).toHaveBeenCalled();
        });

        it("should not trigger _render when only one deferred object is resolved", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "historyLog",
                "view": "detail"
            };
            this.field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef);
            var renderSpy = sinon.stub(this.field, "_render", function() { });
            this.field.handleDeferredRender();
            this.field.mDeferred.resolve();
            expect(renderSpy).not.toHaveBeenCalled();
        });
    });

    describe("test showFieldAlert is properly set", function() {

        var app, fieldDef, field, committedView;

        beforeEach(function() {
            app = SugarTest.app;

            fieldDef = {
                "name": "forecast",
                "type": "historyLog",
                "view": "detail"
            };

            field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef);

            committedView = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsCommitted", "forecastsCommitted", "js", function (d) {
                return eval(d);
            });

            sinon.stub(app.view.Field.prototype, "_render");
        });

        afterEach(function() {
            delete fieldDef;
            delete field;
            delete committedView;
            app.view.Field.prototype._render.restore();
        });

        it("should set showFieldAlert to true if the reportee's forecasts is more recent than the manager's", function() {

            var reporteeModifiedDate = new Date();

            field.model = new Backbone.Model({
                user_id : 'seed_sarah_id',
                date_modified : reporteeModifiedDate.toISOString()
            });

            var managerModifiedDate = new Date();
            managerModifiedDate.setFullYear(reporteeModifiedDate.getFullYear() - 1);

            committedView.models = [new Backbone.Model({
                date_modified : managerModifiedDate.toISOString()
            })];

            field.context.forecasts = {
                committed : committedView
            };

            field._render();

            expect(field.showFieldAlert).toBeTruthy();
        });

        it("should not set showFieldAlert to true if the reportee's forecasts is not more recent than the manager's", function() {

            var reporteeModifiedDate = new Date();

            field.model = new Backbone.Model({
                user_id : 'seed_sarah_id',
                date_modified : reporteeModifiedDate.toISOString()
            });

            var managerModifiedDate = new Date();
            managerModifiedDate.setFullYear(reporteeModifiedDate.getFullYear() + 1);

            committedView.models = [new Backbone.Model({
                date_modified : managerModifiedDate.toISOString()
            })];

            field.context.forecasts = {
                committed : committedView
            };

            field._render();

            expect(field.showFieldAlert).not.toBeTruthy();
        });

        it("should set showFieldAlert to true if there is a reportee's forecast, but no manager forecast", function() {

            var reporteeModifiedDate = new Date();

            field.model = new Backbone.Model({
                user_id : 'seed_sarah_id',
                date_modified : reporteeModifiedDate.toISOString()
            });

            committedView.models = [];

            field.context.forecasts = {
                committed : committedView
            };

            field._render();

            expect(field.showFieldAlert).toBeTruthy();
        });

        it("should set showFieldAlert to false if there is a reportee's forecast but no date_modified entry in the model if when there is no manager forecast", function() {

            var reporteeModifiedDate = new Date();

            field.model = new Backbone.Model({
                user_id : 'seed_sarah_id'
            });

            committedView.models = [];

            field.context.forecasts = {
                committed : committedView
            };

            field._render();

            expect(field.showFieldAlert).not.toBeTruthy();
        });

    })

});
