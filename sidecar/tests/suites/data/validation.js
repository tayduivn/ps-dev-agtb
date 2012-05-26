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

    describe("'url' validator", function() {
        var field = {type: "url"},
            v = validation.validators.url;

        it("should be able to validate a valid url", function() {
            expect(v(field, "http://www.google.com")).toBeUndefined();
            expect(v(field, "http://docs.google.com")).toBeUndefined();

            expect(v(field, "http://example.com")).toBeUndefined();
            expect(v(field, "https://example.com")).toBeUndefined();

            expect(v(field, "http://example.com/sugar")).toBeUndefined();
            expect(v(field, "https://example.com/sugar")).toBeUndefined();

            expect(v(field, "http://example.com:8888")).toBeUndefined();
            expect(v(field, "http://example.com:8888/sugar")).toBeUndefined();

            expect(v(field, "http://192.168.129.107/sugar")).toBeUndefined();
            expect(v(field, "https://192.168.129.107/sugar")).toBeUndefined();

            expect(v(field, "http://192.168.129.107:8888")).toBeUndefined();
            expect(v(field, "http://192.168.129.107:8888/sugar")).toBeUndefined();

            expect(v(field, "http://127.0.0.1/sugar")).toBeUndefined();
            expect(v(field, "https://127.0.0.1/sugar")).toBeUndefined();

            expect(v(field, "http://127.0.0.1:8888")).toBeUndefined();
            expect(v(field, "http://127.0.0.1:8888/sugar")).toBeUndefined();
        });

        it("should be able to invalidate an invalid url", function() {
            expect(v(field, "test.google.com")).toBeTruthy();
            expect(v(field, "http://localhost")).toBeTruthy();
            expect(v(field, "http://localhost:8888")).toBeTruthy();
            expect(v(field, "http://localhost/sugar")).toBeTruthy();
            expect(v(field, "http://localhost:8888/sugar")).toBeTruthy();
        });
    });

    describe("'email' validator", function() {
        var result,
            field = {type: "email"};

        it("should be able to validate a valid email", function() {
            result = validation.validators.email(field, [{email_address: "somebody's.name@name.com"}]);
            expect(result).toBeUndefined();

            result = validation.validators.email(field, [{email_address: "generic@generic.domain.net"}, {email_address: "test.email@test.google.com"}]);
            expect(result).toBeUndefined();
        });

        it("should be able to invalidate invalid emails", function() {
            result = validation.validators.email(field, [{email_address: "ema#l@something.something.com"}, {email_address: "email@.something.something.com"}]);
            expect(result).toEqual(["email@.something.something.com"]);
        });

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