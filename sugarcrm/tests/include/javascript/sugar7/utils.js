/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

describe("Sugar7 utils", function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) { eval(d) });
    });

    afterEach(function() {

    });

    describe("getSubpanelCollection()", function() {
        it("should return the proper subpanel collection", function() {
            var ctx = {};
            ctx.children = [];

            var mdl = new Backbone.Model(),
                targetMdl = new Backbone.Model();

            targetMdl.set({id: 'targetMdl'});
            mdl.set({module: 'Test'});

            var col = new Backbone.Collection();
            col.add(targetMdl);

            mdl.set({collection: col});
            ctx.children.push(mdl);

            var targetCol = app.utils.getSubpanelCollection(ctx, 'Test');

            expect(targetCol.models[0].get('id')).toEqual('targetMdl');
        });
    });
});
