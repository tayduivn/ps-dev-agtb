describe("base date field", function() {

    var app, baseDateField, field;

    beforeEach(function() {
        app = SugarTest.app;
        baseDateField = SugarTest.createField("base","basedatepicker", "basedatepicker", "detail");
        field = SugarTest.createField("base","basedate", "basedate", "detail");
        // To avoid calling initialize we just set these here
        field.usersDatePrefs = 'm/d/Y';
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        baseDateField = null;
        field = null;
    });

    describe("date", function() {

        it("should unformat", function() {
            var isoDateString, actual, parts, now;

            field.serverDateFormat = 'Y/m/d';
            now = new Date();
            isoDateString = now.toISOString();

            actual = field.unformat( (new Date().toISOString()) );
            parts = actual.split('/');

            expect(parseInt(parts[0], 10)).toEqual(now.getFullYear());
            expect(parseInt(parts[1], 10)).toEqual(now.getMonth()+1);
            expect(parseInt(parts[2], 10)).toEqual(now.getDate());
        });
        it("should format the value", function() {
            var jsDate, unformatedValue;
            jsDate = new Date("March 13, 2012");
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue)).toEqual('03/13/2012');
        });

        it("should format value for display_default", function() {
            var today = new Date(), 
                actual, stub, parts,
                originalType = field.view.name;

            stub = sinon.stub(field.model, 'set');
            field.view.name = 'edit';

            field.def.display_default = 'now';
            actual = field.format(null);
            parts = actual.split('/');
            expect(parseInt(parts[0], 10)).toEqual( (today.getMonth()+1) );
            expect(parseInt(parts[1], 10)).toEqual( (today.getDate()) );
            expect(parseInt(parts[2], 10)).toEqual( (today.getFullYear()) );
            expect(stub).toHaveBeenCalled();

            stub.restore();
            field.view.name = originalType;
        });

        it("should format value for required", function() {
            var today = new Date(), 
                actual, stub, parts,
                originalType = field.view.name;

            stub = sinon.stub(field.model, 'set');
            field.view.name = 'edit';

            field.def.required = true;
            actual = field.format(null);
            parts = actual.split('/');
            expect(parseInt(parts[0], 10)).toEqual( (today.getMonth()+1) );
            expect(parseInt(parts[1], 10)).toEqual( (today.getDate()) );
            expect(parseInt(parts[2], 10)).toEqual( (today.getFullYear()) );
            expect(stub).toHaveBeenCalled();

            stub.restore();
            field.view.name = originalType;
        });

        it("should return value from format if NOT edit view and no value", function() {
            var originalType = field.view.name;
            field.view.name = 'not_edit';
            expect(field.format(null)).toEqual(null);
            field.view.name = originalType;
        });
    });

});
