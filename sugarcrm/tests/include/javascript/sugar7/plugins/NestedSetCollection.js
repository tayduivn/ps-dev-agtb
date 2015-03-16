describe('Plugins.NestedSetCollection', function() {
    var module = 'KBContents',
        fieldDef = {
            category_root: '0',
            module_root: module
        },
        app, field, renderTreeStub, sinonSandbox, treeData;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nestedset', module);
        SugarTest.loadPlugin('NestedSetCollection');
        SugarTest.loadPlugin('JSTree');
        SugarTest.loadHandlebarsTemplate('nestedset', 'field', 'base', 'edit', module);

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

        field = SugarTest.createField('base', 'nestedset', 'nestedset', 'edit', fieldDef, module, null, null, true);
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
        delete app.plugins.plugins['field']['JSTree'];
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

    it('Nested bean should trigger app:nestedset:sync:complete when root collection is valid.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        var model = field.collection.get('1');
        sinonSandbox.stub(app.events, 'trigger');

        model.trigger('sync', model);
        expect(app.events.trigger).toHaveBeenCalledWith('app:nestedset:sync:complete');
    });

    it('Nested Collection reset should remove all models.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        field.collection.reset();
        expect(field.collection.length).toEqual(0);
    });

    it('Nested Collection remove should delete a specific model.', function() {
        var modelId = 1;
        field.collection = new app.NestedSetCollection(treeData);
        field.collection.remove([field.collection.get(modelId)]);
        expect(field.collection.getChild(modelId)).toBe(undefined);
    });

    it('Nested Collection get roots.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call', function() {});
        field.collection.roots();
        expect(apiStub.lastCall.args[1].indexOf('tree/roots')).not.toBe(-1);
    });

    it('Nested Collection get tree.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call', function() {});
        field.collection.tree();
        expect(apiStub.lastCall.args[1].indexOf('tree')).not.toBe(-1);
    });

    it('Nested Collection parse should save passed tree to a variable.', function() {
        field.collection = new app.NestedSetCollection(treeData);
        field.collection.parse(treeData);
        expect(field.collection.jsonTree).toBe(treeData);
    });

    it('Nested collection get child by ID.', function() {
        field.collection = new app.NestedSetCollection(treeData);

        var childModel = field.collection.getChild({id: '4'});
        expect(childModel instanceof app.NestedSetBean).toBeTruthy();
    });

    describe('Nested methods test', function() {
        var ID = 1;
        var targetId = '2';
        var module = 'Categories';
        var model;
        beforeEach(function() {
            field.collection = new app.NestedSetCollection(treeData);
            field.collection.module = module;
            model = field.collection.get(ID);
        });

        using('API get data provider', [
            {
                method: 'getChildren',
                specificUrl: 'children'
            },
            {
                method: 'getNext',
                specificUrl: 'next'
            },
            {
                method: 'getPrev',
                specificUrl: 'prev'
            },
            {
                method: 'getParent',
                specificUrl: 'parent'
            },
            {
                method: 'getPath',
                specificUrl: 'path'
            }
        ], function(value) {
            it('API methods should do a call with valid data', function() {
                var url = app.api.buildURL(model.module, ID + '/' + value.specificUrl);
                var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call', function() {});

                model[value.method]({
                    success: function() {
                    }
                });
                expect(apiStub.lastCall.args[1]).toEqual(url);
            });
        });

        using('API move data provider', [
            {
                method: 'moveBefore',
                specificUrl: 'movebefore'
            },
            {
                method: 'moveAfter',
                specificUrl: 'moveafter'
            },
            {
                method: 'moveFirst',
                specificUrl: 'movefirst'
            },
            {
                method: 'moveLast',
                specificUrl: 'movelast'
            }
        ], function(value) {
            it('Move methods should use a collection.', function() {
                var url = app.api.buildURL(module, ID + '/' + value.specificUrl + '/' + targetId);
                var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call', function() {});

                model[value.method]({
                    target: targetId,
                    success: function() {
                    }
                });
                expect(apiStub.lastCall.args[1]).toEqual(url);
            });
        });

        using('API append data provider', [
            {
                method: 'append',
                specificUrl: 'append'
            },
            {
                method: 'prepend',
                specificUrl: 'prepend'
            },
            {
                method: 'insertBefore',
                specificUrl: 'insertbefore'
            },
            {
                method: 'insertAfter',
                specificUrl: 'insertafter'
            }
        ], function(value) {
            it('Move methods should use a collection.', function() {
                var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call', function() {});
                field.collection[value.method]({
                    target: targetId
                });
                expect(apiStub.lastCall.args[1].indexOf(value.specificUrl)).not.toBe(-1);
            });
        });

    });

});
