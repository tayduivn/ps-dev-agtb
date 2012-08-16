describe("Address", function() {

    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","address_street", "address", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field = null;
        Address = null;
    });

    it('should format', function() {
        var obj = {};
        Address = Backbone.Model.extend({});
        field.model = new Address({ 
            address_street: '1 Foo Way',
            address_city: 'Castro Valley',
            address_state: 'CA',
            address_postalcode: '94546',
            address_country: 'USA'
        });
        obj = field.format(obj);
        expect(obj.street).toEqual('1 Foo Way');
        expect(obj.city).toEqual('Castro Valley');
        expect(obj.state).toEqual('CA');
        expect(obj.postalcode).toEqual('94546');
        expect(obj.country).toEqual('USA');
        field.model = null;
    });
    it('should unformat', function() {
        expect(field.unformat("foo")).toEqual('foo');
    });
});

