describe('metadata', function () {
    //Preload the templates
    SUGAR.App.template.load(fixtures.templates);

    beforeEach(function () {
        //Load the metadata
        SUGAR.App.metadata.set(fixtures.metadata);
    });

    afterEach(function () {
    });

    it('exists', function () {
        expect(typeof(SUGAR.App.metadata)).toBe('object');
    });

    it('gets vardefs for a bean', function () {
        expect(SUGAR.App.metadata.get({
            type:"vardef",
            module:"Contacts",
            bean:"Contact"
        })).toBe(fixtures.metadata.modules.Contacts.beans.Contact.fields);
    });

    it('gets viewdefs', function () {
        expect(SUGAR.App.metadata.get({
            type:"view",
            module:"Contacts"
        })).toBe(fixtures.metadata.modules.Contacts.views);
    });

    it('gets defs for a specific view', function () {
        expect(SUGAR.App.metadata.get({
            type:"view",
            module:"Contacts",
            view:"editView"
        })).toBe(fixtures.metadata.modules.Contacts.views.editView);
    });

    it('gets layoutdefs', function () {
        expect(SUGAR.App.metadata.get({
            type:"layout",
            module:"Contacts"
        })).toBe(fixtures.metadata.modules.Contacts.layouts);
    });

    it('gets a specific layout', function () {
        expect(SUGAR.App.metadata.get({
            type:"layout",
            module:"Contacts",
            layout:'detail'
        })).toBe(fixtures.metadata.modules.Contacts.layouts.detail);
    });

    it('gets a specific sugarfield', function () {
        expect(SUGAR.App.metadata.get({
            sugarField:{
                type:'varchar',
                view:'editView'
            }
        })).toBe(fixtures.metadata.sugarFields.text.views.editView);
    });

    it('gets a specific sugarfield defaulted to default if the view does not exist', function () {
        expect(SUGAR.App.metadata.get({
            sugarField:{
                type:'varchar',
                view:'thisViewDoesntExist'
            }
        })).toBe(fixtures.metadata.sugarFields.text.views["default"]);
    });

    it ('should sync metadata', function (){
        //Spy on API
        SUGAR.App.api = {
            getMetadata:function(modules, filters, callbacks){
                var metadata = fixtures.metadata.modules
                callbacks.success(metadata);
            }
        };

        var apiSpy = sinon.spy(SUGAR.App.api, "getMetadata");
        var setSpy = sinon.spy(SUGAR.App.metadata, "set");
        var cbSpy = sinon.spy();

        SUGAR.App.metadata.sync(cbSpy);

        expect(apiSpy).toHaveBeenCalled();
        expect(setSpy).toHaveBeenCalled();
        expect(cbSpy).toHaveBeenCalled()

        SUGAR.App.api.getMetadata.restore();
    });
});