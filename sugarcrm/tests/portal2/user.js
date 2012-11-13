describe("Portal User extensions", function () {
    describe("app.user.addSalutationToFullName", function () {
        it("should patch Contact response data by adding salutation to full name", function () {
            var app = SUGAR.App;
            var data = {
                first_name: "Joe",
                last_name: "Plumber",
                salutation: "MR."
            };
            app.user.addSalutationToFullName(data);
            expect(data.full_name).toBe("Mr. Joe Plumber");

            data = {
                name: "Who",
                full_name: "Who",
                salutation: "DR."
            };
            app.user.addSalutationToFullName(data);
            expect(data.full_name).toBe("Dr. Who");

            data = {
                first_name: "Mister",
                last_name: "Boots"
            };
            app.user.addSalutationToFullName(data);
            expect(data.full_name).toBe("Mister Boots");

            data = {
                first_name: "Mister",
                last_name: "Boots",
                salutation: ""
            };
            app.user.addSalutationToFullName(data);
            expect(data.full_name).toBe("Mister Boots");

        });
    });
});
