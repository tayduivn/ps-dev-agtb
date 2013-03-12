describe("Base.Field.LinkButton", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });
    it('should bind linked module list from the subpanel metadata.', function() {
        var expectedLink = 'blah',
            subpanelStub = sinon.stub(app.metadata, 'getLayout', function() {
                return {components:[
                    {
                        context: {
                            link: expectedLink
                        }
                    }
                ]};
            }),
            expectedRelatedModule = 'Accounts',
            getRelateModuleStub = sinon.stub(app.data, 'getRelatedModule', function() {
                return expectedRelatedModule;
            }),
            aclStub = sinon.stub(app.acl, 'hasAccess', function() {
                return true;
            });
        field = SugarTest.createField("base","linkbutton", "linkbutton", "record");
        var Account = Backbone.Model.extend({});
        field.model = new Account({
            id: 'aaa',
            name: 'boo'
        });

        expect(field.isHidden).not.toBe(true);
        expect(field.linkModules.length).toBe(1);
        expect(_.first(field.linkModules).module).toBe(expectedRelatedModule);
        expect(_.first(field.linkModules).link).toBe(expectedLink);

        subpanelStub.restore();
        getRelateModuleStub.restore();
        aclStub.restore();

        field.model = null;
        field._loadTemplate = null;
        field = null;
    });

    it('should populate linked module list that has ACL for the module.', function() {
        var panelMetadata = [
                {
                    context: {
                        link: 'blah'
                    }
                },
                {
                    context: {
                        link: 'foo'
                    }
                }
            ],
            subpanelStub = sinon.stub(app.metadata, 'getLayout', function() {
                return {components:panelMetadata};
            }),
            getRelateModuleStub = sinon.stub(app.data, 'getRelatedModule', function(module, link) {
                var mapping = {
                    'blah' : 'Accounts',
                    'foo' : 'Contacts'
                }
                return mapping[link];
            }),
            aclStub = sinon.stub(app.acl, 'hasAccess', function(action, module) {
                var mapping = {
                    'Accounts' : false,
                    'Contacts' : true
                }
                return mapping[module];
            });
        field = SugarTest.createField("base","linkbutton", "linkbutton", "record");
        var Account = Backbone.Model.extend({});
        field.model = new Account({
            id: 'aaa',
            name: 'boo'
        });

        expect(field.isHidden).not.toBe(true);
        expect(panelMetadata.length).toBe(2);
        expect(panelMetadata[0].context.link).toBe('blah');
        expect(panelMetadata[1].context.link).toBe('foo');
        expect(panelMetadata.length).toBe(2);
        expect(field.linkModules.length).toBe(1);
        expect(_.first(field.linkModules).module).toBe('Contacts');
        expect(_.first(field.linkModules).link).toBe('foo');

        subpanelStub.restore();
        getRelateModuleStub.restore();
        aclStub.restore();


        field.model = null;
        field._loadTemplate = null;
        field = null;
    });

    it('should be hidden when the subpanel components are empty.', function() {
        var subpanelMeta = sinon.stub(app.metadata, 'getLayout', function() {
            return {components:[]};
        });
        field = SugarTest.createField("base","linkbutton", "linkbutton", "record");
        var Account = Backbone.Model.extend({});
        field.model = new Account({
            id: 'aaa',
            name: 'boo'
        });

        expect(field.isHidden).toBe(true);
        subpanelMeta.restore();


        field.model = null;
        field._loadTemplate = null;
        field = null;
    });
});
