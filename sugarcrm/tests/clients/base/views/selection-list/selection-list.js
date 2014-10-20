describe("Base.View.SelectionList", function () {
    var view, layout, app, moduleName;
    beforeEach(function () {
        moduleName = 'Accounts';
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.loadComponent('base', 'view', 'selection-headerpane');
        SugarTest.testMetadata.addViewDefinition('list', {
            "panels":[
                {
                    "name":"panel",
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        { name: "phone_work" },
                        { name: "email1" },
                        { name: "phone_office" },
                        { name: "full_name" }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', moduleName, 'selection-list');
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it("Should add the selection field at the first field", function () {
        expect(view.leftColumns.length).toBe(1);
        expect('selection').toBe(view.leftColumns[0].type);
    });


    it("Should pass only the model's attributes that the user has the ACL view control", function () {
        var exptected_billing_phone = "111-000-9321",
            model = new Backbone.Model({
            id: "1234",
            name: "bob",
            billing_address: "should be undefined",
            billing_phone: exptected_billing_phone});
        var aclMapping = {
                billing_address: false,
                billing_phone: true
            },
            aclStub = sinon.stub(app.acl, 'hasAccessToModel', function(action, model, field) {
                return aclMapping[field];
            });
        var actualAttributes = {

        };
        app.drawer = {
            close: function(attributes) {
                actualAttributes = attributes;
            }
        };
        view.context.set("selection_model", model);

        expect(actualAttributes['id']).toBe("1234");
        expect(actualAttributes['value']).toBe("bob");
        expect(actualAttributes['billing_address']).toBeUndefined();
        expect(actualAttributes['billing_phone']).toBe(exptected_billing_phone);
        aclStub.restore();
        delete app.drawer;
    });

    it('should remove all links except rowactions', function(){
        var htmlBefore = '<a href="javascript:void(0)">unwrapped</a><a href="" class="rowaction">wrapped</a>',
            htmlAfter = 'unwrapped<a href="" class="rowaction">wrapped</a>';

        view.$el = $('<div>' + htmlBefore + '</div>');
        view.template = function() { return view.$el.html(); }
        view.render();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    it('should add preview row action', function(){
        var hasPreview = _.some(view.rightColumns, function(column) {
            return (column.event === "list:preview:fire");
        });
        expect(hasPreview).toBe(true);
    });

    describe('Multiselect', function() {
        beforeEach(function() {
            view.multiSelect = true;
//            var initializeEventsStub = sinon.collection.stub(view, 'initializeEvents', function() {
//               if (view.multiSelect) {
//                   view.context.on('selection:select:fire', view._selectMultipleAndClose, view);
//               } else {
//                   view.context.on('change:selection_model', view._selectAndClose, view);
//               }
//           });
        });

        it('should display the select button', function() {

        });

        it('should call _showMaxSelectedRecordsAlert if the number of selected items exceeds the maximum', function() {

            var model1 = new Backbone.Model({id: '1'}),
                model2 = new Backbone.Model({id: '2'}),
                model3 = new Backbone.Model({id: '3'}),
                models = new Backbone.Collection([model1, model2, model3]),
                showMaxSelectedRecordsAlert = sinon.collection.stub(view, '_showMaxSelectedRecordsAlert');
            view.maxSelectedRecords = 2;
            view.context.set('mass_collection', models);
            view._selectMultipleAndClose();

            expect(showMaxSelectedRecordsAlert).toHaveBeenCalled();
        });

        it('should call _selectMultipleAndClose when select_button is clicked', function() {
            var selectMultipleAndClose = sinon.collection.stub(view, '_selectMultipleAndClose');

            //click on select

//            expect(selectMultipleAndClose).toHaveBeenCalled();
        });

        it('should display checkboxes instead of radio buttons', function() {

        });

        it('should not call _selectAndClose when a checkbox is checked', function() {

//            expect(_selectAndClose).not.toHaveBeenCalled();
        });

        it('should get the attributes of every selected record', function() {
            var model1 = new Backbone.Model({id: '1'}),
                model2 = new Backbone.Model({id: '2'}),
                model3 = new Backbone.Model({id: '3'}),
                models = new Backbone.Collection([model1, model2, model3]);
            view.maxSelectedRecords = 20;
            view.context.set('mass_collection', models);
            var selections = view.context.get('mass_collection'),
                attributes = view._getCollectionAttributes(selections);

            expect(selections.models[0].attributes).toEqual(attributes[0]);
            expect(selections.models[1].attributes).toEqual(attributes[1]);
            expect(selections.models[2].attributes).toEqual(attributes[2]);
        });
    });
});
