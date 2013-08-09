describe("Emails.Field.QuickCreate", function() {
    var app, field, createField;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'quickcreate', 'Emails');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    createField = function() {
        return SugarTest.createField("base","quickcreate", "quickcreate", "quickcreate", null, 'Emails', null, null, true);
    };

    it('should have href set to mailto on the def when user preference is external client before init', function() {
        app.user.setPreference('use_sugar_email_client', 'false');
        field = createField();
        expect(field.def.href).toEqual('mailto:');
    });

    it('should have no href on the def when user preference is internal client before init', function() {
        app.user.setPreference('use_sugar_email_client', 'true');
        field = createField();
        expect(field.def.href).toEqual(null);
    });
});
