// forms datetime
function _render_content(view, app) {
    // sugar7 date field
    //TODO: figure out how to set the date value when calling createField
    view.model.start_date = '2000-01-01T22:47:00+00:00';
    var fieldSettingsDate = {
        view: view,
        def: {
            name: 'start_date',
            type: 'date',
            view: 'edit',
            enabled: true
        },
        viewName: 'edit',
        context: view.context,
        module: view.module,
        model: view.model,
        meta: app.metadata.getField('date')
    },
    dateField = app.view.createField(fieldSettingsDate);
    view.$('#sugar7_date').append(dateField.el);
    dateField.render();

    // sugar7 datetimecombo field
    view.model.start_datetime = '2000-01-01T22:47:00+00:00';
    var fieldSettingsCombo = {
        view: view,
        def: {
            name: 'start_datetime',
            type: 'datetimecombo',
            view: 'edit',
            enabled: true
        },
        viewName: 'edit',
        context: view.context,
        module: view.module,
        model: view.model,
        meta: app.metadata.getField('datetimecombo')
    },
    datetimecomboField = app.view.createField(fieldSettingsCombo);
    view.$('#sugar7_datetimecombo').append(datetimecomboField.el);
    datetimecomboField.render();

    // static examples
    view.$('#dp1').datepicker();
    view.$('#tp1').timepicker();

    view.$('#dp2').datepicker({format:'mm-dd-yyyy'});
    view.$('#tp2').timepicker({timeFormat:'H.i.s'});

    view.$('#dp3').datepicker();

    var startDate = new Date(2012,1,20);
    var endDate = new Date(2012,1,25);

    view.$('#dp4').datepicker()
      .on('changeDate', function(ev){
        if (ev.date.valueOf() > endDate.valueOf()){
          view.$('#alert').show().find('strong').text('The start date can not be greater then the end date');
        } else {
          view.$('#alert').hide();
          startDate = new Date(ev.date);
          view.$('#startDate').text(view.$('#dp4').data('date'));
        }
        view.$('#dp4').datepicker('hide');
      });

    view.$('#dp5').datepicker()
      .on('changeDate', function(ev){
        if (ev.date.valueOf() < startDate.valueOf()){
          view.$('#alert').show().find('strong').text('The end date can not be less then the start date');
        } else {
          view.$('#alert').hide();
          endDate = new Date(ev.date);
          view.$('#endDate').text(view.$('#dp5').data('date'));
        }
        view.$('#dp5').datepicker('hide');
      });


    view.$('#tp3').timepicker({'scrollDefaultNow': true});

    view.$('#tp4').timepicker();
    view.$('#tp4_button').on('click', function (){
      view.$('#tp4').timepicker('setTime', new Date());
    });

    view.$('#tp5').timepicker({
      'minTime': '2:00pm',
      'maxTime': '6:00pm',
      'showDuration': true
    });

    view.$('#tp6').timepicker();
    view.$('#tp6').on('changeTime', function() {
      view.$('#tp6_legend').text('You selected: ' + $(this).val());
    });

    view.$('#tp7').timepicker({ 'step': 5 });
}

function _dispose_content(view) {
    view.$('#dp4').off('changeDate');
    view.$('#dp5').off('changeDate');
    view.$('#tp4_button').off('click');
    view.$('#tp6').off('changeTime');
}
