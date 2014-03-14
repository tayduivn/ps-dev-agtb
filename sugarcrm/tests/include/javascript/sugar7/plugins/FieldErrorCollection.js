/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
describe('Plugins.FieldErrorCollection', function() {
    var view, layout, getFieldStub, renderStub, field;
    beforeEach(function() {
        SugarTest.loadPlugin('FieldErrorCollection');
        layout = SugarTest.createLayout('base', 'ForecastWorksheets', 'list', null, null);
        view = SugarTest.createView('base', 'Forecasts', 'list-headerpane', null, null, true, layout, true);

        renderStub = sinon.stub(view, 'on', function() {});
        getFieldStub = sinon.stub(view, 'getField', function() {
            return {
                setDisabled: function() {}
            }
        });

        field = {};
        field.model = new Backbone.Model();
        field.model.set({
            id: 'fieldID'
        });
    });

    afterEach(function() {
        renderStub.restore();
        getFieldStub.restore();
        layout = null;
        view = null;
        field = null;
    });

    describe('handleErrorEvent', function() {
        var triggerSpy;
        beforeEach(function() {
            triggerSpy = sinon.spy(view.context, 'trigger');
        });

        afterEach(function() {
            triggerSpy.restore();
        });

        it('should add field model on field error', function() {
            view.context.trigger('field:error', field, true);
            expect(view._errorCollection.models[0].get('id')).toBe('fieldID');
        });

        it('should remove field model on field error clear', function() {
            view.context.trigger('field:error', field, true);
            view.context.trigger('field:error', field, false);
            expect(view._errorCollection.models.length).toEqual(0);
        });

        it('should trigger its own event on the context', function() {
            view.context.trigger('field:error', field, true);
            expect(triggerSpy).toHaveBeenCalledWith('plugin:fieldErrorCollection:hasFieldErrors');
        });
    });

    describe('hasFieldErrors', function() {
        it('should be true if a field still has an error state', function() {
            view.context.trigger('field:error', field, true);
            expect(view.hasFieldErrors()).toBeTruthy();
        });

        it('should be false if all fields have cleared their errors', function() {
            view.context.trigger('field:error', field, true);
            view.context.trigger('field:error', field, false);
            expect(view.hasFieldErrors()).toBeFalsy();
        });
    });
});
