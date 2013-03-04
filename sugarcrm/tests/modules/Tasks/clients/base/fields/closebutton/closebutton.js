describe("Tasks CloseButton", function() {
    var app, field, context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        
        var def = {'name':'record-close','type':'closebutton', 'view':'detail'};
        var Task = Backbone.Model.extend({});
        var model = new Task({
            id: 'aaa',
            name: 'boo',
            module: 'Tasks'
        });
        SugarTest.loadComponent('base', 'field', 'button');
        field = SugarTest.createField("../modules/Tasks/clients/base", 'record-close', "closebutton", "detail", def, 'Tasks', model, context);
   
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field = null;
        context = null;
    });

    it('should show if not closed', function() {
        field.model.set('status','Not Started');
        field._render();
        expect(field.isHidden).toBeFalsy();
    });
    
    it('should not show if closed', function() {
        field.model.set('status', 'Completed');
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should set module to completed if success', function() {
        field.model.set('status','Not Started');
        var saveSpy = sinon.stub(field.model,'save', function(dummy, callbacks) {callbacks.success();});
        field._close(false);

        expect(saveSpy).toHaveBeenCalled();
        expect(field.model.get('status')).toEqual('Completed');

        field.model.save.restore();
    });

    it('should leave module alone if failed', function() {
        field.model.set('status','Not Started');

        // spoof out the new interface
        field.model.isDirty = function() {return true;};
        field.model.revertAttributes = function() {};
        var saveSpy = sinon.stub(field.model,'save', function(dummy, callbacks) {callbacks.error();});
        var dirtySpy = sinon.spy(field.model,'isDirty');
        var revertSpy = sinon.spy(field.model,'revertAttributes');

        field._close(false);

        expect(saveSpy).toHaveBeenCalled();
        expect(dirtySpy).toHaveBeenCalled();
        expect(revertSpy).toHaveBeenCalled();

        field.model.save.restore();
    });
});
