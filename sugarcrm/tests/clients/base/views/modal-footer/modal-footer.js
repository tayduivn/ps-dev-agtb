describe("Base.View.Modal-Footer", function() {
    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        context = app.context.getContext();
        view = SugarTest.createView("base","Contacts", "modal-footer", null, context);
        view.model = new Backbone.Model();

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
    });

    it("should loadData getting modal-body's metadata", function() {
        var calledEvent = '';
        view.layout = {
            getPopupComponent: function() {
                return {
                    model: {
                        module: 'Contacts'
                    },
                    context: {
                        get: function() {
                            return 'list';
                        }
                    }
                }
            }
        }
        view.loadData();
        expect(view.meta).toEqual(app.metadata.getView('Contacts', 'list'));
    });

    it("should call model.save when it clicks saveModel button", function() {
        var Contact = Backbone.Model.extend({});
        view.model = new Contact({
            first_name: 'Foo',
            last_name: 'Bar',
            address: '123 blah way'
        });
        view.model.save = function() {
            sinon.stub();
        }

        sinon.spy(view.model, "save");
        var calledEvent = '';
        view.layout = {
            getPopupComponent: function() {
                return {
                    model: view.model,
                    context: {
                        get: function() {
                            return 'list';
                        }
                    },
                    module: 'Contacts',
                    getFields: function() {
                        return _.keys(view.model.attributes)
                    }
                }
            },
            trigger: function(event) {

            }
        }
        view.saveModel();
        expect(view.model.save).toHaveBeenCalledOnce();
    });
});