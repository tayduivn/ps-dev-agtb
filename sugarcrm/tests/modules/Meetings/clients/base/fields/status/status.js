describe('View.Fields.Base.Meetings.StatusField', function() {
    var app, field,
        module = 'Meetings',
        items = {
            Planned: 'Planned',
            Held: 'Held',
            'Not Held': 'Not Held'
        };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('status', 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate('status', 'field', 'base', 'list', module);
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'status', module);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('when the status field is in detail mode', function() {
        var checkClasses = function(plain, success, important) {
            expect(field.$('.label').length).toBe(plain);
            expect(field.$('.label-success').length).toBe(success);
            expect(field.$('.label-important').length).toBe(important);
        };

        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'status', 'detail', undefined, module);
            field.items = items;
        });

        using('detail modes', ['detail', 'list'], function(mode) {
            it('should be a boostrap label', function() {
                field.action = mode;
                field.model.set('status', 'foo');
                field.render();
                checkClasses(1, 0, 0);
            });
        });

        it('should be a success boostrap label when the meeting was held', function() {
            field.model.set('status', 'Held');
            field.render();
            checkClasses(1, 1, 0);
        });

        it('should be an important boostrap label when the meeting was not held', function() {
            field.model.set('status', 'Not Held');
            field.render();
            checkClasses(1, 0, 1);
        });

        it('should be a plain boostrap label when the meeting is planned', function() {
            field.model.set('status', 'Planned');
            field.render();
            checkClasses(1, 0, 0);
        });
    });

    describe('when the status field is in edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'status', 'edit', undefined, module);
            field.items = items;
        });

        it('should be an enum', function() {
            field.action = 'edit';
            field.render();
            expect(field.$('input.select2').length).toBe(1);
        });
    });
});
