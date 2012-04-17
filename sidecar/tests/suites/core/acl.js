describe("ACLs", function() {
    beforeEach(function() {
        this.acl = SUGAR.App.acl;
        this.acl.set(fixtures.metadata.acl);
    });

    afterEach(function() {
        this.acl.acls = {};
    });

    it("should store ACLs", function() {
        this.acl.set(fixtures.metadata.acl)
        expect(this.acl.acls).toEqual(fixtures.metadata.acl);
        this.acl.acls = {};
    });
    describe("for modules", function() {
        it("should check for module view/edit access", function() {
            var module = 'Cases';
            var action = 'edit';
            var model = new Backbone.Model();
            var access = this.acl.hasAccess(module, action, model);
            expect(access).toBeFalsy();
        });
        it("should return true if not acls for view are defined", function() {
            var module = 'Cases';
            var action = 'thisActionHasNoACLs';
            var model = new Backbone.Model();
            var access = this.acl.hasAccess(module, action, model);
            expect(access).toBeTruthy();
        });
    });
    describe("for fields", function() {
        it("should return true if no field acl is specified", function() {
            var module = 'Cases';
            var fieldName = 'thisfieldhasnospecificACLs';
            var action = 'edit';
            var model = new Backbone.Model();
            var access = this.acl.hasAccess(module, action, model, fieldName);
            expect(access).toBeTruthy();
            fieldName = "status"
            action = 'thisActionHasNoACLs';
            var access = this.acl.hasAccess(module, action, model, fieldName);
            expect(access).toBeTruthy();
        });

        it("should check access to fields for read, edit", function() {
            var module = 'Cases';
            var fieldName = 'status';
            var action = 'edit';
            var model = new Backbone.Model();
            var access = this.acl.hasAccess(module, action, model, fieldName);
            expect(access).toBeFalsy();
        });

        it("should check access to fields for owner", function() {
            var module = 'Cases';
            var fieldName = 'name';
            var action = 'edit';
            var model = new Backbone.Model({assigned_user_id: 'seed_sally_id'});
            var access = this.acl.hasAccess(module, action, model, fieldName);
            expect(access).toBeTruthy();
            var model = new Backbone.Model({assigned_user_id: 'seed_sally_bob'});
            var access = this.acl.hasAccess(module, action, model, fieldName);
            expect(access).toBeFalsy();
        });
    });

    it("should return true for everything if you are a module admin", function() {
        var acl = fixtures.metadata.acl;
        acl.Cases.admin = "yes";
        this.acl.set(acl);
        var module = 'Cases';
        var action = 'edit';
        var model = new Backbone.Model();
        var access = this.acl.hasAccess(module, action, model);
        expect(access).toBeTruthy();
        var module = 'Cases';
        var fieldName = 'status';
        var action = 'edit';
        var model = new Backbone.Model();
        var access = this.acl.hasAccess(module, action, model, fieldName);
        expect(access).toBeTruthy();
        this.acl.acls = {};
    });

});