describe('Base.Field.Fullname', function() {
    var field, model, user, fullName, app, view;

    beforeEach(function() {
        app = SugarTest.App;
        SugarTest.testMetadata.init();

        SugarTest.testMetadata.addViewDefinition('list', {
            'panels': [
                {
                    'fields': [{
                        'name': 'full_name',
                        'type': 'fullname',
                        'link': true,
                        'fields': [{
                            name: 'salutation',
                            type: 'base'
                        }, 'first_name', 'last_name']
                    }]
                }
            ]
        }, 'Contacts');
        SugarTest.loadHandlebarsTemplate('list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'fullname');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'list', null, null);

        view.collection = new Backbone.Collection();
        view.viewName = 'list';

        user = SUGAR.App.user;

        var nameParts = {
            first_name: 'firstName',
            last_name: 'lastName',
            salutation: 'Mr.'
        };

        fullName = nameParts.last_name + ' ' + nameParts.salutation + ' ' + nameParts.first_name;

        model = new Backbone.Model();
        model.set({
            full_name: fullName,
            first_name: nameParts.first_name,
            last_name: nameParts.last_name,
            salutation: nameParts.salutation
        });
        view.collection.add(model);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        if (field) {
            field.dispose();
        }
        user = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        using('available formats', [{
            format: 'f s l',
            expected: ['first_name', 'salutation', 'last_name']
        },{
            format: 's f l',
            expected: ['salutation', 'first_name', 'last_name']
        },{
            format: 'f l',
            expected: ['salutation', 'first_name', 'last_name']
        },{
            format: 's l',
            expected: ['first_name', 'salutation', 'last_name']
        },{
            format: 'l, f',
            expected: ['salutation', 'last_name', 'first_name']
        },{
            format: 's l, f',
            expected: ['salutation', 'last_name', 'first_name']
        },{
            format: 'l s f',
            expected: ['last_name', 'salutation', 'first_name']
        },{
            format: 'l f s',
            expected: ['last_name', 'first_name', 'salutation']
        }], function(value) {
            it('Should sort the dependant fields in order of the user preference.', function() {
                user.setPreference('default_locale_name_format', value.format);
                view.render();
                field = view.getField('full_name');
                _.each(value.expected, function(name, index) {
                    expect(field.def.fields[index].name).toBe(name);
                });
            });
        });
    });

    describe('render', function() {
        it('Should generate children fields dynamically each rendering time', function() {
            user.setPreference('default_locale_name_format', 'l s f');

            view.render();
            field = view.getField('full_name');
            //one placeholder
            expect(_.values(view.fields).length).toBe(1);
            expect(field.fields.length).toBe(0);
            expect(field.value).toBe(fullName);

            //switches to edit mode
            view.viewName = 'edit';
            view.render();
            field = view.getField('full_name');
            //one placeholder for parent (fullname)
            //three placeholders for children (first_name, last_name, salutation)
            expect(_.values(view.fields).length).toBe(4);
            expect(field.fields.length).toBe(3);

            //switches to list mode
            view.viewName = 'list';
            view.render();
            field = view.getField('full_name');
            expect(_.values(view.fields).length).toBe(1);
            expect(field.fields.length).toBe(0);
        });
    });

    describe('_loadTemplate', function() {
        it('should build this.href if def.link true', function() {
            var expected = "#Contacts/12345";
            view.render();
            field = view.getField('full_name');
            field.model.set('id', 12345);
            field._loadTemplate();
            expect(field.href).toEqual(expected);
        });
        it('should NOT build this.href if def.link is falsy', function() {
            view.render();
            field = view.getField('full_name');
            field.def.link = undefined;
            field.href = undefined;
            field.model.set('id', 12345);
            field._loadTemplate();
            expect(field.href).toBeUndefined();
        });
    });
});
