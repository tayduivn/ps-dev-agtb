describe('SUGAR.App.metadata', function () {
    //Temp hack until cache is defined
    var cache = SUGAR.App.cache = {
        get:function (key) {
        }
    };
    //end hack

    var getCache = sinon.stub(cache, "get");
    getCache.withArgs("metadata.Contacts").returns(fixtures.metadata.Contacts)


    beforeEach(function () {
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