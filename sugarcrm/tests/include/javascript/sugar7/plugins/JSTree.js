describe('Plugins.JSTree', function() {
    var module = 'KBContents',
        fieldDef = {
            category_root: '0',
            module_root: module
        },
        app, field, renderTreeStub, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nestedset');
        SugarTest.loadPlugin('JSTree');
        SugarTest.loadPlugin('NestedSetCollection');
        SugarTest.loadHandlebarsTemplate('nestedset', 'field', 'base', 'edit');

        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', 'nestedset', 'nestedset', 'edit', fieldDef, module);
        renderTreeStub = sinonSandbox.stub(field, '_renderTree');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        delete app.plugins.plugins['field']['JSTree'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
    });

    // Skipped because JSTree::createTree() uses _recursiveReplaceHTMLChars before the key 'data' exists.
    xit('Tree should convert special chars.', function() {
        field.render();
        sinonSandbox.stub(field, '_toggleVisibility');
        sinonSandbox.stub(field, '_loadContextMenu', function() {
            return {};
        });

        var data = [
                {
                    id: 'b452afe6-e6d8-1f7c-5789-543ba51e30f5',
                    name: '&amp;',
                    created_by: '1',
                    deleted: '0',
                    root: '76c5ad26-21db-1be5-85ee-54258f68dd4a',
                    lft: '8',
                    rgt: '9',
                    level: '1',
                    children: {next_offset: -1, records: []}
                },
                {
                    id: 'e22900cc-abbc-31b9-bba4-543ba5adeb1d',
                    name: '&lt;',
                    created_by: '1',
                    deleted: '0',
                    root: '76c5ad26-21db-1be5-85ee-54258f68dd4a',
                    lft: '10',
                    rgt: '11',
                    level: '1',
                    children: {next_offset: -1, records: []}
                }
            ],
            containerStub = {
                jstree: function() {
                    this.on = function() {
                        return this;
                    };
                    return this;
                }
            };

        field.createTree(data, containerStub);

        expect(data[0].data).toEqual('&');
        expect(data[1].data).toEqual('<');
    });

    it('Tree add node.', function() {
        field.render();
        field.jsTree = {
            jstree: function() {}
        };

        var jstreeStub = sinonSandbox.stub(field.jsTree, 'jstree');

        field.addNode('test', 'last');
        expect(jstreeStub).toHaveBeenCalledWith('create');
    });

    it('Tree Search.', function() {
        field.render();
        field.jsTree = {
            jstree: function() {}
        };
        var jstreeStub = sinonSandbox.stub(field.jsTree, 'jstree');

        field.searchNode('valid string');
        expect(jstreeStub).toHaveBeenCalledWith('search');
    });

    it('Move Node should call different collection methods.', function() {
        field.render();
        var collectionMoveBeforeStub = sinonSandbox.stub(field.collection, 'moveBefore', function() {}),
            collectionMoveLastStub = sinonSandbox.stub(field.collection, 'moveLast', function() {});

        field.moveNode(1, 2, 'before');
        expect(collectionMoveBeforeStub).toHaveBeenCalled();

        field.moveNode(1, 2, 'last');
        expect(collectionMoveLastStub).toHaveBeenCalled();

        collectionMoveLastStub.reset();
        field.moveNode(1, 2);
        expect(collectionMoveLastStub).toHaveBeenCalled();
    });

});
