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

describe("forecasts_field_editableDate", function() {
    var field, fieldDef, context, model, app, getModuleStub;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        getModuleStub = sinon.stub(app.metadata, "getModule", function() {
            return {
                sales_stage_won: ["Closed Won"],
                sales_stage_lost: ["Closed Lost"]
            };
        });
        fieldDef = {
            "name": "editableDate",
            "type": "editableDate",
            "view": "detail"
        };
        SugarTest.loadComponent('base', 'field', 'date');
        field = SugarTest.createField("../modules/Forecasts/clients/base", "editableDate", "editableDate", "detail", fieldDef, "Forecasts", model, context);
    });

    afterEach(function() {
        getModuleStub.restore();
        field = null;
        context = null;
        model = null;
    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(field, 'render'),
                mockEvent = jQuery.Event('click');

            field.onClick(mockEvent);
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            field.disposed = true;
            field.onClick(mockEvent);
            expect(renderStub).not.toHaveBeenCalled();

        });
    });
    describe("checkIfCanEdit", function() {
        it("should not be able to edit", function() {
            field.model.set({sales_stage: "Closed Won"});
            field.checkIfCanEdit();
            expect(field._canEdit).toBeFalsy();
        });

        it("should be able to edit", function() {
            field.model.set({sales_stage: "asdf"});
            field.checkIfCanEdit();
            expect(field._canEdit).toBeTruthy();
        })
    });

    describe("field validates date properly on hide", function() {
        var stubs = [],
            initialDate = '01/01/2001',
            newValidDate = '02/02/2002',
            newInvalidDate = '89543627598276435',
            fieldTemplate;

        beforeEach(function() {
            SugarTest.loadFile("../modules/Forecasts/clients/base/helper", "hbt-helpers", "js", function(d) {
                return eval(d);
            });
            SugarTest.loadFile("../styleguide/assets/js", "bootstrap-tooltip", "js", function(d) {
                return eval(d);
            });
            // stub a bunch of functions so they dont get called
            stubs.push(sinon.stub(field, 'onBlur', function() {}));
            stubs.push(sinon.stub(field, 'showErrors', function() {}));
            stubs.push(sinon.stub(field, 'render', function() {}));
            stubs.push(sinon.stub(field, '_render', function() {}));

            fieldTemplate = _.template('<span class="edit">'
                + '<div class="input-append date" rel="datepicker">'
                + '<input type="text" class="datepicker input-small focused" value="<%= dateValue %>" rel="datepicker">'
                + '<span class="add-on"><i class="icon-calendar"></i></span>'
                + '<span class="error-tooltip hide" rel="tooltip">'
                + '<i class="icon-exclamation-sign"></i>'
                + '</span>'
                + '</div>'
                + '</span>');

            field.usersDatePrefs = 'm/d/Y';
            field.dateValue = initialDate;

            field.$el.html(fieldTemplate({dateValue: initialDate}));
            field.model.set({editableDate: initialDate});
            field._setupDatepicker();
        });

        afterEach(function() {
            _.each(stubs, function(stub) {
                stub.restore();
            });
            stubs = [];
        });

        it("should not update the field model when given a invalid date", function() {
            // set the date value to an invalid date
            field.$el.find('input.datepicker').prop('value', newInvalidDate);

            // hideDatepicker has the field validation stuff to set or not set the model
            // passing in a dummy jQuery.Event
            field.hideDatepicker(jQuery.Event());

            // the model should NOT change with an invalid date, it should still be the initial date
            expect(field.model.get('editableDate')).toBe(initialDate);
        });

        it("should update the field model when given a valid date", function() {
            // set the date value to a valid date
            field.$el.find('input.datepicker').prop('value', newValidDate);

            // hideDatepicker has the field validation stuff to set or not set the model
            // passing in a dummy jQuery.Event
            field.hideDatepicker(jQuery.Event());

            // the model should change with a valid date, it should not still be the initial date
            expect(field.model.get('editableDate')).not.toBe(initialDate);
        });
    });

});
