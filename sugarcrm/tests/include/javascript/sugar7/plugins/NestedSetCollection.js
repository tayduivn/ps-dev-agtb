describe('Plugins.NestedSetCollection', function() {
    var module = 'KBSContents',
        fieldDef = {
            category_root: '0',
            module_root: module
        },
        app, field, renderTreeStub, sinonSandbox, treeData;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nested-set');
        SugarTest.loadPlugin('NestedSetCollection');
        SugarTest.loadHandlebarsTemplate('nested-set', 'field', 'base', 'edit');

        SugarTest.testMetadata.set();
        app.data.declareModels();

        sinonSandbox.stub(app.metadata, 'getModule', function() {
            return {
                fields: {
                    id: {
                        label: 'id',
                        name: 'id'
                    }
                }
            };
        });

        treeData = SugarTest.loadFixture('tree', '../tests/modules/Categories/fixtures');

        field = SugarTest.createField('base', 'nested-set', 'nested-set', 'edit', fieldDef, module);
        renderTreeStub = sinonSandbox.stub(field, '_renderTree');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    it('The plugin should replace collection.', function() {
        field.render();
        expect(field.collection instanceof app.NestedSetCollection).toBeTruthy();
    });

    it('Nested collection should be populated correctly.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        var firstModel = field.collection.get('1'),
            secondModel = field.collection.get('2'),
            childrenCollection = firstModel.children,
            thirdModel = childrenCollection.get('3'),
            fourthModel = childrenCollection.get('4');

        expect(firstModel instanceof app.NestedSetBean).toBeTruthy();
        expect(secondModel instanceof app.NestedSetBean).toBeTruthy();
        expect(childrenCollection instanceof app.NestedSetCollection).toBeTruthy();
        expect(thirdModel instanceof app.NestedSetBean).toBeTruthy();
        expect(fourthModel instanceof app.NestedSetBean).toBeTruthy();
    });

    it('Nested collection add and remove.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        var model = new app.NestedSetBean({
            id: '5',
            name: 'Fifth',
            date_entered: '2014-10-13 10:11:51',
            date_modified: '2014-10-13 10:11:51',
            created_by: '1',
            deleted: '0',
            root: '76c5ad26-21db-1be5-85ee-54258f68dd4a',
            lft: '1',
            rgt: '1',
            level: '1',
            data: 'fifth',
            _module: module,
            metadata: {id: '5'},
            attr: {'data-id': '5', 'data-level': '1'},
            children: []
        });

        field.collection.add(model);
        expect(field.collection.get('5') instanceof app.NestedSetBean).toBeTruthy();

        field.collection.remove(model);
        expect(field.collection.get('5')).toBeUndefined();
    });

    it('Nested collection get child by ID.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        var childModel = field.collection.getChild({id: '4'});
        expect(childModel instanceof app.NestedSetBean).toBeTruthy();
    });

});
