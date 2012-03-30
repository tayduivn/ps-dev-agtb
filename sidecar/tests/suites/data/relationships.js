describe("Relationships", function() {

    var dm = SUGAR.App.data, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    describe("Factory", function() {
        it("should be able to create a related bean instance from a bean ID", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "opp-1";

            var attrs = { first_name: "John", last_name: "Smith", contact_role: "Decision Maker" };
            var contact = dm.createRelatedBean(opportunity, "contact-1", "contacts", attrs);

            expect(contact.link).toBeDefined();
            expect(contact.link.name).toEqual("contacts");
            expect(contact.link.bean).toEqual(opportunity);
            expect(contact.id).toEqual("contact-1");
            expect(contact.get("first_name")).toEqual("John");
            expect(contact.get("last_name")).toEqual("Smith");
            expect(contact.get("contact_role")).toEqual("Decision Maker");
        });

        it("should be able to create a related bean instance from a bean", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "opp-1";

            var attrs = { id: "contact-1", first_name: "John", last_name: "Smith", contact_role: "Decision Maker" };
            var contact = dm.createBean("Contacts", attrs);
            var relation = dm.createRelatedBean(opportunity, contact, "contacts");

            expect(contact).toEqual(relation);
            expect(contact.link).toBeDefined();
            expect(contact.link.name).toEqual("contacts");
            expect(contact.link.bean).toEqual(opportunity);
            expect(contact.id).toEqual("contact-1");
            expect(contact.get("first_name")).toEqual("John");
            expect(contact.get("last_name")).toEqual("Smith");
            expect(contact.get("contact_role")).toEqual("Decision Maker");
        });

        it("should be able to create a new related bean instance", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "opp-1";

            var attrs = { id: "contact-1", first_name: "John", last_name: "Smith", contact_role: "Decision Maker" };
            var contact = dm.createRelatedBean(opportunity, null, "contacts", attrs);

            expect(contact.link).toBeDefined();
            expect(contact.link.name).toEqual("contacts");
            expect(contact.link.bean).toEqual(opportunity);
            expect(contact.id).toEqual("contact-1");
            expect(contact.get("first_name")).toEqual("John");
            expect(contact.get("last_name")).toEqual("Smith");
            expect(contact.get("contact_role")).toEqual("Decision Maker");
        });

        it("should be able to create a collection of related beans", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "opp-1";

            var contacts = dm.createRelatedCollection(opportunity, "contacts");

            expect(contacts.module).toEqual("Contacts");
            expect(contacts.link).toBeDefined();
            expect(contacts.link.name).toEqual("contacts");
            expect(contacts.link.bean).toEqual(opportunity);
        });
    });

    describe("CRUD", function() {

        var server;

        beforeEach(function() {
            server = sinon.fakeServer.create();
        });

        afterEach(function() {
            if (server && server.restore) server.restore();
        });

        it("should be able to fetch related beans", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "1";

            server.respondWith("GET", /\/Opportunities\/1\/contacts/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/opportunities/1/contacts"].GET.response)]);

            var contacts = dm.createRelatedCollection(opportunity, "contacts");
            contacts.fetch({ relate: true });
            server.respond();

            expect(server.requests[0].requestBody).toBeNull();
            expect(contacts.length).toEqual(3);
            _.each([
                "6beade8e-ea5c-1906-203f-4f501294939e",
                "877df603-25c8-a601-198c-4f50124b0366",
                "8c440c9c-7357-54d2-7b43-4f5012552ba9"
            ], function(id) {
                var contact = contacts.get(id);
                expect(contact).toBeDefined();
                expect(contact.get("first_name")).toBeDefined();
                expect(contact.get("last_name")).toBeDefined();
                expect(contact.get("opportunity_role")).toBeDefined();
                expect(contact.link).toEqual(contacts.link);
            });
        });

        it("should be able to create a related bean", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "1";
            var contact = dm.createRelatedBean(opportunity, null, "contacts", {
                first_name: "John",
                last_name: "Smith",
                opportunity_role: "Influencer"
            });

            server.respondWith("POST", /\/Opportunities\/1\/contacts/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/opportunities/1/contacts"].POST.response)]);

            contact.save(null, { relate: true });
            server.respond();

            expect(server.requests[0].requestBody).toEqual('{"field_0":100,"first_name":"John","last_name":"Smith","opportunity_role":"Influencer"}');
            expect(contact.id).toEqual("2");
            expect(contact.get("date_modified")).toBeDefined();
            expect(opportunity.get("date_modified")).toBeDefined();
        });

        it("should be able to delete a relationship", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "1";
            var contact = dm.createRelatedBean(opportunity, null, "contacts", { id: "2" });

            server.respondWith("DELETE", /\/Opportunities\/1\/contacts\/2/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/opportunities/1/contacts"].DELETE.response)]);

            contact.destroy({ relate: true });
            server.respond();

            expect(server.requests[0].requestBody).toBeNull();
            expect(opportunity.get("date_modified")).toBeDefined();
            expect(contact.get("date_modified")).toBeDefined();
        });

        it("should be able to update a relationship", function() {
            dm.declareModels(metadata);

            var opportunity = dm.createBean("Opportunities");
            opportunity.id = "1";
            var contact = dm.createRelatedBean(opportunity, null, "contacts", { id: "2" });
            contact.set({ opportunity_role: "Primary Decision Maker" })

            server.respondWith("PUT", /\/Opportunities\/1\/contacts\/2/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/opportunities/1/contacts"].PUT.response)]);

            contact.save(null, { relate: true });
            server.respond();

            expect(server.requests[0].requestBody).toEqual('{"field_0":100,"id":"2","opportunity_role":"Primary Decision Maker"}');
            expect(opportunity.get("date_modified")).toBeDefined();
            expect(contact.get("date_modified")).toBeDefined();
        });

    });

});