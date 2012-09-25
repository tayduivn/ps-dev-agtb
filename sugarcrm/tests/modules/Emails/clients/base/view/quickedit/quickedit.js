describe("Emails - clients/base/view/quickedit", function() {

    var view = null;
    var app;
    var xhr;
    var requests;

    beforeEach(function() {
        view = SugarTest.createModuleView('base', 'Emails', 'quickedit');
        view.model = new Backbone.Model();
        view.collection = new Backbone.Collection(view.model);
        app = SUGAR.App;

        xhr = sinon.useFakeXMLHttpRequest();
        requests = this.requests = [];

        xhr.onCreate = function (xhr) {
            requests.push(xhr);
        };
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        xhr.restore();
        
        app = null;
        view = null;
        xhr = null;
        requests = null;
        delete Handlebars.templates;
    });


    describe("Sending save request", function() {
        it("should send formatted data to conform to API parameters", function() {
            var data = {
                to_addresses : "test@example.com",
                subject    : "subject",
                html_body  : "html body",
                text_body  : "text body"
            };
            
            var expected_data = _.extend({}, data);
            expected_data.to_addresses = [ { email: "test@example.com" } ]; 
            
            view.model.set(data);
            view.saveModel();
            
            expect(requests.length).toEqual(1);
            expect(requests[0].requestBody).toEqual(JSON.stringify(expected_data));
        });
    });

});
