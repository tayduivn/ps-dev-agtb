describe("sugarfields", function() {

    describe("date", function() {
        it("should format the date for the user", function() {

            var controller = SugarFieldTest.loadSugarField('date/date'),
                field = SugarFieldTest.createField("date", "detail"),
                unformatedValue, expectedValue,
                myUser = SUGAR.App.user.getUser();
            field = _.extend(field, controller);
            
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');

            unformatedValue = '2012-02-14T13:15:17-0600';
            expectedValue = '02/14/2012';
            expect(field.format(unformatedValue)).toEqual(expectedValue);
        });
    });
});
