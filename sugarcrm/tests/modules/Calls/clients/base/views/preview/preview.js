describe('Calls.View.Preview', function() {
    var preview, app, meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.testMetadata.set();

        preview = SugarTest.createView('base', 'Calls', 'preview', null, null, true);

        meta = {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name"]
            }, {
                "name": "panel_body",
                "label": "LBL_PANEL_2",
                "columns": 1,
                "labels": true,
                "labelsOnTop": false,
                "placeholders":true,
                "fields": ["status"]
            }, {
                "name": "panel_hidden",
                "hide": true,
                "labelsOnTop": false,
                "placeholders": true,
                "fields": ["location","date_entered","date_modified","modified_user_id"]
            }]
        };
    });

    afterEach(function() {
        preview.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('_previewifyMetadata', function(){
        it('should flatten reminders metadata', function(){
            var modified,
                remindersMetadata = {
                    name: 'reminders',
                    type: 'reminders',
                    fields: ['reminder_notification','reminder_email']
                };

            meta.panels[2].fields.splice(1, 0, remindersMetadata);
            modified = preview._previewifyMetadata(app.utils.deepCopy(meta));

            expect(modified).not.toEqual(meta);
            expect(modified.panels[2].fields).toEqual([
                'location',
                'reminder_notification',
                'reminder_email',
                'date_entered',
                'date_modified',
                'modified_user_id'
            ]);
        });

        it('should display label for duration field', function(){
            var modified,
                durationMetadata = {
                    name: 'duration',
                    type: 'duration',
                    detail_view_label: 'foo',
                    dismiss_label: true,
                    fields: ['date_start','date_end']
                };

            meta.panels[1].fields.push(durationMetadata);
            modified = preview._previewifyMetadata(app.utils.deepCopy(meta));

            expect(modified).not.toEqual(meta);
            expect(modified.panels[1].fields[1].label).toBe('foo');
            expect(modified.panels[1].fields[1].dismiss_label).toBe(false);
        });

        it('should not display recurrence field', function(){
            var modified,
                recurrenceMetadata = {
                    name: 'recurrence',
                    type: 'recurrence',
                    fields: ['repeat_interval','repeat_dow','repeat_until','repeat_count']
                };

            meta.panels[1].fields.push(recurrenceMetadata);
            modified = preview._previewifyMetadata(app.utils.deepCopy(meta));

            expect(modified).not.toEqual(meta);
            expect(modified.panels[1].fields).toEqual(['status']);
        });
    });
});
