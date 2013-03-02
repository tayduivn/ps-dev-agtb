describe("Editable View", function() {
    var moduleName = 'Accounts',
        viewName = 'editable',
        sinonSandbox, view, app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "description","case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();
        view = SugarTest.createView("base", moduleName, 'record', null, null);
    });

    afterEach(function() {
        SugarTest.app.view.reset();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        view = null;
    });

    it("Should toggle a single field to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        expect(randomField.tplName).toBe(view.action);
        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.toggleField(randomField);
        expect(randomField.tplName).toBe(view.action);
    });

    it("Should switch back to the previous mode when it triggers fieldClose", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        app.drawer = {
            isActive: function() {
                return false;
            }
        };

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.fieldClose({target: null}, randomField);
        expect(randomField.tplName).toBe(view.action);

        delete app.drawer;
    });

    it("Should toggle all selected fields to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        _.each(view.fields, function(field) {
            expect(field.tplName).toBe(view.action);
        });

        view.toggleFields(view.fields, true);

        waitsFor(function() {
            var last = _.last(_.keys(view.fields));
            return view.fields[last].tplName == 'edit';
        }, 'it took too long to wait switching view', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

});
