describe("Base.Fields.Int", function() {
    var field;

    beforeEach(function(){
        field = SugarTest.createField("base", "int", "int", "detail", { number_group_seperator: "," });
    });

    afterEach(function() {
        field = null;
    });
    it("should format the value", function() {
        expect(field.format("123456.502")).toEqual("123,457");
        expect(field.unformat("123456.498")).toEqual("123456");
    });

    it("should format zero", function() {
        expect(field.format(0)).toEqual('0');
    });

    it("should not format a non number string", function() {
        expect(field.format("Asdt")).toBeUndefined();
    });
});
