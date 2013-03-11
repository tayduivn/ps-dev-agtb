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

describe("products_view_record", function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "commit_stage"
                    }]
                }]
            }
        };

        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');
        view = SugarTest.loadFile("../modules/Products/clients/base/views/record", "record", "js", function(d) { return eval(d); });
    });

    describe("initialization", function() {
        beforeEach(function() {
            sinon.stub(app.view.views.RecordView.prototype, "initialize");

            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "commit_stage_binary_dom"
                }
            })
            sinon.stub(view, "_setupCommitStageField");

        });

        afterEach(function() {
            view._setupCommitStageField.restore();
            app.metadata.getModule.restore();
            app.view.views.RecordView.prototype.initialize.restore();
        });

        it("should set up the commit_stage field for products", function () {
            view.initialize(options);
            expect(view._setupCommitStageField).toHaveBeenCalled();//With(options.meta.panels);
        });
    });

    describe("_setupCommitStageField method", function() {
        it("should remove the commit_stage field if forecasts is not setup", function() {
            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: false
                }
            });
            view._setupCommitStageField(options.meta.panels);
            expect(options.meta.panels[0].fields).toEqual([]);
            app.metadata.getModule.restore();
        });

        it("should set the proper options on the commit_stage field if forecasts has been setup", function() {
            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "something_testable"
                }
            });
            view._setupCommitStageField(options.meta.panels);
            expect(options.meta.panels[0].fields[0].options).toEqual("something_testable");
            app.metadata.getModule.restore();
        });
    });

})
