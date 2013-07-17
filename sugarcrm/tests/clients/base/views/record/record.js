describe("Record View", function () {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        sinonSandbox,
        view,
        createListCollection,
        buildRouteStub,
        oRouter,
        buildGridsFromPanelsMetadataStub;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "buttons": [
                {
                    "type": "button",
                    "name": "cancel_button",
                    "label": "LBL_CANCEL_BUTTON_LABEL",
                    "css_class": "btn-invisible btn-link",
                    "showOn": "edit"
                },
                {
                    "type": "actiondropdown",
                    "name": "main_dropdown",
                    "buttons": [
                        {
                            "type": "rowaction",
                            "event": "button:edit_button:click",
                            "name": "edit_button",
                            "label": "LBL_EDIT_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "view",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "event": "button:save_button:click",
                            "name": "save_button",
                            "label": "LBL_SAVE_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "edit",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "name": "delete_button",
                            "label": "LBL_DELETE_BUTTON_LABEL",
                            "showOn": "view",
                            "acl_action":"delete"
                        },
                        {
                            "type": "rowaction",
                            "name": "duplicate_button",
                            "label": "LBL_DUPLICATE_BUTTON_LABEL",
                            "showOn": "view",
                            "acl_action":"create"
                        }
                    ]
                }
            ],
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": [{name: "name", span: 8, labelSpan: 4}],
                    "labels": true
                },
                {
                    "name": "panel_body",
                    "label": "LBL_PANEL_2",
                    "columns": 1,
                    "labels": true,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "description", type: "base", label: "description", span: 8, labelSpan: 4},
                        {name: "case_number", type: "float", label: "case_number", span: 8, labelSpan: 4},
                        {name: "type", type: "text", label: "type", span: 8, labelSpan: 4}
                    ]
                },
                {
                    "name": "panel_hidden",
                    "hide": true,
                    "columns": 1,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "created_by", type: "date", label: "created_by", span: 8, labelSpan: 4},
                        {name: "date_entered", type: "date", label: "date_entered", span: 8, labelSpan: 4},
                        {name: "date_modified", type: "date", label: "date_modified", span: 8, labelSpan: 4},
                        {name: "modified_user_id", type: "date", label: "modified_user_id", span: 8, labelSpan: 4}
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        oRouter = SugarTest.app.router;
        SugarTest.app.router = {buildRoute: function () {
        }};
        buildRouteStub = sinonSandbox.stub(SugarTest.app.router, 'buildRoute', function (module, id, action, params) {
            return module + '/' + id;
        });

        view = SugarTest.createView("base", moduleName, "record", null, null);

        buildGridsFromPanelsMetadataStub = sinon.stub(view, "_buildGridsFromPanelsMetadata", function(panels) {
            view.hiddenPanelExists = true;

            // The panel grid contains references to the actual fields found in panel.fields, so the fields must
            // be modified to include the field attributes that would be calculated during a normal render
            // operation and then added to the grid in the correct row and column.
            panels[0].isAvatar  = false;
            panels[0].grid      = [[panels[0].fields[0]]];
            panels[1].grid      = [
                [panels[1].fields[0]],
                [panels[1].fields[1]],
                [panels[1].fields[2]]
            ];
            panels[2].grid      = [
                [panels[2].fields[0]],
                [panels[2].fields[1]],
                [panels[2].fields[2]],
                [panels[2].fields[3]]
            ];
        });
    });

    afterEach(function () {
        buildGridsFromPanelsMetadataStub.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        SugarTest.app.router = oRouter;
        sinonSandbox.restore();
        view = null;
    });

    describe('Render', function () {
        it("Should not render any fields if model is empty", function () {
            view.render();

            expect(_.size(view.fields)).toBe(0);
        });


        it("Should render 8 editable fields and 6 buttons", function () {

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

        it("Should hide 4 editable fields", function () {
            var hiddenFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            _.each(view.editableFields, function (field) {
                if ((field.$el.closest('.hide').length === 1)) {
                    hiddenFields++;
                }
            });

            expect(hiddenFields).toBe(4);
        });

        it("Should place name field in the header", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('name').$el.closest('.headerpane').length === 1).toBe(true);
        });

        it("Should not render any fields when a user doesn't have access to the data", function () {
            sinonSandbox.stub(SugarTest.app.acl, 'hasAccessToModel', function () {
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

        it("should call clearValidationErrors when Cancel is clicked", function () {
            var clock = sinon.useFakeTimers();
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var stub = sinon.stub(view, "clearValidationErrors");
            view.cancelClicked();
            //Use sinon clock to delay expectations since decoration is deferred
            clock.tick(20);
            expect(stub.calledOnce).toBe(true);
            stub.restore();
            clock.restore();
        });

        it("Should display all 8 editable fields when more link is clicked", function () {
            var hiddenFields = 0,
                visibleFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            view.$('.more').click();
            _.each(view.editableFields, function (field) {
                if (field.$el.closest('.hide').length === 1) {
                    hiddenFields++;
                } else {
                    visibleFields++;
                }

            });

            expect(hiddenFields).toBe(0);
            expect(visibleFields).toBe(8);
        });

        it("Should not be editable when this field is in the noEditFields array", function () {
            var noEditFields = ["name", "created_by", "date_entered", "date_modified", "case_number"];

            _.each(view.meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(noEditFields, field.name) >= 0) {
                        view.noEditFields.push(field.name);
                    }
                }, this);
            }, this);

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            view.$('.more').click();

            var editableFields = 0;
            _.each(view.editableFields, function (field) {
                if (field.$el.closest(".record-cell").find(".record-edit-link-wrapper").length === 1) {
                    editableFields++;
                }
            });

            expect(editableFields).toBe(3);
            expect(_.size(view.editableFields)).toBe(3);
        });
    });

    describe('Edit', function () {
        it("Should toggle to an edit mode when a user clicks on the inline edit icon", function () {
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

        it("Should toggle all editable fields to edit modes when a user clicks on the edit button", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            _.each(view.editableFields, function (field) {
                expect(field.options.viewName).toBe(view.action);
            });

            view.context.trigger('button:edit_button:click');

            waitsFor(function () {
                return (_.last(view.editableFields)).options.viewName === 'edit';
            }, 'it took too long to wait switching view', 1000);

            runs(function () {
                _.each(view.editableFields, function (field) {
                    expect(field.options.viewName).toBe('edit');
                });
            });
        });

        it("Should ask the model to revert if cancel clicked", function () {
            view.render();
            view.model.revertAttributes = function () {
            };
            var revertSpy = sinon.spy(view.model, 'revertAttributes');
            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            view.$('a[name=cancel_button]').click();
            expect(revertSpy).toHaveBeenCalled();
        });
    });

    describe("build grids", function() {
        var hasAccessToModelStub,
            readonlyFields = ["created_by", "date_entered", "date_modified"],
            aclFailFields  = ["case_number"];

        beforeEach(function() {
            buildGridsFromPanelsMetadataStub.restore();
            hasAccessToModelStub = sinon.stub(SugarTest.app.acl, "hasAccessToModel", function (method, model, field) {
                return _.indexOf(aclFailFields, field) < 0;
            });
        });

        afterEach(function() {
            hasAccessToModelStub.restore();
        });

        describe('Header panel', function () {
            it('Should set isAvatar to false if the header doesn\'t the picture field', function () {
                view._buildGridsFromPanelsMetadata(view.meta.panels);
                expect(view.meta.panels[0].isAvatar).toBeFalsy();
            });

            it('Should set isAvatar to true if the header contains the picture field', function () {
                var meta = {
                    "panels": [
                        {
                            "name": "panel_header",
                            "header": true,
                            "fields": ["picture", "name"]
                        },
                        {
                            "name": "panel_body",
                            "label": "LBL_PANEL_2",
                            "columns": 1,
                            "labels": true,
                            "labelsOnTop": false,
                            "placeholders": true,
                            "fields": ["description", "case_number", "type"]
                        },
                        {
                            "name": "panel_hidden",
                            "hide": true,
                            "labelsOnTop": false,
                            "placeholders": true,
                            "fields": ["created_by", "date_entered", "date_modified", "modified_user_id"]
                        }
                    ]
                };
                view._buildGridsFromPanelsMetadata(meta.panels);
                expect(meta.panels[0].isAvatar).toBeTruthy();
            });
        });

        it("Should convert string fields to objects", function() {
            var meta = {
                panels: [{
                    fields: ["description"]
                }]
            };
            view._buildGridsFromPanelsMetadata(meta.panels);
            expect(meta.panels[0].fields[0].name).toBe("description");
        });

        it("Should add readonly fields and acl fail fields to the noEditFields array", function () {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: "date_modified"}
                    ]
                }]
            };

            _.each(meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(readonlyFields, field.name) >= 0) {
                        field.readonly = true;
                    }
                }, this);
            }, this);

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = _.union(readonlyFields, aclFailFields);

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });

        it("Should add a field to the noEditFields array when a user doesn't have write access on the field", function () {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: "date_modified"}
                    ]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.acl, "_hasAccessToField", function (action, acls, field) {
                return field !== 'case_number';
            });
            sinonSandbox.stub(SugarTest.app.user, "getAcls", function () {
                var acls = {};
                acls[moduleName] = {
                    edit: "yes",
                    fields: {
                        name: {
                            write: "no"
                        }
                    }
                };
                return acls;
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = aclFailFields;

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });
    });

    describe('Switching to next and previous record', function () {

        beforeEach(function () {
            createListCollection = function (nbModels, offsetSelectedModel) {
                view.context.set('listCollection', new Backbone.Collection());
                view.collection = new Backbone.Collection();

                var modelIds = [];
                for (var i = 0; i <= nbModels; i++) {
                    var model = new Backbone.Model(),
                        id = i + '__' + Math.random().toString(36).substr(2, 16);

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

        it("Should find previous and next model from list collection", function () {
            var modelIds = createListCollection(5, 3);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).toBeDefined();
            expect(view.collection.next).toBeDefined();
            expect(view.collection.previous.get('id')).toEqual(modelIds[2]);
            expect(view.collection.next.get('id')).toEqual(modelIds[4]);
        });

        it("Should find previous model from list collection", function () {
            var modelIds = createListCollection(5, 5);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).toBeDefined();
            expect(view.collection.next).not.toBeDefined();
            expect(view.collection.previous.get('id')).toEqual(modelIds[4]);
        });

        it("Should find next model from list collection", function () {
            var modelIds = createListCollection(5, 0);
            view.showPreviousNextBtnGroup();
            expect(view.collection.previous).not.toBeDefined();
            expect(view.collection.next).toBeDefined();
            expect(view.collection.next.get('id')).toEqual(modelIds[1]);
        });
    });

    describe('duplicateClicked', function () {
        var triggerStub, openStub, closeStub, expectedModel = {id: 'abcd12345'};

        beforeEach(function () {
            closeStub = sinon.stub();
            triggerStub = sinon.stub(Backbone.Model.prototype, 'trigger', function (event, model) {
                if (event === "duplicate:before") {
                    expect(model.get("name")).toEqual(view.model.get("name"));
                    expect(model.get("description")).toEqual(view.model.get("description"));
                    expect(model).toNotBe(view.model);
                }
            });
            SugarTest.app.drawer = {
                open: function () {
                },
                close: function () {
                }
            };
            openStub = sinon.stub(SugarTest.app.drawer, "open", function (opts, closeCallback) {
                expect(opts.context.model).toBeDefined();
                expect(opts.layout).toEqual("create-actions");
                expect(opts.context.model.get("name")).toEqual(view.model.get("name"));
                expect(opts.context.model.get("description")).toEqual(view.model.get("description"));
                expect(opts.context.model).toNotBe(view.model);
                if (closeCallback) {
                    closeStub(expectedModel);
                }
            });
        });
        afterEach(function () {
            if (triggerStub) {
                triggerStub.restore();
            }
            if (openStub) {
                openStub.restore();
            }
        });
        it("should trigger 'duplicate:before' on model prior to opening create drawer", function () {
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

        it(" should pass model to mutate with 'duplicate:before' event", function () {
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

        it("should fire 'drawer:create:fire' event with copied model set on context", function () {
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

        it("should call close callback", function () {
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

    describe('Field labels', function () {
        it("should be hidden on view for headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=name]').css('display')).toBe('none');
        });

        it("should be shown on view for non-headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=description]').css('display')).not.toBe('none');
        });

        it("should be shown on edit for headerpane fields", function () {
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

    describe('Set and restore last state', function(){
        describe('more_less toggle', function(){
            it("should set state when more/less is toggled", function(){
                var setStateStub = sinon.stub(SugarTest.app.user.lastState, "set", $.noop());
                view.toggleMoreLess();
                expect(setStateStub.calledOnce).toBe(true);
                view.toggleMoreLess();
                expect(setStateStub.calledTwice).toBe(true);
                setStateStub.restore();
            });
            it("should toggleMoreLess during render when last state is 'less'", function(){
                var getState = "less";
                var getStateStub = sinon.stub(SugarTest.app.user.lastState, "get", function(){
                    return getState;
                });
                var toggleMoreLessStub = sinon.stub(view, 'toggleMoreLess', $.noop());
                view.render();
                expect(getStateStub.calledOnce).toBe(true);
                expect(toggleMoreLessStub.calledOnce).toBe(true);
                toggleMoreLessStub.reset();
                getStateStub.reset();

                getState = "more";
                view.render();
                expect(getStateStub.calledOnce).toBe(true);
                expect(toggleMoreLessStub.called).toBe(false);

                toggleMoreLessStub.restore();
                getStateStub.restore();
            });
        });
    });

    describe('Set Button States', function () {
        it('should show buttons where the showOn states match', function() {
            // we need our buttons to be initialized before we can test them
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            }, {
                silent:true
            });

            // load up with our spies to detect nefarious activity
            _.each(view.buttons,function(button) {
                sinonSandbox.spy(button,'hide');
                sinonSandbox.spy(button,'show');
            });

            view.setButtonStates(view.STATE.EDIT);

            // with access, assume the show/hide are based solely on showOn
            _.each(view.buttons,function(button) {
                var shouldHide = !!button.def.showOn && (button.def.showOn !== view.STATE.EDIT);
                expect(button.hide.called).toEqual(shouldHide);
                expect(button.show.called).toEqual(!shouldHide);
            });
        });

    });

    describe('hasUnsavedChanges', function() {
        it('should NOT warn unsaved changes when synced values are matched with current model value', function() {
            var attrs = {
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            };
            view.model._setSyncedAttributes(attrs);
            view.model.set(attrs);
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
        });
        it('should warn unsaved changes among the synced attributes', function() {
            view.model._setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);
        });
        it('should warn unsaved changes ONLY IF the changes are editable fields', function() {
            view.model._setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description',
                non_editable: 'system value'
            });
            //un-editable field
            view.model.set({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
            //Changed non-editable field
            view.model.set({
                non_editable: 'user value'
            });
            actual = view.hasUnsavedChanges();
            var editableFields = _.pluck(view.editableFields, 'name');
            expect(_.contains(editableFields, 'non_editable')).toBe(false);
            expect(actual).toBe(false);
            //Changed editable field
            view.model.set({
                description: 'Changed description'
            });
            actual = view.hasUnsavedChanges();
            expect(_.contains(editableFields, 'description')).toBe(true);
            expect(actual).toBe(true);
        });

    });
});
