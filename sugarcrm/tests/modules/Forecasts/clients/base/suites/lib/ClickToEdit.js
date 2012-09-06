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

describe("ClickToEdit", function(){
    var field, view, editable, clickToEdit;

    beforeEach(function() {
        app = SugarTest.app;
        editable = SugarTest.loadFile("../include/javascript/twitterbootstrap/js", "jquery.jeditable", "js", function(d) { return eval(d); });
        clickToEdit = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ClickToEdit", "js", function(d) { return eval(d); });

        view = {
            $el: $('<div class="testview"></div>'),
            url: "/test"
        };
        field = {
            $el: $('<div class="testfield"></div>'),
            viewName:'testView',
            def: {
                clickToEdit:true
            },
            delegateEvents: function() {}
        };
        view.$el.append(field.$el);
    });

    afterEach(function() {
        view = {};
        field = {};
    });

    it("should add the editable plugin on the element", function() {
        var jqSpy = sinon.spy(field.$el, "editable");
        new app.view.ClickToEditField(field, view);
        expect(jqSpy).toHaveBeenCalled();
        jqSpy.restore();
    });

    it("should add the show/hide icon handlers", function(){
        expect(field.showCteIcon).not.toBeDefined();
        expect(field.hideCteIcon).not.toBeDefined();
        new app.view.ClickToEditField(field, view);
        expect(field.showCteIcon).toBeDefined();
        expect(field.hideCteIcon).toBeDefined();
    });

    it("should add the mouseenter and mouseleave events", function() {
        expect(field.events).not.toBeDefined();
        new app.view.ClickToEditField(field, view);
        expect(field.events).toBeDefined();
        expect(field.events.mouseenter).toBeDefined();
        expect(field.events.mouseleave).toBeDefined();
    });

    it("should add pencil icon on mouseenter events and remove on mouseleave events", function() {
        new app.view.ClickToEditField(field, view);
        expect(field.$el.parent()).not.toContain("i.icon-pencil");
        field.showCteIcon();
        expect(field.$el.parent()).toContain("i.icon-pencil");
        field.hideCteIcon();
        expect(field.$el.parent()).not.toContain("i.icon-pencil");
    });

    it("should correctly validate the percentage adjustments for currency fields", function() {
        field.type = 'currency';
        var clickToEdit = new app.view.ClickToEditField(field, view);
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "+10%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "-10%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "+10.5%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "-10.5%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "10,000.50")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "abc%")).toBeFalsy();
    });

    it("should correctly accept input for all text type fields", function() {
        field.type = 'text';
        var clickToEdit = new app.view.ClickToEditField(field, view);
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "+10%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "-10%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "+10.5%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "-10.5%")).toBeTruthy();
        expect(app.view.ClickToEditField.prototype._checkDatatype(field, "abc")).toBeTruthy();
    });

});