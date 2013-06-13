describe("Merge Duplicates", function () {

    var view, layout, app;

    beforeEach(function () {
        var module = 'Contacts',
            context = null;
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'merge-duplicates');
        SugarTest.loadComponent('base', 'layout', 'list');
        SugarTest.testMetadata.addViewDefinition('record', {
            panels: [
                {
                    fields: ['test']
                }
            ]
        }, module);
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', module, 'list');
        layout.context.set({
            selectedDuplicates: [new Backbone.Model(), new Backbone.Model()]
        });
        view = SugarTest.createView("base", module, "merge-duplicates", null, layout.context, null, layout);
    });

    afterEach(function () {
        layout.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    it("Tests fields for merge", function() {
        var data = [
            {
                field: {
                    type: 'datetime',
                    source: 'db',
                    name: 'customDate'
                },
                expectedResult: true
            },
            {
                field: {
                    type: 'datetime',
                    source: 'db',
                    name: 'date_modified'
                },
                expectedResult: false
            },
            {
                field: {
                    type: 'text',
                    source: 'non-db',
                    name: 'relateText'
                },
                expectedResult: false
            },
            {
                field: null,
                expectedResult: false
            },
            {
                field: {
                    type: 'enum',
                    source: 'db',
                    duplicate_merge: 'disabled'
                },
                expectedResult: false
            },
            {
                field: {
                    type: 'int',
                    source: 'db',
                    name: 'age',
                    auto_increment: true
                },
                expectedResult: false
            },
            {
                field: {
                    type: 'datetimecombo',
                    source: 'db',
                    name: 'birth'
                },
                expectedResult: true
            }
        ];
        _.each(data, function(testData) {
            expect(view.validMergeField(testData.field)).toBe(testData.expectedResult);
        });
    });

    describe ("Merge Duplicates Fields", function() {
        var view, layout, app;

        beforeEach(function () {
            var module = 'Contacts',
                context = null,
                fieldDef = {
                    test1: {
                        label: 'test1',
                        name: 'test1',
                        type: 'datetime',
                        source: 'db'
                    },
                    test2: {
                        label: 'test2',
                        name: 'test2',
                        type: 'fieldset',
                        fields: [
                            {
                                label: 'sub1Test',
                                name: 'sub1Test',
                                type: 'varchar',
                                source: 'db'
                            },
                            {
                                label: 'sub2Test',
                                name: 'sub2Test',
                                type: 'varchar',
                                source: 'db'
                            }
                        ]
                    },
                    test3: {
                        label: 'test3',
                        name: 'test3',
                        type: 'varchar',
                        source: 'db'
                    }
                },
                meta =  _.extend(fieldDef,
                    {
                        sub1Test: {
                            label: 'sub1Test',
                            name: 'sub1Test',
                            type: 'varchar',
                            source: 'db'
                        },
                        sub2Test: {
                            label: 'sub2Test',
                            name: 'sub2Test',
                            type: 'varchar',
                            source: 'db'
                        }

                    });
            app = SugarTest.app;
            SugarTest.testMetadata.init();
            SugarTest.loadComponent('base', 'view', 'merge-duplicates');
            SugarTest.loadComponent('base', 'layout', 'list');
            meta = _.extend(app.metadata.getModule(module), {fields: meta});
            SugarTest.testMetadata.updateModuleMetadata(module, meta);
            SugarTest.testMetadata.addViewDefinition('record', {
                panels: [
                    {
                        fields: [fieldDef.test1, fieldDef.test2, fieldDef.test3]
                    }
                ]
            }, module);
            SugarTest.testMetadata.set();

            layout = SugarTest.createLayout('base', module, 'list');
            layout.context.set({
                selectedDuplicates: [new Backbone.Model(), new Backbone.Model()]
            });
            view = SugarTest.createView("base", module, "merge-duplicates", null, layout.context, null, layout);
        });

        it("Should Flatten Fieldsets Properly", function() {
            var result = _.pluck(view.mergeFields, 'name');
            expect(result).toEqual(['test1', 'sub1Test', 'sub2Test', 'test3']);
        });

        afterEach(function () {
            layout.dispose();
            app.view.reset();
            SugarTest.testMetadata.dispose();
        });
    });
});
