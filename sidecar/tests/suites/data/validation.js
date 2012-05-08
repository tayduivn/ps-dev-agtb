describe("Validation", function() {

    var validation = SUGAR.App.validation,
        Bean = SUGAR.App.Bean;

    describe("'maxLength' validator", function() {

        var field = { len: 5 }; // field metadata

        it("should be able to validate a long string value", function() {

            var result = validation.validators.maxLength(field, "some value");
            expect(result).toBeDefined();
        });

        it("should be able to validate a null or undefined value", function() {
            var result = validation.validators.maxLength(field, null);
            expect(result).toBeUndefined();

            result = validation.validators.maxLength(field, undefined);
            expect(result).toBeUndefined();
        });

        it("should be able to validate a short string value", function() {
            var result = validation.validators.maxLength(field, "foo");
            expect(result).toBeUndefined();

            result = validation.validators.maxLength(field, "12345");
            expect(result).toBeUndefined();
        });

        it("should be able to validate a small numeric value", function() {
            var result = validation.validators.maxLength(field, 100);
            expect(result).toBeUndefined();
        });

        it("should be able to validate a large numeric value", function() {
            var result = validation.validators.maxLength(field, 100000);
            expect(result).toBeDefined();
        });
    });

    describe("'minLength' validator", function() {
        var field = {minlen: 3}; // TODO: Update this to the proper property, using minlen for now

        it("should return the minimum length if the string does not validate", function() {
            var result = validation.validators.minLength(field, ".");
            expect(result).toEqual(3);
        });

        it("should be able to validate a long string value", function() {
            var result = validation.validators.minLength(field, "some value");
            expect(result).toBeUndefined();
        });

        it("should be able to validate a null or undefined value", function() {
            var result = validation.validators.minLength(field);
            expect(result).toBeDefined();
        });

        it("should be able to validate a short string value", function() {
            var result = validation.validators.minLength(field, "hi");
            expect(result).toBeDefined();
        });

        it("should be able to validate a just short enough string value", function() {
            var result = validation.validators.minLength(field, "hit");
            expect(result).toBeUndefined();
        });

        it("should be able to validate a small numeric value", function() {
            var result = validation.validators.minLength(field, 10);
            expect(result).toBeDefined();
        });

        it("should be able to validate a large numeric value", function() {
            var result = validation.validators.minLength(field, 19280);
            expect(result).toBeUndefined();
        });
    });

    xdescribe("'url' validator", function() {
        var result,
            field = {type: "url"};

        it("should be able to validate a valid url", function() {
            result = validation.validators.url(field, "http://www.google.com");
            expect(result).toBeUndefined();

            result = validation.validators.url(field, "http://docs.google.com");
            expect(result).toBeUndefined();

            result = validation.validators.url(field, "test.google.com");
            expect(result).toBeUndefined();
        });

        it("should be able to invalidate an invalid url", function() {
            result = validation.validators.url(field, "something.something");
            expect(result).toBeDefined();
        });
    });

    xdescribe("'email' validator", function() {
        var result,
            field = {type: "email"};

        it("should be able to validate a valid url", function() {
            result = validation.validators.url(field, "somebody's.name@name.com");
            expect(result).toBeUndefined();

            result = validation.validators.url(field, "generic@generic.domain.net");
            expect(result).toBeUndefined();

            result = validation.validators.url(field, "test.email@test.google.com");
            expect(result).toBeUndefined();
        });

        it("should be able to invalidate an invalid url", function() {
            result = validation.validators.url(field, "ema#l@something.something.com");
            expect(result).toBeDefined();
        });

        result = validation.validators.url(field, "email@.something.something.com");
        expect(result).toBeDefined();
    });

    describe("'required' validator", function() {

        var rv = validation.requiredValidator,
            field = { required: true }; // field metadata

        it("should be able to validate an empty string field set on a bean with a field already set", function() {
            var bean = new Bean({ name: "foo" }),
                result = rv(field, "name", bean, "");
            expect(result).toBeTruthy();

            result = rv(field, "name", bean, undefined);
            expect(result).toBeFalsy();

            result = rv(field, "name", bean, null);
            expect(result).toBeTruthy();
        });

        it("should be able to validate an empty string field set on a bean with unset field", function() {
            var bean = new Bean(),
                result = rv(field, "name", bean, "");
            expect(result).toBeTruthy();

            result = rv(field, "name", bean, undefined);
            expect(result).toBeTruthy();

            result = rv(field, "name", bean, null);
            expect(result).toBeTruthy();
        });

        it("should be able to validate a non-empty string field set on a bean with unset field", function() {
            var bean = new Bean(),
                result = rv(field, "name", bean, "bar");
            expect(result).toBeFalsy();
        });

        it("should be able to validate a non-empty string field set on a bean with a field already set", function() {
            var bean = new Bean({ name: "foo" }),
                result = rv(field, "name", bean, "bar");
            expect(result).toBeFalsy();
        });

        it("should skip validation if a field is not required", function() {
            var bean = new Bean(),
                result = rv({required: false}, "name", bean, "");
            expect(result).toBeFalsy();

            result = rv({}, "name", bean, "");
            expect(result).toBeFalsy();
        });
    });
});