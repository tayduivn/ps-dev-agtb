/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Plugins.NestedCollection', function() {
    var app;
    var link = 'attachments';
    var context;
    var model;
    var module = 'Emails';
    var attachments = [
        {
            id: _.uniqueId(),
            name: 'quote.pdf'
        },
        {
            id: _.uniqueId(),
            name: 'logo.png'
        },
        {
            id: _.uniqueId(),
            name: 'NDA.doc'
        }
    ];
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('NestedCollection');
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');
        model.set('id', _.uniqueId());
        // Add a collection field driven by the VirtualCollection plugin to
        // prove that the two plugins will play well together.
        model.set('to', []);
        model.set('cc', []);
        model.set('bcc', []);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('creating a nested collection', function() {
        it('should create an empty link', function() {
            var collection;

            model.set(link, []);
            collection = model.get(link);

            expect(collection.length).toBe(0);
            expect(collection.link.bean).toBe(model);
            expect(collection.link.name).toBe(link);
            expect(collection.next_offset).toBe(0);
            expect(model.getDefault(link)).toBe(collection);
        });

        it('should create a link from an array', function() {
            var collection;

            model.set(link, attachments);
            collection = model.get(link);

            expect(collection.length).toBe(3);
            expect(collection.link.bean).toBe(model);
            expect(collection.link.name).toBe(link);
            expect(collection.next_offset).toBe(0);
            expect(model.getDefault(link)).toBe(collection);
        });

        it('should create a link from a collection', function() {
            var coll = app.data.createBeanCollection('Notes', attachments);
            var collection;

            model.set(link, coll);
            collection = model.get(link);

            expect(collection.length).toBe(3);
            expect(collection.link.bean).toBe(model);
            expect(collection.link.name).toBe(link);
            expect(collection.next_offset).toBe(0);
            expect(model.getDefault(link)).toBe(collection);
        });

        it('should create a link from an API response', function() {
            var response = {
                records: attachments,
                next_offset: -1
            };
            var collection;

            model.set(link, response);
            collection = model.get(link);

            expect(collection.length).toBe(3);
            expect(collection.link.bean).toBe(model);
            expect(collection.link.name).toBe(link);
            expect(collection.next_offset).toBe(-1);
            expect(model.getDefault(link)).toBe(collection);
        });

        it('should create a link from an object', function() {
            model.set(link, attachments[0]);
            expect(model.get(link).length).toBe(1);
        });

        it('should create a link from bean', function() {
            var bean = app.data.createBean('Notes', attachments[0]);
            var collection;

            model.set(link, bean);
            collection = model.get(link);

            expect(collection.length).toBe(1);
            expect(collection.link.bean).toBe(model);
            expect(collection.link.name).toBe(link);
            expect(collection.next_offset).toBe(0);
            expect(model.getDefault(link)).toBe(collection);
        });

        it('should set non-collection attributes as usual', function() {
            var attributes = {name: 'foo'};

            attributes[link] = attachments;
            model.set(attributes);

            expect(model.get('name')).toBe('foo');
            expect(model.get(link).length).toBe(3);
        });

        it('should trigger a change on the bean', function() {
            var spy = sandbox.spy();

            model.on('change', spy);
            model.on('change:' + link, spy);
            model.set(link, []);
            model.get(link).add(attachments);

            expect(spy.callCount).toBe(4);
        });

        it('should not trigger a change on the bean', function() {
            var spy = sandbox.spy();

            model.on('change', spy);
            model.on('change:' + link, spy);
            model.set(link, [], {silent: true});
            model.get(link).add(attachments, {silent: true});

            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('getting the JSON for a bean', function() {
        var collection;

        beforeEach(function() {
            model.set('name', 'foo');
            model.set(link, attachments);
            collection = model.get(link);
        });

        it('should include data for creating, linking, and unlinking models', function() {
            var models = [
                {
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                }
            ];
            var remove = collection.at(0);
            var json;

            collection.add(models);
            collection.remove(remove);
            json = model.toJSON();

            expect(json.name).toBe('foo');
            expect(json[link].create).toEqual([models[0]]);
            expect(json[link].add).toEqual([models[1]]);
            expect(json[link].delete).toEqual([remove.get('id')]);
        });

        it('should not include data for unlinking models', function() {
            var models = [
                {
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                }
            ];
            var json;

            collection.add(models);
            json = model.toJSON();

            expect(json.name).toBe('foo');
            expect(json[link].create).toEqual([models[0]]);
            expect(json[link].add).toEqual([models[1]]);
            expect(json[link].delete).toBeUndefined();
        });
    });

    describe('copying a bean', function() {
        var target;

        beforeEach(function() {
            target = app.data.createBean(module);
            // In production, the `to` field will be driven by the
            // VirtualCollection plugin. So set it up that way.
            target.set('to', []);
            target.set('cc', []);
            target.set('bcc', []);
        });

        it('should copy all data to the target bean', function() {
            var sourceCollection;
            var targetCollection;
            var models = [
                {
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                }
            ];

            model.set('name', 'foo');
            model.set(link, attachments);
            sourceCollection = model.get(link);
            sourceCollection.add(models);
            sourceCollection.remove(sourceCollection.at(0));

            target.copy(model);
            targetCollection = target.get(link);

            expect(targetCollection).not.toBe(sourceCollection);
            expect(targetCollection.length).toBe(sourceCollection.length);
            expect(targetCollection.getSynced().length).toBe(0);
            expect(target.toJSON()).toEqual({
                name: 'foo',
                to: [],
                cc: [],
                bcc: [],
                attachments: {
                    create: [models[0]],
                    add: [
                        attachments[1],
                        attachments[2],
                        models[1]
                    ]
                }
            });
        });
    });

    describe('has the bean changed?', function() {
        var collection;

        beforeEach(function() {
            model.set(link, attachments);
            collection = model.get(link);
        });

        using('attribute names', [undefined, link], function(attr) {
            it('should return true when the collection has changed but no other attributes have', function() {
                var models = [
                    {
                        name: 'bar'
                    },
                    {
                        id: _.uniqueId(),
                        name: 'biz'
                    }
                ];

                collection.add(models);
                collection.remove(collection.at(0));

                expect(model.hasChanged(attr)).toBe(true);
            });
        });

        using('attribute names', [undefined, 'name'], function(attr) {
            it('should return true when the collection has not changed but another attribute has', function() {
                model.set('name', 'foo');
                expect(model.hasChanged(attr)).toBe(true);
            });
        });

        using('attribute names', [undefined, link, 'name'], function(attr) {
            it('should return false', function() {
                model.setDefault('name', 'foo');
                model.set('name', 'foo');

                expect(model.hasChanged(attr)).toBe(false);
            });
        });
    });

    describe('reverting a bean', function() {
        var collection;

        beforeEach(function() {
            // Pretend that the model was created with name=foo.
            model.setSyncedAttributes({name: 'foo'});
            model.set('name', 'foo');
            model.changed = {};

            model.set(link, attachments);
            collection = model.get(link);
        });

        it('should include any link fields in the return value for synchronized attributes', function() {
            var synchronized = model.getSynced();

            expect(synchronized.name).toBe('foo');
            expect(synchronized[link]).toBe(collection);
        });

        it('should revert changes to any link fields', function() {
            var models = [
                {
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                }
            ];

            model.set('name', 'bar');
            collection.add(models);
            collection.remove(collection.at(0));

            expect(model.get('name')).toBe('bar');
            expect(collection.length).toBe(4);
            expect(model.hasChanged()).toBe(true);

            model.revertAttributes();

            expect(model.get('name')).toBe('foo');
            expect(collection.length).toBe(3);
            // Data.Bean#revertAttributes does not reset the Data.Bean#changed
            // hash, so Data.Bean#hasChanged still returns `true` after
            // reverting.
            expect(model.hasChanged()).toBe(true);
        });
    });

    describe('getting changed attributes', function() {
        var collection;

        beforeEach(function() {
            model.set('name', 'foo');
            model.set(link, attachments);
            collection = model.get(link);
        });

        it('should not include `' + link + '` in the return value', function() {
            var changed = model.changedAttributes(model.getSynced());

            expect(changed[link]).toBeUndefined();
        });

        it('should include `' + link + '` in the return value', function() {
            var changed;
            var models = [
                {
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                }
            ];

            collection.add(models);
            collection.remove(collection.at(0));
            changed = model.changedAttributes(model.getSynced());

            expect(changed[link]).toBe(collection);
        });
    });

    describe('getting the names of the collection fields', function() {
        it('should return an empty array', function() {
            delete model.fields[link];

            expect(model.getNestedCollectionFieldNames('link').length).toBe(0);
        });

        it('should return an array without the link field names that are used by collection fields', function() {
            expect(model.getNestedCollectionFieldNames('link')).toEqual([link]);
        });

        it('should return an array with the collection field names', function() {
            expect(model.getNestedCollectionFieldNames('collection')).toEqual(['to', 'cc', 'bcc']);
        });
    });
});
