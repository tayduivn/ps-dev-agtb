describe('DragdropSelect2 Plugin', function() {
    var app, field, $select2, $items;

    beforeEach(function() {
        app = SugarTest.app;

        field = SugarTest.createField('base', 'foo', 'base');
        field.plugins = ['DragdropSelect2'];
        SugarTest.loadPlugin('DragdropSelect2');
        SugarTest.app.plugins.attach(field, 'field');

        field.$el.append('<select multiple id="e1" class="select2">' +
            '<option value="A" selected>Apple</option>' +
            '<option value="B" selected>Banana</option>' +
            '<option value="C" selected>Carrot</option>' +
            '</select>');

        $select2 = field.$('.select2');
        $select2.select2({
            formatSelection: function(item) {
                return '<span data-id="' + item.id + '">' + item.text + '</span>';
            }
        });
        $items = field.$(field.itemSelector);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('setSelectable', function() {
        var $firstItem, $lastItem;

        beforeEach(function() {
            $firstItem = $items.first();
            $lastItem = $items.last();
            field.setSelectable($select2, $items);
        });

        it('should mark item selected', function() {
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should keep item selected', function() {
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should change selection from first to last item', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(false);

            $lastItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
            expect($lastItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should select both first and last item', function() {
            var metaClickEvent = jQuery.Event('click', {keyCode: 91, metaKey: true});

            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(false);

            $lastItem.trigger(metaClickEvent);
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);
            expect($lastItem.hasClass(field.selectedClass)).toBe(true);
        });

        it('should deselect when clicking on document', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);

            $(document).click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
        });

        it('should deselect when clicking on select2 container whitespace', function() {
            $firstItem.click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(true);

            $select2.select2('container').click();
            expect($firstItem.hasClass(field.selectedClass)).toBe(false);
        });
    });

    describe('setDraggable', function() {
        var $firstItem, $lastItem;

        beforeEach(function() {
            $firstItem = $items.first();
            $lastItem = $items.last();
            field.setDraggable($select2, $items);
        });

        it('should allow drag of one item', function() {
            var event, helper, result;

            event = jQuery.Event('click', {
                keyCode: 91,
                target: $firstItem.get(0)
            });
            helper = $firstItem.draggable('option', 'helper');

            result = helper(event);
            expect($(result).find('[data-id]').length).toEqual(1);
        });

        it('should allow drag of two items', function() {
            var event, helper, result;

            $firstItem.addClass(field.selectedClass);
            $lastItem.addClass(field.selectedClass);

            event = jQuery.Event('click', {
                keyCode: 91,
                target: $firstItem.get(0)
            });
            helper = $firstItem.draggable('option', 'helper');

            result = helper(event);
            expect($(result).find('[data-id]').length).toEqual(2);
        });
    });

    describe('setDroppable', function() {
        beforeEach(function() {
            field.setDroppable($select2);
        });

        it('should move items from one field to another', function() {
            var dropHandler,
                $helper,
                sourceCollection = app.data.createBeanCollection(),
                targetCollection = app.data.createBeanCollection(),
                item1 = app.data.createBean('Foo', {id: '123'}),
                item2 = app.data.createBean('Foo', {id: '456'}),
                item3 = app.data.createBean('Foo', {id: '789'}),
                mockEvent = {};

            sourceCollection.reset([item1, item2, item3]);
            field.model.set({
                source_field: sourceCollection,
                target_field: targetCollection
            });
            field.name = 'target_field';

            dropHandler = $select2.select2('container').droppable('option', 'drop');

            //build up mock drag helper with first and third items dragged
            $helper = $('<div data-source-field="source_field"></div>');
            $helper.append('<span data-id="' + item1.id + '"></span>');
            $helper.append('<span data-id="' + item3.id + '"></span>');

            //simulate the drop
            dropHandler(mockEvent, {helper: $helper.get(0)});

            //only item2 should remain in sourceCollection
            expect(sourceCollection.contains(item1)).toBe(false);
            expect(sourceCollection.contains(item2)).toBe(true);
            expect(sourceCollection.contains(item3)).toBe(false);

            //item1 and item3 should now be in targetCollection
            expect(targetCollection.contains(item1)).toBe(true);
            expect(targetCollection.contains(item2)).toBe(false);
            expect(targetCollection.contains(item3)).toBe(true);
        });
    });
});
