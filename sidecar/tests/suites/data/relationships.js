describe("Relationships", function() {

    var dm = SUGAR.App.dataManager, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    it("should be able to build a relation instance (one-to-many)", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";
        var call = dm.createBean("Contacts");
        call.id = "call-1";

        var data = { prop1: "custom prop1" };
        var relation = SUGAR.App.Relationships.buildRelation("calls", opportunity, call, data);

        expect(relation.get("name")).toEqual("opportunity_calls");
        expect(relation.get("relationship")).toEqual(opportunity.relationships["opportunity_calls"]);
        expect(relation.get("id1")).toEqual(opportunity.id);
        expect(relation.get("id2")).toEqual(call.id);
        expect(relation.get("bean1")).toEqual(opportunity);
        expect(relation.get("bean2")).toEqual(call);
        expect(relation.get("data")).toEqual(data);
    });

    it("should be able to build a relation instance (one-to-many reversed)", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";
        var account = dm.createBean("Accounts");
        account.id = "account-1";

        var relation = SUGAR.App.Relationships.buildRelation("accounts", opportunity, account);

        expect(relation.get("name")).toEqual("accounts_opportunities");
        expect(relation.get("relationship")).toEqual(opportunity.relationships["accounts_opportunities"]);
        expect(relation.get("id1")).toEqual(account.id);
        expect(relation.get("id2")).toEqual(opportunity.id);
        expect(relation.get("bean1")).toEqual(account);
        expect(relation.get("bean2")).toEqual(opportunity);
        expect(relation.get("data")).toBeUndefined();
    });

    it("should be able to build a relation collection", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var relations = SUGAR.App.Relationships.buildCollection("contacts", opportunity);

        expect(relations.name).toEqual("opportunities_contacts");
        expect(relations.relationship).toEqual(opportunity.relationships["opportunities_contacts"]);
        expect(relations.bean).toEqual(opportunity);
    });

    it("should be able to add related beans", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";
        var call = dm.createBean("Contacts");
        call.id = "call-1";

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("update");
        opportunity.addRelated("calls", call);
        mock.verify();
    });

    it("should be able to remove related bean", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("delete");
        opportunity.removeRelated("contacts", "contact-1");
        mock.verify();
    });

    it("should be able to fetch related beans", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");
        opportunity.fetchRelated("contacts");
        mock.verify();

    });

    it("should be able to set an attribute of type 'relate'", function() {
        dm.declareModels(metadata);

        var opportunity = dm.createBean("Opportunities", {
            account_name: "Account 1",
            account_id: "account-1"
        }, undefined);

        opportunity.id = "opp-1";

        var account = dm.createBean("Accounts", { name: 'Account-2'});
        account.id = "account-2";

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("update");
        opportunity.setRelated("account_name", account);
        mock.verify();

    });


});