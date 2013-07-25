describe("Base.Fields.Phone", function() {
    var field;

    beforeEach(function(){
        field = SugarTest.createField("base", "phone", "phone", "detail", {});
    });

    afterEach(function() {
        field = null;
    });
    it("should figure out if skype is enabled", function() {
        var metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo', function(){
            return {
              "system_skypeout_on": true
            };
        });
        field.initialize(field.options);

        expect(field.skypeEnabled).toBeTruthy();
        metamock.restore();
        var metamock = sinon.stub(SugarTest.app.metadata,'getServerInfo', function(){
            return {
                "system_skypeout_on": false
            };
        });
        field.initialize(field.options);

        expect(field.skypeEnabled).toBeFalsy();
        metamock.restore();
    });
    it("should format values if theyre in the correct format", function() {
        var data = {
            '+123443asdf':'+123443',
            '001(234)43asdf':'+00123443',
            '011(234)43asdf':'+01123443'

        }
        var formatted
        field.skypeEnabled = true;
        field.action = 'detail';
        _.each(data, function(value, key){
            formatted = field.format(key);
            expect(formatted).toEqual(value);
        });
    });
});
