describe("Sugar App Language Manager", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    it("should retreive the label from the language string store according to the module and label name", function() {
        var string = app.lang.get("LBL_ASSIGNED_TO_NAME", "Contacts");
        expect(string).toEqual("Assigned to");
    });

    it("should retreive the label from app strings if its not set in mod strings", function() {
        var string = app.lang.get("DATA_TYPE_DUE", "Accounts");
        expect(string).toEqual("Due");
    });

    it("should return the input if its not set at all", function() {
        var string = app.lang.get("THIS_LABEL_DOES_NOT_EXIST");
        expect(string).toEqual("THIS_LABEL_DOES_NOT_EXIST");
    });

    it("should retrieve app string", function() {
        expect(app.lang.getAppString('DATA_TYPE_DUE')).toEqual("Due");
    });

    it("should return key if can't find app strings from key", function() {
        expect(app.lang.getAppString('BOGUS')).toEqual('BOGUS');
    });

    it("should retrieve app list strings as string", function() {
        expect(app.lang.getAppListStrings('case_priority_default_key')).toEqual('P2');
    });

    it("should retrieve app list strings as object", function() {
        expect(app.lang.getAppListStrings('merge_operators_dom')).toEqual(fixtures.metadata.appListStrings.merge_operators_dom);
    });

    it("should retrieve app list strings as undefined if the key doesn't exist", function() {
        expect(app.lang.getAppListStrings('BOGUS')).toBeUndefined();
    });

});

