describe('metadata', function () {
    //Preload the templates
    SUGAR.App.template.load(fixtures.templates);

    beforeEach(function () {
        //Load the metadata
        SUGAR.App.metadata.set(fixtures.metadata);
        SUGAR.App.metadata.set({sugarFields:sugarFieldsFixtures.fieldsData});
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

    it('gets a specific sugarfield', function () {
        expect(SUGAR.App.metadata.get({
            sugarField:{
                name:'varchar',
                view:'editView'
            }
        })).toBe(sugarFieldsFixtures.fieldsData.text.editView);
    });

    it('gets a specific sugarfield defaulted to default if the view does not exist', function () {
        expect(SUGAR.App.metadata.get({
            sugarField:{
                name:'varchar',
                view:'thisViewDoesntExist'
            }
        })).toBe(sugarFieldsFixtures.fieldsData.text.default);
    });
});