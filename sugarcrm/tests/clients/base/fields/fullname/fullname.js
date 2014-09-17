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
                    }]
                }
            ]
        }, 'Contacts');
        SugarTest.loadHandlebarsTemplate('list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('fullname', 'field', 'base', 'record-edit');
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
        	id: 'test-contact',
            full_name: fullName,
            first_name: nameParts.first_name,
            last_name: nameParts.last_name,
            salutation: nameParts.salutation
        });
        model.module = 'Contacts';
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
            expected: ['first_name', 'last_name']
        },{
            format: 's l',
            expected: ['salutation', 'last_name']
        },{
            format: 'l, f',
            expected: ['last_name', 'first_name']
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
        it('should update the Full Name when First Name or Last Name changes', function() {
            user.setPreference('default_locale_name_format', 's f l');
            view.render();
            field = view.getField('full_name');
            var renderStub = sinon.stub(field, 'render');

            field.fields.length = 3;
            expect(field.value).toBe('Mr. firstName lastName');

            field.model.set('first_name', 'FIRST');
            expect(field.model.get('full_name')).toBe('Mr. FIRST lastName');

            field.model.set('last_name', 'LAST');
            expect(field.model.get('full_name')).toBe('Mr. FIRST LAST');

            field.model.set('salutation', 'Dr.');
            expect(field.model.get('full_name')).toBe('Dr. FIRST LAST');

            expect(renderStub).toHaveBeenCalled();
            expect(renderStub.calledThrice).toBeTruthy();

            renderStub.restore();
        });
    });
});
