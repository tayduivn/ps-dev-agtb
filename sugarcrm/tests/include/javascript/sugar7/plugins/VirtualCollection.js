/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Plugins.VirtualCollection', function() {
    var app, attribute, collection, contacts, context, model, module, sandbox;

    module = 'Meetings';

    contacts = [
        {_module: 'Contacts', id: '1', name: 'Sam Stewart'},
        {_module: 'Contacts', id: '2', name: 'Ralph Davis'},
        {_module: 'Contacts', id: '3', name: 'Joe Reynolds'},
        {_module: 'Contacts', id: '4', name: 'Katie Ross'},
        {_module: 'Contacts', id: '5', name: 'Brad Harris'},
        {_module: 'Contacts', id: '6', name: 'Thomas Wallace'}
    ];

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');
        model.fields = {
            invitees: {
                name: 'invitees',
                source: 'non-db',
                type: 'collection',
                vname: 'LBL_INVITEES',
                links: ['contacts', 'accounts'],
                order_by: 'name:asc',
                fields: [
                    {
                        name: 'name',
                        type: 'name',
                        label: 'LBL_SUBJECT'
                    },
                    'accept_status_meetings',
                    'picture'
                ]
            },
            related_cases: {
                name: 'related_cases',
                source: 'non-db',
                type: 'collection',
                links: ['cases']
            },
            contacts: {
                name: 'contacts',
                type: 'link',
                source: 'non-db'
            },
            accounts: {
                name: 'accounts',
                type: 'link',
                source: 'non-db'
            },
            cases: {
                name: 'cases',
                type: 'link',
                source: 'non-db'
            }
        };
        attribute = model.fields.invitees.name;

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Meetings', 'contacts').returns('Contacts');
        app.data.getRelatedModule.withArgs('Meetings', 'accounts').returns('Accounts');
        app.data.getRelatedModule.withArgs('Meetings', 'cases').returns('Cases');
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('creating a collection attribute', function() {
        it('should initialize the collection model', function() {
            model.set(attribute, contacts);
            collection = model.get(attribute);

            expect(collection.parent).toBe(model);
            expect(_.size(collection.links)).toBe(_.size(model.fields[attribute].links));
        });

        it('should identify the initial models as having come from the server', function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            collection.add(_.rest(contacts, 2));

            expect(collection.length).toBe(6);
            expect(collection.links.contacts.length).toBe(4);
            expect(collection.links.contacts.defaults.length).toBe(2);
        });
    });

    describe('when the parent model is synchronized', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);

            collection.add(_.rest(contacts, 4));
            collection.remove([2]);
        });

        it('should not report any new changes', function() {
            expect(collection.hasChanged()).toBe(true);

            model.trigger('sync');

            expect(collection.hasChanged()).toBe(false);
        });

        it('should reset the links to empty', function() {
            model.trigger('sync');

            _.each(collection.links, function(link) {
                expect(link.length).toBe(0);
            });
        });

        it('should add synchronized models to their respective defaults list', function() {
            model.trigger('sync');

            expect(collection.links.contacts.defaults.length).toBe(5);
        });
    });

    describe('triggering change events', function() {
        var fieldSpy, modelSpy;

        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);

            fieldSpy = sandbox.spy();
            model.on('change:' + attribute, function() {
                fieldSpy();
            });

            modelSpy = sandbox.spy();
            model.on('change', function() {
                modelSpy();
            });
        });

        it('should trigger a change on the parent model', function() {
            collection.add(_.rest(contacts, 4));

            expect(fieldSpy).toHaveBeenCalled();
            expect(modelSpy).toHaveBeenCalled();
        });

        it('should not trigger a change on the parent model', function() {
            collection.add(_.rest(contacts, 4), {silent: true});

            expect(fieldSpy).not.toHaveBeenCalled();
            expect(modelSpy).not.toHaveBeenCalled();
        });
    });

    describe('adding models to the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should not do anything if there are no models to add', function() {
            collection.add([]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should add a model that does not already exist in the collection', function() {
            collection.add(_.last(contacts));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('create');
        });

        it('should add a model that was marked for removal', function() {
            collection.remove([2]);

            expect(collection.length).toBe(1);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('delete');

            collection.add(contacts[1]);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });

        it('should not add a model that already exists in the collection', function() {
            collection.add(contacts[1]);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });

        it('should merge a model that already exists in the collection', function() {
            var contact = {_module: 'Contacts', id: '1', name: 'Sammy Stewart'};

            collection.add([contact], {merge: true});

            expect(collection.get(contact.id).get('name')).toEqual(contact.name);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('update');
        });

        it('should not merge a model that already exists in the collection', function() {
            var contact = {_module: 'Contacts', id: '1', name: 'Sammy Stewart'};

            collection.add([contact]);

            expect(collection.get(contact.id).get('name')).toEqual('Sam Stewart');
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('removing models from the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should not do anything if there are no models to remove', function() {
            collection.remove([]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should not remove a model that does not exist in the collection', function() {
            collection.remove([5]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should remove a model that exists in the collection', function() {
            collection.remove([1]);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(1);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('delete');
        });

        it('should add and then remove a model', function() {
            var contact = _.last(contacts);

            collection.add(contact);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.links.contacts.length).toBe(1);

            collection.remove(contact);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('resetting the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should remove all of the models', function() {
            collection.reset([]);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(0);
            expect(collection.links.contacts.length).toBe(4);

            collection.links.contacts.each(function(contact) {
                expect(contact.get('_action')).toEqual('delete');
            });
        });

        it('should replace all of the models', function() {
            collection.reset(_.rest(contacts, 4));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(2);
            expect(collection.pluck('id').sort().join(',')).toEqual('5,6');
            expect(collection.links.contacts.length).toBe(6);
            expect(collection.links.contacts.get('1').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('2').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('3').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('4').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('5').get('_action')).toEqual('create');
            expect(collection.links.contacts.get('6').get('_action')).toEqual('create');
        });

        it('should replace some of the models', function() {
            collection.reset(_.rest(contacts, 3));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.pluck('id').sort().join(',')).toEqual('4,5,6');
            expect(collection.links.contacts.length).toBe(5);
            expect(collection.links.contacts.get('1').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('2').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('3').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('5').get('_action')).toEqual('create');
            expect(collection.links.contacts.get('6').get('_action')).toEqual('create');
        });

        it('should revert the collection to its original state', function() {
            sandbox.spy(collection, 'trigger');

            collection.add(_.rest(contacts, 4));
            collection.remove([2]);

            expect(collection.length).toBe(5);
            expect(collection.links.contacts.length).toBe(3);

            collection.revert();

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.trigger.lastCall.args[0]).toEqual('reset');
            expect(collection.length).toBe(4);
            expect(collection.pluck('id').sort().join(',')).toEqual('1,2,3,4');
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('detecting changes to the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
        });

        it('should return true', function() {
            collection.add(_.rest(contacts, 4));

            expect(collection.hasChanged()).toBe(true);
        });

        it('should return false', function() {
            expect(collection.hasChanged()).toBe(false);
        });
    });

    describe('Bean overrides for supporting collection fields', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        describe('calling toJSON() on the model', function() {
            it('should return an object with links when only the link fields are specified', function() {
                collection.add(_.rest(contacts, 4));
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                expect(model.toJSON({
                    fields: ['contacts', 'accounts']
                })).toEqual({
                    contacts: {
                        add: ['5','6'],
                        delete: ['3']
                    },
                    accounts: {
                        add: ['10']
                    }
                });
            });

            it('should not return links that have not been specified', function() {
                collection.add(_.rest(contacts, 4));
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                expect(model.toJSON({
                    fields: ['accounts']
                })).toEqual({
                    accounts: {
                        add: ['10']
                    }
                });
            });

            it('should only return the collection if only the collection name is specified', function() {
                var result;

                collection.add(_.rest(contacts, 4));
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                result = model.toJSON({
                    fields: ['invitees']
                });

                expect(_.size(result)).toBe(1);
                expect(_.size(result.invitees)).toBe(6);
            });

            it('should return all collections by default', function() {
                var result;

                model.set('related_cases', [{
                    _module: 'Cases',
                    id: '11',
                    name: 'foo'
                }, {
                    _module: 'Cases',
                    id: '12',
                    name: 'bar'
                }]);

                result = model.toJSON();

                expect(_.size(result)).toBe(2);
                expect(_.size(result.invitees)).toBe(4);
                expect(_.size(result.related_cases)).toBe(2);
            });

            it('should return no collections and links if specifically not included in options.fields', function() {
                var result;

                model.set('id', '123');
                model.set('related_cases', [{
                    _module: 'Cases',
                    id: '11',
                    name: 'foo'
                }, {
                    _module: 'Cases',
                    id: '12',
                    name: 'bar'
                }]);

                result = model.toJSON({fields:['id']});

                expect(result).toEqual({id: '123'});
            });
        });

        describe('copying a model with a collection field', function() {
            var fields, target;

            beforeEach(function() {
                fields = app.utils.deepCopy(model.fields);
                sandbox.stub(app.metadata, 'getModule').withArgs('Meetings').returns({fields: fields});

                target = app.data.createBean('Meetings');
                target.fields = fields;
            });

            it('should still copy any non-collection fields to the new bean', function() {
                model.set('foo', 'bar');

                expect(target.get(attribute)).toBeUndefined();

                target.fields.foo = {name: 'foo', type: 'varchar'};
                target.copy(model);

                expect(target.get('foo')).toEqual('bar');
            });

            it('should copy any collection fields to the new bean', function() {
                expect(target.get(attribute)).toBeUndefined();

                target.copy(model);
                collection = target.get(attribute);

                expect(collection.length).toBe(4);

                _.each(collection.links, function(link) {
                    expect(link.length).toBe(0);
                });
            });

            it('should copy the exact state of the collection field to the new bean', function() {
                model.get(attribute).remove([2]);
                target.copy(model);

                expect(target.get(attribute).length).toBe(3);
            });
        });

        describe('setting a collection field', function() {
            it('should set the collection as a default when created', function() {
                expect(model.getDefaultAttribute(attribute)).toBe(collection);
            });

            it('should still set any non-collection fields on the bean', function() {
                var attributes = {};

                attributes[attribute] = _.last(contacts);
                attributes.foo = 'bar';
                model.set(attributes);

                expect(model.get(attribute).length).toBe(1);
                expect(model.get('foo')).toEqual('bar');
            });
        });

        describe('has the bean changed?', function() {
            using('attribute names', [undefined, attribute], function(attr) {
                it('should return true when the collection has changed but no other attributes have', function() {
                    model.get(attribute).remove([2]);

                    expect(model.hasChanged(attr)).toBe(true);
                });
            });

            using('attribute names', [undefined, 'foo'], function(attr) {
                it('should return true when the collection has not changed but another attribute has', function() {
                    model.set('foo', 'bar');

                    expect(model.hasChanged(attr)).toBe(true);
                });
            });

            using('attribute names', [undefined, attribute, 'foo'], function(attr) {
                it('should return false', function() {
                    model.set('foo', 'bar');
                    delete model.changed.foo;

                    expect(model.hasChanged(attr)).toBe(false);
                });
            });
        });

        it('should include any collection fields in the return value for synchronized attributes', function() {
            expect(model.getSyncedAttributes()[attribute]).not.toBeUndefined();
        });

        it('should revert changes to any collection fields', function() {
            collection.remove([2, 3]);

            expect(collection.length).toBe(2);

            model.revertAttributes();

            expect(collection.length).toBe(4);
        });

        describe('getting changed attributes', function() {
            it('should not include `invitees` in the return value', function() {
                var changed = model.changedAttributes(model.getSyncedAttributes());

                expect(changed[attribute]).toBeUndefined();
            });

            it('should include `invitees` in the return value', function() {
                var changed;

                model.get(attribute).remove([2]);
                changed = model.changedAttributes(model.getSyncedAttributes());

                expect(changed[attribute]).not.toBeUndefined();
            });
        });

        describe('getting the names of the collection fields', function() {
            it('should return an empty array', function() {
                delete model.fields[attribute];
                delete model.fields['related_cases'];

                expect(model.getCollectionFieldNames().length).toBe(0);
            });

            it('should return an array with the collection field names', function() {
                var fields = model.getCollectionFieldNames();

                expect(fields.length).toBe(2);
                expect(fields).toEqual(['invitees','related_cases']);
            });
        });
    });
});
