describe("ACLs", function() {
    var app, model;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SUGAR.App;
        model = app.data.createBean("Cases", {id: "1234", assigned_user_id: 'seed_sally_id'});
        app.user.id = "seed_sally_id";
    });

    describe("for modules", function() {
        it("should check for module view/edit access", function() {
            expect(app.acl.hasAccess("edit", "Cases")).toBeFalsy();
        });
        it("should return true if not acls for view are defined", function() {
            expect(app.acl.hasAccess("thisActionHasNoACLs", "Cases")).toBeTruthy();
        });
    });

    describe("for fields", function() {
        it("should return true if no field acl is specified", function() {
            expect(app.acl.hasAccessToModel("edit", model, 'thisfieldhasnospecificACLs')).toBeTruthy();
            expect(app.acl.hasAccessToModel("thisActionHasNoACLs", model, 'status')).toBeTruthy();
        });

        it("should check access to fields for read, edit", function() {
            expect(app.acl.hasAccessToModel("edit", model, "status")).toBeFalsy();
        });

        it("should check access to fields for owner", function() {
            expect(app.acl.hasAccessToModel("edit", model, "name")).toBeTruthy();

            model.set("assigned_user_id", "seed_sally_bob");
            expect(app.acl.hasAccessToModel("edit", model, "name")).toBeFalsy();
        });
    });

    it("should return true for everything if you are a module admin", function() {
        expect(app.acl.hasAccess("edit", "Accounts")).toBeTruthy();
        expect(app.acl.hasAccess("edit", "Accounts", "status")).toBeTruthy();
    });

});
