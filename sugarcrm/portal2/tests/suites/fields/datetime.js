describe("sugarfields", function() {

    describe("datetime", function() {
        it("should format the datetime for the user", function() {

            var controller = SugarFieldTest.loadSugarField('datetime/datetime'),
                field = SugarFieldTest.createField("datetime", "detail"),
                unformatedValue, expectedValue,
                myUser = SUGAR.App.user.getUser();
            field = _.extend(field, controller);
            
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');

            unformatedValue = '2012-02-14T13:15:17-0600';
            expectedValue = '02/14/2012 13:15';
            expect(field.format(unformatedValue)).toEqual(expectedValue);
        });
        it("should format the datetime for the portal user", function() {

            var controller = SugarFieldTest.loadSugarField('datetime/datetime','portal'),
                field = SugarFieldTest.createField("datetime", "detail"),
                unformatedValue, expectedValue,
                myUser = SUGAR.App.user.getUser();
            field = _.extend(field, controller);
            
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');

            // Should create a date and set it to the current date/time
            var myDate = new Date();

            // Simulate the ISO string that the date/time formatting will hand in (will be in GMT here)
            unformatedValue = myDate.toISOString();
            
            expectedValue = SUGAR.App.utils.date.format(myDate,'m/d/Y H:i');
            expect(field.format(unformatedValue)).toEqual(expectedValue);
        });
    });
});
