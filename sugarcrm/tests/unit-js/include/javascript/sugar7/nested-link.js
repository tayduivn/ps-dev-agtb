describe('Sugar7.NestedLink', function() {
    var app;
    var bean;
    var fieldName = 'accounts';
    var sandbox;
    var spy;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;

        sandbox = sinon.sandbox.create();
        spy = sandbox.spy();

        bean = app.data.createBean('Contacts', {id: _.uniqueId()});
        bean.fields[fieldName].order_by = 'name:asc';
        bean.on('change', spy);
        bean.on('change:' + fieldName, spy);
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('creating a collection', function() {
        var models;

        beforeEach(function() {
            models = [
                {
                    id: _.uniqueId(),
                    name: 'foo'
                },
                {
                    id: _.uniqueId(),
                    name: 'bar'
                },
                {
                    id: _.uniqueId(),
                    name: 'biz'
                },
                {
                    name: 'baz'
                }
            ];
        });

        it('should recognize the initial models as synchronized unless a model is new', function() {
            var options = {
                next_offset: -1,
                link: {
                    name: fieldName,
                    bean: bean
                }
            };
            var collection = new app.NestedLink(models, options);
            var data = collection.getData();
            var synchronized = collection.getSynced();

            expect(collection.length).toBe(4);
            expect(collection.offset).toBe(3);
            expect(collection.next_offset).toBe(-1);
            expect(collection.page).toBe(1);
            expect(collection.module).toBe('Accounts');
            expect(data.create.length).toBe(1);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(3);
            expect(bean.getRelatedCollection(fieldName)).toBe(collection);
            expect(collection.getOption('relate')).toBe(true);
        });

        it('should use options.next_offset for the value of next_offset', function() {
            var options = {
                // One of the models is new, so it has not been synchronized
                // and cannot be included in the value of the `next_offset`.
                next_offset: 3,
                link: {
                    name: fieldName,
                    bean: bean
                }
            };
            var collection = new app.NestedLink(models, options);

            expect(collection.length).toBe(4);
            expect(collection.offset).toBe(3);
            expect(collection.next_offset).toBe(3);
            expect(collection.page).toBe(1);
        });

        it('should set next_offset to 0 when options.next_offset is not given', function() {
            var options = {
                link: {
                    name: fieldName,
                    bean: bean
                }
            };
            var collection = new app.NestedLink(models, options);

            expect(collection.length).toBe(4);
            expect(collection.offset).toBe(3);
            expect(collection.next_offset).toBe(0);
            expect(collection.page).toBe(1);
        });
    });

    describe('adding to a collection', function() {
        var collection;
        var models;

        beforeEach(function() {
            var options = {
                link: {
                    name: fieldName,
                    bean: bean
                },
                silent: true
            };
            var model = app.data.createBean('Accounts', {
                id: _.uniqueId(),
                name: 'biz'
            });
            models = [
                // New records to create.
                {name: 'foo'},
                {name: 'bar'},
                // An existing record to link.
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'baz'
                })
            ];

            // Create the collection with just a single model.
            collection = new app.NestedLink(model, options);
        });

        it('should add the models', function() {
            var data;
            var synchronized;

            collection.add(models);
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(4);
            expect(data.create.length).toBe(2);
            expect(data.add.length).toBe(1);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(1);
            expect(spy.callCount).toBe(2);
        });

        it('should update the models', function() {
            var data;
            var synchronized;

            // Seed the collection with models.
            collection.add(models);

            // Copy the models and change their names.
            models = collection.map(function(model) {
                return _.extend(
                    model.isNew() ? {cid: model.cid} : {},
                    model.attributes,
                    {name: model.get('name') + '2'}
                );
            });

            // Merge the changes onto the current models.
            collection.add(models, {merge: true});
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(4);
            expect(data.create.length).toBe(2);
            expect(data.create[0].name).toBe('foo2');
            expect(data.create[1].name).toBe('bar2');
            expect(data.add.length).toBe(2);
            expect(data.add[0].name).toBe('baz2');
            expect(data.add[1].name).toBe('biz2');
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(1);
            expect(spy.callCount).toBe(2);
        });

        it('should not update the models', function() {
            var data;
            var synchronized;

            // Seed the collection with models.
            collection.add(models);

            // Copy the models and change their names.
            models = collection.map(function(model) {
                return _.extend(
                    model.isNew() ? {cid: model.cid} : {},
                    model.attributes,
                    {name: model.get('name') + '2'}
                );
            });

            // Add the models again. Their changes should not be merged.
            collection.add(models);
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(4);
            expect(data.create.length).toBe(2);
            expect(data.create[0].name).toBe('foo');
            expect(data.create[1].name).toBe('bar');
            expect(data.add.length).toBe(1);
            expect(data.add[0].name).toBe('baz');
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(1);
            expect(spy.callCount).toBe(2);
        });

        it('should not trigger a change on the model', function() {
            collection.add(models, {silent: true});

            expect(collection.length).toBe(4);
            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('removing from a collection', function() {
        var collection;

        beforeEach(function() {
            var options = {
                link: {
                    name: fieldName,
                    bean: bean
                },
                silent: true
            };
            var model = app.data.createBean('Accounts', {
                id: _.uniqueId(),
                name: 'biz'
            });
            var models = [
                // New records to create.
                {name: 'foo'},
                {name: 'bar'},
                // An existing record to link.
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'baz'
                })
            ];

            // Create the collection with just a single model.
            collection = new app.NestedLink(model, options);

            // Add the rest of the models.
            collection.add(models, {silent: true});
        });

        it('should remove the models', function() {
            var data;
            var synchronized;
            var cids = collection.map(function(model) {
                return model.cid;
            });

            collection.remove(cids);
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(0);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(1);
            expect(synchronized.length).toBe(1);
            expect(spy.callCount).toBe(2);
        });

        it('should not trigger a change on the model', function() {
            collection.remove(collection.at(1), {silent: true});

            expect(collection.length).toBe(3);
            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('detecting changes in a collection', function() {
        var collection;
        var data;

        beforeEach(function() {
            var options = {
                link: {
                    name: fieldName,
                    bean: bean
                },
                silent: true
            };
            var model = app.data.createBean('Accounts', {
                id: _.uniqueId(),
                name: 'biz'
            });

            // Create the collection with just a single model.
            collection = new app.NestedLink(model, options);
        });

        data = [
            // A new record to create.
            {name: 'foo'},
            // An existing record to link.
            {
                id: _.uniqueId(),
                name: 'baz'
            }
        ];
        using('add', data, function(model) {
            it('should return true', function() {
                collection.add(model);
                expect(collection.hasChanged()).toBe(true);
            });
        });

        it('should return true when merging a synchronized model', function() {
            // Change the name of the synchronized model.
            collection.add(_.extend(collection.at(0).toJSON(), {name: 'buzz'}), {merge: true});
            expect(collection.hasChanged()).toBe(true);
        });

        it('should return true when removing a synchronized model', function() {
            collection.remove(collection.at(0));
            expect(collection.hasChanged()).toBe(true);
        });

        it('should return false', function() {
            collection.add({name: 'bar'});
            // Remove the unsynchronized model.
            collection.remove(collection.at(1));
            expect(collection.hasChanged()).toBe(false);
        });
    });

    describe('replacing the models in a collection', function() {
        var collection;

        beforeEach(function() {
            var options = {
                next_offset: -1,
                link: {
                    name: fieldName,
                    bean: bean
                },
                silent: true
            };
            var models;

            // Create the collection with the initial set of models.
            models = [
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'biz'
                }),
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'qux'
                }),
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'abc'
                })
            ];
            collection = new app.NestedLink(models, options);

            // Add the rest of the models.
            models = [
                // New records to create.
                {name: 'foo'},
                {name: 'bar'},
                // An existing record to link.
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'baz'
                })
            ];
            collection.add(models);

            // Change one of the synchronized models.
            collection.add(collection.at(0).set('name', 'xyz'), {merge: true});

            // Remove one of the synchronized models.
            collection.remove(collection.at(2));

            // Remove an unsynchronized model.
            collection.remove(collection.at(3));
        });

        it('should empty the collection', function() {
            var data;
            var synchronized;

            collection.reset();
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(0);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(0);
        });

        it('should replace the models in the collection', function() {
            var data;
            var synchronized;
            var models = [
                // New records to create.
                {name: 'fizz'},
                {name: 'buzz'},
                // An existing record to link.
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'bat'
                }),
                // A synchronized model that has changed.
                collection.at(0),
                // A synchronized model that has not changed.
                collection.at(1)
            ];

            collection.reset(models);
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(5);
            expect(data.create.length).toBe(2);
            expect(data.add.length).toBe(2);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(2);
        });

        it('should revert the collection', function() {
            var data = collection.getData();
            var synchronized = collection.getSynced();

            expect(collection.length).toBe(4);
            expect(data.create.length).toBe(1);
            expect(data.add.length).toBe(2);
            expect(data.delete.length).toBe(1);
            expect(synchronized.length).toBe(3);

            collection.revert();
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(3);
            expect(collection.offset).toBe(4);
            expect(collection.next_offset).toBe(4);
            expect(collection.page).toBe(1);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(1);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(3);
        });

        it('should have 0 for the offset when reverting leaves the collection empty', function() {
            var options = {
                link: {
                    name: fieldName,
                    bean: bean
                }
            };
            var data;
            var synchronized;

            collection = new app.NestedLink([], options);
            collection.add([{name: 'fizz'}, {name: 'buzz'}]);

            collection.revert();
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(0);
            expect(collection.offset).toBe(0);
            expect(collection.next_offset).toBe(0);
            expect(collection.page).toBe(1);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(0);
        });
    });

    describe('fetching the models in a collection', function() {
        it('should replace the models when performing a standard fetch', function() {
            var success = sandbox.spy();
            var models;
            var collection;
            var data;
            var synchronized;

            // Create the collection with the initial set of models.
            models = [
                {
                    id: _.uniqueId(),
                    name: 'biz'
                },
                {
                    id: _.uniqueId(),
                    name: 'qux'
                },
                {
                    id: _.uniqueId(),
                    name: 'abc'
                }
            ];
            collection = new app.NestedLink(models, {
                next_offset: -1,
                link: {
                    name: fieldName,
                    bean: bean
                },
                silent: true
            });

            // Add the rest of the models.
            collection.add([
                // New records to create.
                {name: 'foo'},
                {name: 'bar'},
                // An existing record to link.
                app.data.createBean('Accounts', {
                    id: _.uniqueId(),
                    name: 'baz'
                })
            ]);

            // Change one of the synchronized models.
            collection.add(collection.at(0).set('name', 'xyz'), {merge: true});

            // Remove one of the synchronized models.
            collection.remove(collection.at(2));

            // Remove an unsynchronized model.
            collection.remove(collection.at(3));

            sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
                var response = {
                    records: models,
                    next_offset: -1
                };

                callbacks.success(response);
            });

            collection.fetch({success: success});
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(3);
            expect(collection.offset).toBe(3);
            expect(collection.next_offset).toBe(-1);
            expect(collection.page).toBe(1);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(3);
            expect(success).toHaveBeenCalled();
        });

        it('should paginate the collection', function() {
            var success = sandbox.spy();
            var data;
            var synchronized;
            var collection = new app.NestedLink({
                id: _.uniqueId(),
                name: 'biz'
            }, {
                next_offset: 1,
                link: {
                    name: fieldName,
                    bean: bean
                }
            });

            sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
                var response = {
                    records: [{
                        id: _.uniqueId(),
                        name: 'qux'
                    }],
                    next_offset: 2
                };

                callbacks.success(response);
            });

            collection.paginate({
                limit: 1,
                success: success
            });
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(2);
            expect(collection.offset).toBe(2);
            expect(collection.next_offset).toBe(2);
            expect(collection.page).toBe(2);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(2);
            expect(success).toHaveBeenCalled();
        });

        it('should fetch all models', function() {
            var success = sandbox.spy();
            var models;
            var collection;
            var data;
            var synchronized;
            var offset = 0;

            // Create the collection with the initial set of models.
            models = [
                {
                    id: _.uniqueId(),
                    name: 'biz'
                },
                {
                    id: _.uniqueId(),
                    name: 'qux'
                },
                {
                    id: _.uniqueId(),
                    name: 'abc'
                }
            ];
            collection = new app.NestedLink([], {
                next_offset: offset,
                link: {
                    name: fieldName,
                    bean: bean
                }
            });

            sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
                var response = {
                    records: [models.shift()],
                    next_offset: models.length > 0 ? ++offset : -1
                };

                callbacks.success(response);
            });

            collection.fetch({
                all: true,
                limit: 1,
                success: success
            });
            data = collection.getData();
            synchronized = collection.getSynced();

            expect(collection.length).toBe(3);
            expect(collection.offset).toBe(3);
            expect(collection.next_offset).toBe(-1);
            expect(collection.page).toBe(1);
            expect(data.create.length).toBe(0);
            expect(data.add.length).toBe(0);
            expect(data.delete.length).toBe(0);
            expect(synchronized.length).toBe(3);
            expect(success.callCount).toBe(1);
        });
    });

    describe('synchronizing the bean', function() {
        using(
            'setup, change, sync',
            [
                // All records are known to the client, so `next_offset` and
                // `offset` can be confidently set.
                [-1, 0, 2, 0, {length: 4, next_offset: -1, offset: 4}],
                [-1, 0, 2, 1, {length: 3, next_offset: -1, offset: 3}],
                // Two new and two existing records are linked. The order of
                // those rows is unknown and the id's of the new records are
                // unknown. So `offset` should remain as is. The existing
                // records will be retrieved as duplicates during one of the
                // subsequent paginations, but that will not affect `offset` or
                // `next_offset`. The offset values will self-correct after one
                // or more paginations.
                [-1, 2, 2, 0, {length: 4, next_offset: 2, offset: 2}],
                // The only difference is that one of the initial records was
                // unlinked. The client should assume that unlinked records
                // never existed, so the offset values are reduced by the
                // number of unlinked records.
                [-1, 2, 2, 1, {length: 3, next_offset: 1, offset: 1}],
                // Two existing records are linked, but the order of those rows
                // with respect to the remaining unfetched records is unknown.
                // So the offset values should remain as they are and they will
                // self-correct after one or more paginations.
                [2, 0, 2, 0, {length: 4, next_offset: 2, offset: 2}],
                // The only difference is that two new records are linked in
                // addition to the two existing records. But the circumstances
                // surrounding row order remains the same. Therefore, the same
                // rules apply.
                [2, 2, 2, 0, {length: 4, next_offset: 2, offset: 2}],
                // To existing records are linked, but the order of those rows
                // with respect to the remaining unfetched records is unknown.
                // So the offset values should not increase. And as the client
                // should assume that unlinked records never existed, the
                // offset values are reduced by the number of unlinked records.
                [2, 0, 2, 1, {length: 3, next_offset: 1, offset: 1}],
                // The only difference is that two new records are linked in
                // addition to the two existing records. Again, the same rules
                // apply.
                [2, 2, 2, 1, {length: 3, next_offset: 1, offset: 1}]
            ],
            function(nextOffset, createCount, addCount, deleteCount, expectations) {
                it('should adjust the offset information and reset the internal collections', function() {
                    var options = {
                        next_offset: nextOffset,
                        link: {
                            name: fieldName,
                            bean: bean
                        }
                    };
                    var collection = new app.NestedLink([
                        app.data.createBean('Accounts', {
                            id: _.uniqueId(),
                            name: 'foo'
                        }),
                        app.data.createBean('Accounts', {
                            id: _.uniqueId(),
                            name: 'bar'
                        })
                    ], options);
                    var data;
                    var synchronized;

                    while (createCount > 0) {
                        collection.add({name: 'biz' + createCount});
                        createCount--;
                    }

                    while (addCount > 0) {
                        collection.add(app.data.createBean('Accounts', {
                            id: _.uniqueId(),
                            name: 'baz' + addCount
                        }));
                        addCount--;
                    }

                    while (deleteCount > 0) {
                        collection.remove(collection.at(0));
                        deleteCount--;
                    }

                    sandbox.spy(collection, 'getPageNumber');

                    bean.trigger('sync');
                    data = collection.getData();
                    synchronized = collection.getSynced();

                    expect(collection.length).toBe(expectations.length);
                    expect(collection.next_offset).toBe(expectations.next_offset);
                    expect(collection.offset).toBe(expectations.offset);
                    expect(data.create.length).toBe(0);
                    expect(data.add.length).toBe(0);
                    expect(data.delete.length).toBe(0);
                    expect(synchronized.length).toBe(collection.length);
                    expect(collection.getPageNumber).toHaveBeenCalled();
                });
            }
        );
    });
});
