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

describe("Home Dashboard layout controller", function(){
    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        var dashboardController = SugarTest.loadFile("../clients/base/layouts/dashboard", "dashboard", "js", function(d) {
            return eval(d);
        });

        var options = {
            context: new Backbone.Model(),
            meta: {
                type: 'dashboard',
                components: {}
            }
        };

        layout = SugarTest.createComponent('Layout', {
            name: "dashboard",
            module: "Home",
            context: options.context,
            meta : options.meta,
            controller: dashboardController
        });
    });

    afterEach(function() {
        layout = '';
    });

    describe("drag/drop functions", function() {
        var testDraggableEl = '<div id="myDrag" class="testDrag">Test</div>',
            testDroppableEl = '<div id="myDrop" class="testDrop">Test</div>',
            previousEl='';

        beforeEach(function() {
            previousEl = layout.$el;
        });

        afterEach(function() {
            layout.$el = previousEl;
        })

        it("addDraggable test", function() {
            layout.$el.append(testDraggableEl);
            layout.draggableClass = ".testDrag";
            layout.addDraggable();
            expect(layout.$el.find('#myDrag').hasClass('ui-draggable')).toBeTruthy();
        });
        it("addDroppable test", function() {
            layout.$el.append(testDroppableEl);
            layout.droppableClass = ".testDrop";
            layout.addDroppable();
            expect(layout.$el.find('#myDrop').hasClass('ui-droppable')).toBeTruthy();
        })
    });
});