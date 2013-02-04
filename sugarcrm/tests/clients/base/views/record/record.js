describe("Record View", function() {
    var moduleName = 'Cases',
        viewName = 'record',
        sinonSandbox, view,
        createListCollection;

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

        sinonSandbox = sinon.sandbox.create();

        view = SugarTest.createView("base", moduleName, "record", null, null);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
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

        it("should call decorateError on error fields during 'error:validation' events", function(){
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var descriptionField = _.find(view.fields, function(field){
                return field.name === 'description';
            });
            var stub = sinon.stub(descriptionField, 'decorateError');
            //Simulate a 'required' error on description field
            view.model.trigger('error:validation', {description: {required : true}});
            //Defer expectations since decoration is deferred
            _.defer(function(stub){
                expect(stub).toHaveBeenCalledWith({required: true});
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

        it("Should show save and cancel buttons and hide edit button when data changes", function() {

            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            view.render();
            view.editMode = true;
            view.model.set({
                name: 'Foo',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('save_button').getFieldElement().css('display')).toBe('none');
            expect(view.getField('cancel_button').getFieldElement().css('display')).toBe('none');
            expect(view.getField('edit_button').getFieldElement().css('display')).not.toBe('none');

            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            expect(view.getField('save_button').getFieldElement().css('display')).not.toBe('none');
            expect(view.getField('cancel_button').getFieldElement().css('display')).not.toBe('none');
            expect(view.getField('edit_button').getFieldElement().css('display')).toBe('none');
        });

        it("Should revert data back to the old value when the cancel button is clicked after data has been changed", function() {
            view.render();
            view.model.set({
                name: 'Foo',
                case_number: 123,
                description: 'Description'
            });
            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            expect(view.model.get('name')).toBe('Bar');
            view.$('a[name=cancel_button]').click();
            expect(view.model.get('name')).toBe('Foo');
        });
    });

    describe('_renderPanels with 1 column', function () {
        it("Should create panel grid with all fields on separate rows", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      1,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       ["description", "case_number", "type"]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(3);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(1);
            expect(results[2].length).toBe(1);
        });
    });

    describe('_renderPanels with 2 columns', function () {
        it("Should create panel grid with last row containing one empty column", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      2,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       ["description", "case_number", "type"]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(2);
            expect(results[0].length).toBe(2);
            expect(results[1].length).toBe(1);
        });

        it("Should create panel grid with second field on its own row where second field's span causes overflow", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      2,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       [
                        "case_number",
                        {
                            'name': 'description',
                            'span': 12
                        },
                        "type"
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(3);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(1);
            expect(results[2].length).toBe(1);
        });

        it("Should create panel grid with first field on its own row where the first field's span fills the row", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      2,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       [
                        {
                            'name': 'description',
                            'span': 12
                        },
                        "case_number",
                        "type"
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(2);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(2);
        });

        it("Should create panel grid with all fields fitting within the maximum allowable span when the panel def specifies a field whose span is out of range", function () {
            var results,
                panelDefs = [{
                                 "name":         "panel_body",
                                 "label":        "LBL_PANEL_2",
                                 "columns":      2,
                                 "labels":       true,
                                 "labelsOnTop":  false,
                                 "placeholders": true,
                                 "fields":       [
                                     {
                                         'name': 'description',
                                         'span': 12 // out of range for a panel with inline labels
                                     },
                                     "case_number",
                                     "type"
                                 ]
                             }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(2);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(2);
            expect(results[0][0].span).toBe(8); // the description field's span should have been reset to 8 since 12 won't fit
            expect(results[1][0].span).toBe(4); // verifying that the field span is calculated correctly when labels are inline
            expect(results[1][1].span).toBe(4); // verifying that the field span is calculated correctly when labels are inline
        });
    });

    describe('_renderPanels with 3 columns', function () {
        it("Should create panel grid with last field on its own row where the last field's span causes overflow", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      3,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       [
                        "case_number",
                        {
                            'name': 'description',
                            'span': 6
                        },
                        "type"
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(2);
            expect(results[0].length).toBe(2);
            expect(results[1].length).toBe(1);
        });

        it("Should create panel grid with first field on its own row where the first field's span fills the row", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      3,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       [
                        {
                            'name': 'description',
                            'span': 12
                        },
                        "case_number",
                        "type"
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(2);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(2);
        });

        it("Should create panel grid with all fields on their own row when the span of the second of three fields causes fills a row", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      3,
                    "labels":       true,
                    "labelsOnTop":  true,
                    "placeholders": true,
                    "fields":       [
                        "case_number",
                        {
                            'name': 'description',
                            'span': 10 // this field won't fit on the row with case_number
                        },
                        "type" // this field won't fit on the row with description because description's span was too large
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(3);
            expect(results[0].length).toBe(1);
            expect(results[1].length).toBe(1);
            expect(results[2].length).toBe(1);
        });
    });

    describe("_renderPanels corner cases", function() {
        it("Should create panel grid with no span0's when the column count is greater than 4 and labels are inline", function () {
            var results,
                panelDefs = [{
                    "name":         "panel_body",
                    "label":        "LBL_PANEL_2",
                    "columns":      5,
                    "labels":       true,
                    "labelsOnTop":  false,
                    "placeholders": true,
                    "fields":       [
                        "case_number",
                        "description",
                        "type",
                        "account_name",
                        "name"
                    ]
                }];

            view._renderPanels(panelDefs);
            results = panelDefs[0].grid;

            expect(results.length).toBe(1);
            expect(results[0][0].span).toBe(1);
            expect(results[0][0].labelSpan).toBe(1);
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
});
