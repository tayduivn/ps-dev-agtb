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

describe("forecasts_field_historyLog", function() {
    var field, fieldDef, context, model;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({"selectedUser": {"id": 'test_user'}});

        model = new Backbone.Model({
            user_id: 'test_user',
            show_history_log: "0"
        });

        fieldDef = {
            "name": "forecastHistoryLog",
            "type": "historyLog",
            "view": "detail"
        };

        field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef, null, model, context);
    });
    afterEach(function() {
        context.unset("selectedUser");
        field = null;
        fieldDef = null;
    });

    it("field.uid should be the user_id from the model", function() {
        expect(field.uid).toEqual(model.get('user_id'));
    });

    describe("showFieldAlert", function() {
        it("should be false", function() {
            expect(field.showFieldAlert).toBeFalsy();
        });
        it("should be true", function() {
            model.set({"show_history_log": "1"});
            var field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef, null, model, context);
            expect(field.showFieldAlert).toBeTruthy();
        });
    });

    describe("showOpps", function() {
        it("should be false", function() {
            context.set({"selectedUser": {"id": 'test_user_1'}});
            var field = SugarTest.createField("../modules/Forecasts/clients/base", "historyLog", "historyLog", "detail", fieldDef, null, model, context);
            expect(field.showOpps).toBeFalsy();
        });
        it("should be true", function() {
            expect(field.showOpps).toBeTruthy();
        });
    })

});
