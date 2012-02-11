describe("Validation", function() {

    var validation = SUGAR.App.validation,
        Bean = SUGAR.App.Bean;

    describe("'maxLength' validator", function() {

        it("should be able to validate a long string field", function() {
            var validator = validation.createValidator("maxLength", "name", 5);
            var result = validator(undefined, "some value");
            expect(result).toBeDefined();
            expect(result.maxLength).toBeDefined();
        });

        it("should be able to validate a null or undefined field", function() {
            var validator = validation.createValidator("maxLength", "name", 5);
            var result = validator(undefined, null);
            expect(result).toBeUndefined();

            result = validator(undefined, undefined);
            expect(result).toBeUndefined();
        });

        it("should be able to validate a short string field", function() {
            var validator = validation.createValidator("maxLength", "name", 5);
            var result = validator(undefined, "foo");
            expect(result).toBeUndefined();
        });

    });


    describe("'required' validator", function() {

        var rv = validation.requiredValidator;

        it("should be able to validate a empty string field set on a bean with a field already set", function() {
            var bean = new Bean({ name: "foo" });
            var result = rv("name", bean, "");
            expect(result).toBeDefined();

            result = rv("name", bean, undefined);
            expect(result).toBeUndefined();

            result = rv("name", bean, null);
            expect(result).toBeDefined();

            result = rv("name", bean, "bar");
            expect(result).toBeUndefined();

        });

        it("should be able to validate a empty string field set on a bean with unset field", function() {
            var bean = new Bean();
            var result = rv("name", bean, "");
            expect(result).toBeDefined();

            result = rv("name", bean, undefined);
            expect(result).toBeDefined();

            result = rv("name", bean, null);
            expect(result).toBeDefined();

            result = rv("name", bean, "bar");
            expect(result).toBeUndefined();

        });

    });


});