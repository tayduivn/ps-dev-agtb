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

describe("ForecastWorksheets.Field.Parent", function () {

    var app, field, buildRouteStub, moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'relate');
        SugarTest.loadComponent('base', 'field', 'parent');

        var fieldDef = {
            "name": "parent_name",
            "rname": "name",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
            "link": "accounts",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };

        // Workaround because router not defined yet
        oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function(){}};
        buildRouteStub = sinon.stub(SugarTest.app.router, 'buildRoute', function(module, id, action, params) {
            return module+'/'+id;
        });

        field = SugarTest.createField("base", "parent", 'parent', 'list', fieldDef, moduleName, null, null, true);
    });

    afterEach(function() {
        buildRouteStub.restore();
        field = null;
        app = null;
    });

    it('field.options.viewName should undefined', function() {
        field.model = new Backbone.Model({'parent_deleted': 0});
        field.render();
        expect(_.isUndefined(field.options.viewName)).toBeTruthy();
    });

    it('field.options.viewName should equal deleted', function() {
        field.model = new Backbone.Model({'parent_deleted': 1});
        field.render();
        expect(_.isUndefined(field.options.viewName)).toBeFalsy();
        expect(field.options.viewName).toEqual('deleted');
        expect(field.deleted_value).not.toBeEmpty();
    });
});
