describe("Record View", function() {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        sinonSandbox, view,
        createListCollection,
        buildRouteStub,
        oRouter;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', 'editable');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "buttons": [{
                "type":"button",
                "name":"cancel_button",
                "label":"LBL_CANCEL_BUTTON_LABEL",
                "css_class":"btn-invisible btn-link",
                "showOn":"edit"
            }, {
                "type":"actiondropdown",
                "name":"main_dropdown",
                "buttons":[{
                    "type":"rowaction",
                    "event":"button:edit_button:click",
                    "name":"edit_button",
                    "label":"LBL_EDIT_BUTTON_LABEL",
                    "primary":true,
                    "showOn":"view"
                }, {
                    "type":"rowaction",
                    "event":"button:save_button:click",
                    "name":"save_button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "primary":true,
                    "showOn":"edit"
                }, {
                    "type":"rowaction",
                    "name":"delete_button",
                    "label":"LBL_DELETE_BUTTON_LABEL",
                    "showOn":"view"
                }, {
                    "type":"rowaction",
                    "name":"duplicate_button",
                    "label":"LBL_DUPLICATE_BUTTON_LABEL",
                    "showOn":"view"
                }]
            }],
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name"]
            }, {
                "name": "panel_body",
                "label": "LBL_PANEL_2",
                "columns": 1,
                "labels": true,
                "labelsOnTop": false,
                "placeholders":true,
                "fields": ["description","case_number","type"]
            }, {
                "name": "panel_hidden",
                "hide": true,
                "labelsOnTop": false,
                "placeholders": true,
                "fields": ["created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function(){}};
        buildRouteStub = sinonSandbox.stub(SugarTest.app.router, 'buildRoute', function(module, id, action, params) {
            return module+'/'+id;
        });

        view = SugarTest.createView("base", moduleName, "record", null, null);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        SugarTest.app.router = oRouter;
        sinonSandbox.restore();
        view = null;
    });

    describe('Render', function() {
        it("Should not render any fields if model is empty", function() {
            view.render();

            expect(_.size(view.fields)).toBe(0);
        });


        it("Should render 8 editable fields and 6 buttons", function() {

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            var actual_field_length = _.keys(view.editableFields).length,
                actual_button_length = _.keys(view.buttons).length;
            expect(actual_field_length).toBe(8);
            expect(actual_button_length).toBe(6);
        });

        it("Should hide 4 editable fields", function() {
            var hiddenFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            _.each(view.editableFields, function(field) {
                if ((field.$el.closest('.hide').length === 1)) {
                    hiddenFields++;
                }
            });

            expect(hiddenFields).toBe(4);
        });

        it("Should place name field in the header", function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('name').$el.closest('.headerpane').length === 1).toBe(true);
        });

        it("Should not render any fields when a user doesn't have access to the data", function() {
            sinonSandbox.stub(SugarTest.app.acl, 'hasAccessToModel', function() {
                return false;
            });
            sinonSandbox.stub(SugarTest.app.error, 'handleRenderError', $.noop());

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(_.size(view.fields)).toBe(0);
        });

        it("Should not be editable when a user doesn't have write access on this field", function() {
            sinonSandbox.stub(SugarTest.app.acl, '_hasAccessToField', function(action, acls, field) {
                return field !== 'name';
            });
            sinonSandbox.stub(SugarTest.app.user, 'getAcls', function() {
                var acls = {};
                acls[moduleName] = {
                    edit: 'yes',
                    fields: {
                        name: {
                            write: 'no'
                        }
                    }
                };
                return acls;
            });

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            view.$('.more').click();
            var editableFields = 0;
            _.each(view.editableFields, function(field) {
                if (field.$el.closest('.record-cell').find('.record-edit-link-wrapper').length === 1) {
                    editableFields++;
                }
            });

            expect(editableFields).toBe(7);
            expect(_.size(view.editableFields)).toBe(7);
        });

        it("should call clearValidationErrors when Cancel is clicked", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var stub = sinon.stub(view, "clearValidationErrors");
            view.cancelClicked();
            //Defer expectations since decoration is deferred
            _.defer(function(stub){
                expect(stub.calledOnce).toBe(true);
                stub.restore();
            }, stub);
        });

        it("Should display all 8 editable fields when more link is clicked", function() {
            var hiddenFields = 0,
                visibleFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            view.$('.more').click();
            _.each(view.editableFields, function(field) {
                if (field.$el.closest('.hide').length === 1) {
                    hiddenFields++;
                } else {
                    visibleFields++;
                }

            });

            expect(hiddenFields).toBe(0);
            expect(visibleFields).toBe(8);
        });
    });

    describe('Edit', function() {
        it("Should toggle to an edit mode when a user clicks on the inline edit icon", function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('name').options.viewName).toBe(view.action);

            view.getField('name').$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(view.getField('name').options.viewName).toBe('edit');
        });

        it("Should toggle all editable fields to edit modes when a user clicks on the edit button", function() {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            _.each(view.editableFields, function(field) {
                expect(field.options.viewName).toBe(view.action);
            });

            view.context.trigger('button:edit_button:click');

            waitsFor(function() {
                return (_.last(view.editableFields)).options.viewName == 'edit';
            }, 'it took too long to wait switching view', 1000);

            runs(function() {
                _.each(view.editableFields, function(field) {
                    expect(field.options.viewName).toBe('edit');
                });
            });
        });

        it("Should ask the model to revert if cancel clicked", function() {
            view.render();
            view.model.revertAttributes = function(){};
            var revertSpy = sinon.spy(view.model, 'revertAttributes');
            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            view.$('a[name=cancel_button]').click();
            expect(revertSpy).toHaveBeenCalled();
        });
    });

    describe("render panels", function() {
        describe("labels are on top", function() {
            it("Should create a one-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field is a string, thus field.span is undefined
                        // result: should be converted to an object and field.span should be 12
                        "foo1",

                        // case: field.span >= 12
                        // result: field.span should be 12
                        {
                            name: "foo2",
                            span: 20
                        },

                        // case: field.span is 0
                        // result: field.span should remain 0
                        {
                            name: "foo3",
                            span: 0
                        },

                        // case: 0 < field.span < 12
                        // result: field.span should remain 6
                        {
                            name: "foo4",
                            span: 6
                        }
                    ],
                    panelDefs = [{
                        columns:     1,
                        labels:      true,
                        labelsOnTop: true,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // each field should be on its own row
                expect(results.length).toBe(fields.length);

                // case: field is a string, thus field.span is undefined
                expect(results[0][0].name).toBe("foo1");
                expect(results[0][0].span).toBe(12);

                // case: field.span >= 12
                expect(results[1][0].span).toBe(12);

                // case: field.span is 0
                expect(results[2][0].span).toBe(0);

                // case: 0 < field.span < 12
                expect(results[3][0].span).toBe(6);
            });

            it("Should create a two-column panel grid", function() {
                var results,
                    fields    = [
                        // case: the third field should be on its own row with an empty column
                        // result: the first two fields are one the first row and the third field is on its own row
                        "foo1",
                        "foo2",
                        "foo3",

                        // case: field.span is 8 and previous field's span is 6
                        // result: field overflows the row and ends up on the next row
                        {
                            name: "foo4",
                            span: 8
                        },

                        // case: field.span >= 12
                        // result: field.span should be 12; field overflows the row and its span dictates that the
                        // field be on its own row
                        {
                            name: "foo5",
                            span: 20
                        }
                    ],
                    panelDefs = [{
                        columns:     2,
                        labels:      true,
                        labelsOnTop: true,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // the number of rows found in this two-column grid
                expect(results.length).toBe(4);

                // case: the third field should be on its own row with an empty column
                expect(results[0].length).toBe(2);
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(6);

                // case: field.span is 8 and previous field's span is 6
                expect(results[2].length).toBe(1);
                expect(results[2][0].span).toBe(8);

                // result: field.span should be 12; field overflows the row and its span dictates that the
                // field be on its own row
                expect(results[3].length).toBe(1);
                expect(results[3][0].span).toBe(12);
            });

            it("Should create a three-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span is calculated for three fields such that they fit on one row
                        // result: three fields are on the same row
                        "foo1",
                        "foo2",
                        "foo3",

                        // case: field.span is 5 of the first field; field.span is 8 for the second field; field.span
                        // is calculated for the third field
                        // result: the first field is on its own row; the second field overflows the first row and is
                        // on the second row with the third field
                        {
                            name: "foo4",
                            span: 5
                        },
                        {
                            name: "foo5",
                            span: 8
                        },
                        "foo6",

                        // case: field.span >= 12
                        // result: field.span should be 12; field overflows the row and its span dictates that the
                        // field be on its own row
                        {
                            name: "foo7",
                            span: 12
                        },

                        // case: field.span for the second field is too large to fit on the first row and too large for
                        // the third field to fit on the second row
                        // result: three fields are on their own rows
                        "foo8",
                        {
                            name: "foo8",
                            span: 10
                        },
                        "foo9"
                    ],
                    panelDefs = [{
                        columns:     3,
                        labels:      true,
                        labelsOnTop: true,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // the number of rows found in this three-column grid
                expect(results.length).toBe(7);

                // result: three fields are on the same row
                expect(results[0].length).toBe(3);

                // case: field.span is 5 of the first field; field.span is 8 for the second field; field.span
                // is calculated for the third field
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(5);
                expect(results[1][0].labelSpan).toBe(1);
                expect(results[2].length).toBe(2);
                expect(results[2][0].span).toBe(8);
                expect(results[2][0].labelSpan).toBe(1);
                expect(results[2][1].span).toBe(4);
                expect(results[2][1].labelSpan).toBe(1);

                // case: field.span >= 12
                expect(results[3].length).toBe(1);
                expect(results[3][0].span).toBe(12);

                // case: field.span for the second field is too large to fit on the first row and too large for
                // the third field to fit on the second row
                expect(results[4].length).toBe(1);
                expect(results[5].length).toBe(1);
                expect(results[6].length).toBe(1);
            });
        });

        describe("labels are inline", function() {
            it("Should create a one-column panel grid", function() {
                var results,
                    fields    = [
                        // case: field.span and field.labelSpan are undefined
                        // result: field.span should be 8 and field.labelSpan should be 4
                        "foo1",

                        // case: field.span is 0 and field.labelSpan is 0
                        // result: field.span should remain 0 and field.labelSpan should remain 0
                        {
                            name: "foo2",
                            span: 0,
                            labelSpan: 0
                        },

                        // case: field.span <= 8 and field.labelSpan is defined
                        // result: field.span and field.labelSpan should not change, even if
                        // field.span + field.labelSpan > 12
                        {
                            name: "foo3",
                            span: 7,
                            labelSpan: 7
                        },

                        // case: field.span > 8 and field.labelSpan is defined
                        // result: field.span should be 8 and field.labelSpan should not change
                        {
                            name: "foo4",
                            span: 10,
                            labelSpan: 2
                        },

                        // case: field.span >= 12
                        // result: field.span should be 8 and field.labelSpan should be 4
                        {
                            name: "foo5",
                            span: 20
                        },

                        // case: field.span is 12, field.labelSpan is undefined, and field.dismiss_label is true
                        // result: field.span should be 12 and field.labelSpan should be 0
                        {
                            name: "foo6",
                            span: 12,
                            dismiss_label: true
                        },

                        // case: field.span is 4, field.labelSpan is undefined, and field.dismiss_label is true
                        // result: field.span should be 8 and field.labelSpan should be 0
                        {
                            name: "foo7",
                            span: 4,
                            dismiss_label: true
                        },

                        // case: field.span is 8, field.labelSpan is undefined, and field.dismiss_label is true
                        // result: field.span should be 12 and field.labelSpan should be 0
                        {
                            name: "foo8",
                            span: 10,
                            dismiss_label: true
                        },

                        // case: field.span is 7, field.labelSpan is 7, and field.dismiss_label is true
                        // result: field.span should be 12 and field.labelSpan should be 0
                        {
                            name: "foo9",
                            span: 7,
                            labelSpan: 7,
                            dismiss_label: true
                        }
                    ],
                    panelDefs = [{
                        columns:     1,
                        labels:      true,
                        labelsOnTop: false,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // each field should be on its own row
                expect(results.length).toBe(fields.length);

                // case: field.span and field.labelSpan are undefined
                expect(results[0][0].span).toBe(8);
                expect(results[0][0].labelSpan).toBe(4);

                // case: field.span is 0 and field.labelSpan is 0
                expect(results[1][0].span).toBe(0);
                expect(results[1][0].labelSpan).toBe(0);

                // case: field.span <= 8 and field.labelSpan is defined
                expect(results[2][0].span).toBe(7);
                expect(results[2][0].labelSpan).toBe(7);

                // case: field.span > 8 and field.labelSpan is defined
                expect(results[3][0].span).toBe(8);
                expect(results[3][0].labelSpan).toBe(2);

                // case: field.span >= 12
                expect(results[4][0].span).toBe(8);
                expect(results[4][0].labelSpan).toBe(4);

                // case: field.span is 12, field.labelSpan is undefined, and field.dismiss_label is true
                expect(results[5][0].span).toBe(12);
                expect(results[5][0].labelSpan).toBe(0);

                // case: field.span is 4, field.labelSpan is undefined, and field.dismiss_label is true
                expect(results[6][0].span).toBe(8);
                expect(results[6][0].labelSpan).toBe(0);

                // case: field.span is 8, field.labelSpan is undefined, and field.dismiss_label is true
                expect(results[7][0].span).toBe(12);
                expect(results[7][0].labelSpan).toBe(0);

                // case: field.span is 7, field.labelSpan is 7, and field.dismiss_label is true
                expect(results[8][0].span).toBe(12);
                expect(results[8][0].labelSpan).toBe(0);
            });

            it("Should create a two-column panel grid", function() {
                var results,
                    fields    = [
                        // case: If the field span is defined to be 12 and the label span is defined to be 0, 1 or 2
                        // (or it is undefined and gets calculated to be 1 or 2), then there is no guarantee that the
                        // field will be on its own row. If the field that follows has a field span defined to be 1 and
                        // a label span defined to be 1, then both fields will fit within the maximum row span of 12.
                        // result: The first field should have a field span of 8 and a label span of 2. The second
                        // field should have a field span of 1 and a label span of 1. The sum of these spans is 12, so
                        // both fields will be on the same row.
                        {
                            name: "foo1",
                            span: 12,
                            labelSpan: 2
                        },
                        {
                            name: "foo2",
                            span: 1,
                            labelSpan: 1
                        },

                        // case: field.span >= 12 and field.labelSpan is 4
                        // result: field.span should be 8 and field.labelSpan should be 4; field overflows the row and
                        // its span dictates that the field be on its own row
                        {
                            name: "foo3",
                            span: 20,
                            labelSpan: 4
                        },

                        // case: field.span and field.labelSpan are undefined for both fields
                        // result: field.span should be 4 and field.labelSpan should be 2 for both fields; both fields
                        // should fit on one row
                        "foo4",
                        "foo5",

                        // case: the sum of the spans for the first two fields and their labels < 12, and a third
                        // field is too large to fit on the row
                        // result: The first field should have a field span of 3 and a label span of 2. The second
                        // field should have a field span of 3 and a label span of 1. The sum of these spans is 12, so
                        // both fields will be on the same row. The third field naturally overflows the row and is
                        // added to the next row.
                        {
                            name: "foo6",
                            span: 3,
                            labelSpan: 2
                        },
                        {
                            name: "foo7",
                            span: 3,
                            labelSpan: 1
                        },
                        "foo8",

                        // case: field.span and field.labelSpan are undefined, and field.dismiss_label is true
                        // result: field.span should be 6 and field.labelSpan should be 0
                        {
                            name: "foo9",
                            dismiss_label: true
                        }
                    ],
                    panelDefs = [{
                        columns:     2,
                        labels:      true,
                        labelsOnTop: false,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // the number of rows found in this two-column grid
                expect(results.length).toBe(5);

                // case: If the field span is defined to be 12 and the label span is defined to be 0, 1 or 2
                // (or it is undefined and gets calculated to be 1 or 2), then there is no guarantee that the
                // field will be on its own row. If the field that follows has a field span defined to be 1 and
                // a label span defined to be 1, then both fields will fit within the maximum row span of 12.
                expect(results[0].length).toBe(2);
                expect(results[0][0].span).toBe(8);
                expect(results[0][0].labelSpan).toBe(2);
                expect(results[0][1].span).toBe(1);
                expect(results[0][1].labelSpan).toBe(1);

                // case: field.span >= 12 and field.labelSpan is 4
                expect(results[1].length).toBe(1);
                expect(results[1][0].span).toBe(8);
                expect(results[1][0].labelSpan).toBe(4);

                // case: field.span and field.labelSpan are undefined for both fields
                expect(results[2].length).toBe(2);
                expect(results[2][0].span).toBe(4);
                expect(results[2][0].labelSpan).toBe(2);
                expect(results[2][1].span).toBe(4);
                expect(results[2][1].labelSpan).toBe(2);

                // case: the sum of the spans for the first two fields and their labels < 12, and a third
                // field is too large to fit on the row
                expect(results[3].length).toBe(2);
                expect(results[3][0].span).toBe(3);
                expect(results[3][0].labelSpan).toBe(2);
                expect(results[3][1].span).toBe(3);
                expect(results[3][1].labelSpan).toBe(1);
                expect(results[4].length).toBe(2); // 2 because the next test case adds a field to the fifth row
                expect(results[4][0].span).toBe(4);
                expect(results[4][0].labelSpan).toBe(2);

                // case: field.span and field.labelSpan are undefined, and field.dismiss_label is true
                expect(results[4][1].span).toBe(6);
                expect(results[4][1].labelSpan).toBe(0);
            });

            it("Should create a five-column panel grid with no field.span's or label.span's less than 1", function() {
                var results,
                    fields    = [
                        // case:
                        // result: field.span should be 8 and field.labelSpan should be 4; field overflows the row and
                        // its span dictates that the field be on its own row
                        "foo1",
                        "foo2",
                        "foo3",
                        "foo4",
                        "foo5"
                    ],
                    panelDefs = [{
                        columns:     5,
                        labels:      true,
                        labelsOnTop: false,
                        fields:      fields
                    }];

                view._renderPanels(panelDefs);
                results = panelDefs[0].grid;

                // the number of rows found in this five-column grid
                expect(results.length).toBe(1);

                // When displaying a five-column grid in which on fields have defined field.span or field.labelSpan,
                // the spans are calculated using Math.Floor, which leads to values that are 0. Unless a span was
                // intentionally defined to be 0, it should be assumed that 0 is an invalid span -- a span of 0 doesn't
                // make sense and isn't supported by Twitter Boostrap. Therefore, the spans should be set to a minimum
                // value of 1 if they are calculated to be less than 1.
                expect(results[0].length).toBe(5);

                _.each(results[0], function(field) {
                    expect(field.span).toBe(1);
                    expect(field.labelSpan).toBe(1);
                }, this);
            });
        });

        describe('Header panel', function() {
            it('Should set isAvatar to false if the header doesn\'t the picture field', function() {
                view._renderPanels(view.meta.panels);
                expect(view.meta.panels[0].isAvatar).toBeFalsy();
            });

            it('Should set isAvatar to true if the header contains the picture field', function() {
                var meta = {
                            "panels": [{
                                "name": "panel_header",
                                "header": true,
                                "fields": ["picture","name"]
                            }, {
                                "name": "panel_body",
                                "label": "LBL_PANEL_2",
                                "columns": 1,
                                "labels": true,
                                "labelsOnTop": false,
                                "placeholders":true,
                                "fields": ["description","case_number","type"]
                            }, {
                                "name": "panel_hidden",
                                "hide": true,
                                "labelsOnTop": false,
                                "placeholders": true,
                                "fields": ["created_by","date_entered","date_modified","modified_user_id"]
                            }]
                        };
                view._renderPanels(meta.panels);
                expect(meta.panels[0].isAvatar).toBeTruthy();
            });
        });
    });

    describe('Switching to next and previous record', function() {

        beforeEach(function() {
            createListCollection = function(nbModels, offsetSelectedModel) {
                     view.context.set('listCollection', new Backbone.Collection());
                     view.collection = new Backbone.Collection();

                     var modelIds = [];
                     for (var i=0;i<=nbModels;i++) {
                         var model = new Backbone.Model(),
                             id = i + '__' + Math.random().toString(36).substr(2,16);

                         model.set({id: id});
                         if (i === offsetSelectedModel) {
                             view.model.set(model.toJSON());
                             view.collection.add(model);
                         }
                         view.context.get('listCollection').add(model);
                         modelIds.push(id);
                     }
                     return modelIds;
                 };
        });

        it("Should find previous and next model from list collection", function() {
            var modelIds = createListCollection(5, 3);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).toBeDefined();
            expect(view.collection.next).toBeDefined();
            expect(view.collection.previous.get('id')).toEqual(modelIds[2]);
            expect(view.collection.next.get('id')).toEqual(modelIds[4]);
        });

        it("Should find previous model from list collection", function() {
            var modelIds = createListCollection(5, 5);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).toBeDefined();
            expect(view.collection.next).not.toBeDefined();
            expect(view.collection.previous.get('id')).toEqual(modelIds[4]);
        });

        it("Should find next model from list collection", function() {
            var modelIds = createListCollection(5, 0);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).not.toBeDefined();
            expect(view.collection.next).toBeDefined();
            expect(view.collection.next.get('id')).toEqual(modelIds[1]);
        });
    });

    describe('duplicateClicked', function(){
        var triggerStub, openStub, closeStub, expectedModel = {id:'abcd12345'};

        beforeEach(function(){
            closeStub = sinon.stub();
            triggerStub = sinon.stub(Backbone.Model.prototype, 'trigger', function(event, model){
                if(event == "duplicate:before"){
                    expect(model.get("name")).toEqual(view.model.get("name"));
                    expect(model.get("description")).toEqual(view.model.get("description"));
                    expect(model).toNotBe(view.model);
                }
            });
            SugarTest.app.drawer = {
                open: function(){},
                close: function(){}
            };
            openStub = sinon.stub(SugarTest.app.drawer, "open", function(opts, closeCallback){
                expect(opts.context.model).toBeDefined();
                expect(opts.layout).toEqual("create");
                expect(opts.context.model.get("name")).toEqual(view.model.get("name"));
                expect(opts.context.model.get("description")).toEqual(view.model.get("description"));
                expect(opts.context.model).toNotBe(view.model);
                if (closeCallback) closeStub(expectedModel);
            });
        });
        afterEach(function(){
            if(triggerStub){
                triggerStub.restore();
            }
            if(openStub){
                openStub.restore();
            }
        });
        it("should trigger 'duplicate:before' on model prior to opening create drawer", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith("duplicate:before")).toBe(true);
            expect(openStub.called).toBe(true);
            expect(triggerStub.calledBefore(openStub)).toBe(true);
        });

        it(" should pass model to mutate with 'duplicate:before' event", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith('duplicate:before')).toBe(true);
            //Further expectations in stub
        });

        it("should fire 'drawer:create:fire' event with copied model set on context", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();
            view.duplicateClicked();
            expect(openStub.called).toBe(true);
            expect(openStub.lastCall.args[0].context.model.get("name")).toEqual(view.model.get("name"));
        });

        it("should call close callback", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                module: "Bugs"
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();
            view.duplicateClicked();
            expect(closeStub.lastCall.args[0].id).toEqual(expectedModel.id);
        });
    });

    describe('Field labels', function(){
        it("should be hidden on view for headerpane fields", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=name]').css('display')).toBe('none');
        });

        it("should be shown on view for non-headerpane fields", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=description]').css('display')).not.toBe('none');
        });

        it("should be shown on edit for headerpane fields", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            view.getField('name').$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(view.$('.record-label[data-name=name]').css('display')).not.toBe('none');
        });
    });
});
