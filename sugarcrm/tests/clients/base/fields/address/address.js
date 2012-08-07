describe("Address", function() {
    var app, field, controller, Address;

    beforeEach(function() {
        app = SugarTest.app;
        controller = SugarFieldTest.loadSugarField('address/address');
        field = SugarFieldTest.createField("address", "detail");
        field = _.extend(field, controller);
        Address = Backbone.Model.extend({
        });
        field.model = new Address({ 
            address: '1 Foo Way',
            address_city: 'Castro Valley',
            address_state: 'CA',
            address_postalcode: '94546',
            address_country: 'USA'
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        delete Handlebars.templates;
        field.model = null;
        field = null;
        controller = null;
        Address = null;
    });

    it('should format', function() {
        var obj = {};
        obj = field.format(obj);
        expect(obj.street).toEqual('1 Foo Way');
        expect(obj.city).toEqual('Castro Valley');
        expect(obj.state).toEqual('CA');
        expect(obj.postalcode).toEqual('94546');
        expect(obj.country).toEqual('USA');
    });
    it('should unformat', function() {
        expect(field.unformat("foo")).toEqual('foo');
    });
});

