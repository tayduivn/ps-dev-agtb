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

    it("should check access to module actions", function() {
        expect(0).toEqual(1);
    });

    it("should return true if no field acl is specified", function() {
        var module = 'Cases';
        var fieldName = 'thisfieldhasnospecificACLs';
        var action = 'edit';
        var model = new Backbone.Model();
        var access = this.acl.hasFieldAccess(module, fieldName, action, model);
        expect(access).toBeTruthy();
        fieldName = "status"
        action = 'thisActionHasNoACLs';
        var access = this.acl.hasFieldAccess(module, fieldName, action, model);
        expect(access).toBeTruthy();
    });

    it("should check access to fields for read, edit", function() {
        var module = 'Cases';
        var fieldName = 'status';
        var action = 'edit';
        var model = new Backbone.Model();
        var access = this.acl.hasFieldAccess(module, fieldName, action, model);
        expect(access).toBeFalsy();
    });

    it("should check access to fields for owner", function() {1
        var module = 'Cases';
        var fieldName = 'name';
        var action = 'edit';
        var model = new Backbone.Model({assigned_user_id:'seed_sally_id'});
        var access = this.acl.hasFieldAccess(module, fieldName, action, model);
        expect(access).toBeTruthy();
        var model = new Backbone.Model({assigned_user_id:'seed_sally_bob'});
        var access = this.acl.hasFieldAccess(module, fieldName, action, model);
        expect(access).toBeFalsy();
    });
});