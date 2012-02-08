describe("Layout", function() {

    it('creates views', function () {
        expect(SUGAR.App.layout.get({
            view : "EditView",
            module: "Contacts"
        })).not.toBe(null);
    });
});