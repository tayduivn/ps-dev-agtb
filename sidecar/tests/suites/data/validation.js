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

        it("should be able to validate a big numeric value", function() {
            var result = validation.validators.maxLength(field, 100000);
            expect(result).toBeDefined();
        });

    });


    describe("'required' validator", function() {

        var rv = validation.requiredValidator,
            field = { required: true }; // field metadata

        it("should be able to validate an empty string field set on a bean with a field already set", function() {
            var bean = new Bean({ name: "foo" }),
                result = rv(field, "name", bean, "");
            expect(result).toBeFalsy();

            result = rv(field, "name", bean, undefined);
            expect(result).toBeTruthy();

            result = rv(field, "name", bean, null);
            expect(result).toBeFalsy();
        });

        it("should be able to validate an empty string field set on a bean with unset field", function() {
            var bean = new Bean(),
                result = rv(field, "name", bean, "");
            expect(result).toBeFalsy();

            result = rv(field, "name", bean, undefined);
            expect(result).toBeFalsy();

            result = rv(field, "name", bean, null);
            expect(result).toBeFalsy();
        });

        it("should be able to validate a non-empty string field set on a bean with unset field", function() {
            var bean = new Bean(),
                result = rv(field, "name", bean, "bar");
            expect(result).toBeTruthy();
        });

        it("should be able to validate a non-empty string field set on a bean with a field already set", function() {
            var bean = new Bean({ name: "foo" }),
                result = rv(field, "name", bean, "bar");
            expect(result).toBeTruthy();
        });

        it("should skip validation if a field is not required", function() {
            var bean = new Bean(),
                result = rv({required: false}, "name", bean, "");
            expect(result).toBeTruthy();

            result = rv({}, "name", bean, "");
            expect(result).toBeTruthy();
        });

    });


});
