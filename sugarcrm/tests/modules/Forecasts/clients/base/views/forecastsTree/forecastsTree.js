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

describe("The forecasts tree view", function(){

    var app, view, data, replaceHTMLChars;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsTree", "forecastsTree", "js", function(d) { return eval(d); });
    });

    describe("_recursiveReplaceHTMLChars", function() {

        beforeEach(function() {

            //This is a global namespace function of window if loaded so we can stub it out; otherwise let's just create a similar function
            if(typeof window.replaceHTMLChars == "function")
            {
                replaceHTMLChars = sinon.stub(window, "replaceHTMLChars", function(value) {
                    return value.replace(/&#039;/gi,'\'');
                });
            } else {
                replaceHTMLChars = function(value) {
                    return value.replace(/&#039;/gi,'\'');
                };
            }

            data = [{

                attr : {
                    id: "jstree_node_jim",
                    rel: "root"
                },

                children : {
                    0 : {
                        attr: {
                           id: "jstree_node_sarah",
                           rel: "reportee"
                        },

                        children: [],

                        data: "Sarah O&#039;Reilly" //Sarah O'Reilly
                    }
                },

                data: "Jim O&#039;Gara" //Jim O'Gara
            }];

        });

        afterEach(function() {
            data = null;
            replaceHTMLChars = null;
        });

        it("correctly encodes Jim and Sarah's name", function()
        {
           var result = view._recursiveReplaceHTMLChars(data, view);
           expect(result[0].data === "Jim O'Gara").toBeTruthy("Correctly encoded Jim's name");
           var children = result[0].children[0];
           expect(children[0].data === "Sarah O'Reilly").toBeTruthy("Correctly encoded Sarah's name");
        });

    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(view, 'render'),
                ctx = {},
                selectedUser = {
                    showOpps: false,
                    isManager: true,
                    id: 'testA'
                };
            view.currentRootId = 'testB';

            view.checkRender(ctx, selectedUser);
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            view.disposed = true;
            view.checkRender(ctx, selectedUser);
            expect(renderStub).not.toHaveBeenCalled();

        });
    });
});
