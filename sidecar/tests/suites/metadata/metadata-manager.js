describe('SUGAR.App.metadata', function () {
    //Preload the templates
    SUGAR.App.template.load(fixtures.templates);

    beforeEach(function () {
        //Load the metadata
        SUGAR.App.metadata.set(fixtures.metadata);
    });

    afterEach(function () {
    });

    it('exists', function () {
        expect(typeof(SUGAR.App.metadata)).toBe('object');
    });

    it('gets vardefs', function () {
        expect(SUGAR.App.metadata.get({
            type:"vardef",
            module:"Contacts"
        })).toBe(fixtures.metadata.Contacts.beans.Contact.vardefs);
    });

    it('gets viewdefs', function () {
        expect(SUGAR.App.metadata.get({
            type:"view",
            module:"Contacts"
        })).toBe(fixtures.metadata.Contacts.views);
    });

    it('gets defs for a specific view', function () {
        expect(SUGAR.App.metadata.get({
            type:"view",
            module:"Contacts",
            view:"editView"
        })).toBe(fixtures.metadata.Contacts.views.editView);
    });

    it('gets layoutdefs', function () {
        expect(SUGAR.App.metadata.get({
            type:"layout",
            module:"Contacts"
        })).toBe(fixtures.metadata.Contacts.layouts);
    });

    it('gets a specific layout', function () {
        expect(SUGAR.App.metadata.get({
            type:"layout",
            module:"Contacts",
            layout:'detail'
        })).toBe(fixtures.metadata.Contacts.layouts.detail);
    });
});