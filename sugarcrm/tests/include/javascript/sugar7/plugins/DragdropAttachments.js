describe("DragdropAttachments Plugin", function() {
    var plugin;

    beforeEach(function() {
        SugarTest.loadPlugin('DragdropAttachments');
        plugin = SugarTest.app.plugins._get('DragdropAttachments', 'view');
    });

    describe("_mapNoteParentAttributes", function() {
        var dataProvider = [
            {
                message: "Should add parent if parentId and parentType specified",
                note: new Backbone.Model(),
                parentId: '123',
                parentType: 'Accounts',
                expectedAttributes: {
                    parent_id: '123',
                    parent_type: 'Accounts'
                }
            },
            {
                message: "Should not add parent if parentId not specified",
                note: new Backbone.Model(),
                parentType: 'Accounts',
                expectedAttributes: {}
            },
            {
                message: "Should not add parent if parentType not specified",
                note: new Backbone.Model(),
                parentId: '123',
                expectedAttributes: {}
            },
            {
                message: "Should not add parent if neither parentId or parentType specified",
                note: new Backbone.Model(),
                expectedAttributes: {}
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = plugin._mapNoteParentAttributes(data.parentId, data.parentType);
                expect(actual).toEqual(data.expectedAttributes);
            });
        }, this);
    });
});
