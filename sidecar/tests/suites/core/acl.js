describe("ACLs", function() {
    beforeEach(function() {
        this.acl = SUGAR.App.acl;
        this.acl.set(fixtures.metadata.acl);
        this.model = new Backbone.Model({assigned_user_id: 'seed_sally_id'});
        this.model.module = 'Cases';
    });

    afterEach(function() {
        this.acl.acls = {};
    });

    it("should store ACLs", function() {
        this.acl.set(fixtures.metadata.acl)
        expect(this.acl.acls).toEqual(fixtures.metadata.acl);
    });
    describe("for modules", function() {
        it("should check for module view/edit access", function() {
            var action = 'edit';
            var access = this.acl.hasAccess(action, this.model);
            expect(access).toBeFalsy();
        });
        it("should return true if not acls for view are defined", function() {
            var action = 'thisActionHasNoACLs';
            var model = new Backbone.Model();
            var access = this.acl.hasAccess(action, this.model);
            expect(access).toBeTruthy();
        });
    });
    describe("for fields", function() {
        it("should return true if no field acl is specified", function() {
            var fieldName = 'thisfieldhasnospecificACLs';
            var action = 'edit';
            var access = this.acl.hasAccess(action, this.model, fieldName);
            expect(access).toBeTruthy();
            fieldName = "status"
            action = 'thisActionHasNoACLs';
            var access = this.acl.hasAccess(action, this.model, fieldName);
            expect(access).toBeTruthy();
        });

        it("should check access to fields for read, edit", function() {
            var fieldName = 'status';
            var action = 'edit';
            var access = this.acl.hasAccess(action, this.model, fieldName);
            expect(access).toBeFalsy();
        });

        it("should check access to fields for owner", function() {
            var fieldName = 'name';
            var action = 'edit';
            var access = this.acl.hasAccess(action, this.model, fieldName);
            expect(access).toBeTruthy();
            var model = new Backbone.Model({assigned_user_id: 'seed_sally_bob'});
            model.module = 'Cases';
            var access = this.acl.hasAccess(action, model, fieldName);
            expect(access).toBeFalsy();
        });
    });

    it("should return true for everything if you are a module admin", function() {
        var acl = fixtures.metadata.acl;
        acl.Cases.admin = "yes";
        this.acl.set(acl);
        var action = 'edit';
        var access = this.acl.hasAccess(action, this.model);
        expect(access).toBeTruthy();
        var fieldName = 'status';
        var action = 'edit';
        var access = this.acl.hasAccess(action, this.model, fieldName);
        expect(access).toBeTruthy();
        this.acl.acls = {};
    });

});