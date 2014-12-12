/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
var getNotesData,
    showNotes,
    addNotes,
    deleteNotes,
    addRow,
    deleteRow;

getNotesData = function (caseId, caseIndex, noEdit) {
    var responseData;
    $.ajax({
        url:   "./index.php?module=ProcessMaker&action=showNotes&to_pdf=1&cas_id=" + caseId + '&cas_index=' + caseIndex + '&noEdit=' + noEdit,
        async: false
    }).done(function (ajaxResponse) {
        responseData = ajaxResponse;
    });
    return responseData;
};

showNotes = function (caseId, caseIndex, noEdit) {
    var f, w, np, notesTextArea, proxy, log, newLog, pictureUrl, i;
//    hp = new HtmlPanel({
//        source: getNotesData(caseId, caseIndex, noEdit),
//        scroll: false
//    });
    proxy = new SugarProxy({
        //url: SUGAR_URL + '/rest/v10/Log/',
        url: 'pmse_Inbox/note_list/' + caseId,
//        restClient: restClient,
//        uid : caseId,
        callback: null
    });
    notesTextArea = new TextareaField({
        name: 'notesTextArea',
        label: '',
        fieldWidth: '80%'
    });

    App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
    np = new NotePanel({
        items :[notesTextArea],
        caseId : caseId,
        caseIndex : caseIndex,
        callback :{
            'loaded': function (data) {

                proxy.getData(null, {
                    success: function(notes) {


                        for (i = 0 ; i < notes.rowList.length; i += 1) {
                            log = notes.rowList[i];
                            pictureUrl = App.api.buildFileURL({
                                module: 'Users',
                                id: log.not_user_id,
                                field: 'picture'
                            });
                            newLog = {
                                name: 'log' ,
                                label: log.not_content,
                                user:  log.last_name,
                                picture : pictureUrl,
                                duration: '<strong> ' + timeElapsedString(Date.parse(notes.currentDate), Date.parse(log.date_entered)) + ' <strong>',
                                startDate: Date.parse(log.date_entered).toString('MMMM d, yyyy HH:mm'),
                                logId: log.id
                            };
                            np.addLog(newLog);

                        }
                        App.alert.dismiss('upload');
                    }
                });
//
//

            }}
    });
    w = new Window({
        width: 800,
        height: 380,
        modal: true,
        title: translate('LBL_PMSE_TITLE_CURRENT_CASE_NOTES') + ' # ' + caseId
    });

    w.addPanel(np);
    w.show();
};

addNotes = function (casId, caseIndex) {
    var txtNote = document.getElementById('txtNote'),
        countNotes = document.getElementById('countNotes'),
        strNote = txtNote.value.trim(),
        reg,
        e;
    txtNote.style.borderColor = '#CCCCCC';
    if (strNote === '') {
        txtNote.style.borderColor = 'red';
        txtNote.focus();
        return false;
    }
    reg = /<[^\s]/g;
    strNote = strNote.trim();
    e = reg.test(strNote);
    if (e) {
        strNote = strNote.replace(/</g, '< ');
    }
    $.ajax({
        url: "./index.php?module=ProcessMaker&action=addNotes&to_pdf=1",
        async: false,
        data: {not_content: strNote, cas_id: casId, cas_index: caseIndex},
        dataType: 'json',
        type: 'POST'
    })
        .done(function (ajaxResponse) {
//            console.log(ajaxResponse);
            if (ajaxResponse.success) {
                addRow(ajaxResponse.data);
                txtNote.value = '';
                countNotes.style.display = 'block';
                countNotes.innerHTML = parseInt(countNotes.innerHTML, 10) + 1;
            }
        });
};

deleteNotes = function (id) {
    var countNotes = document.getElementById('countNotes');
    $.ajax({
        url: "./index.php?module=ProcessMaker&action=deleteNotes&to_pdf=1",
        async: false,
        data: {not_id: id},
        dataType: 'json',
        type: 'POST'
    })
        .done(function (ajaxResponse) {
            if (ajaxResponse.success) {
                deleteRow(id);
                countNotes.innerHTML = parseInt(countNotes.innerHTML, 10) - 1;
            }
        });
};

addRow = function (args) {
    var table = document.getElementById('tblNotes'),
        rowCount = table.rows.length,
        row = table.insertRow(rowCount),
        lastRow,
//        colCount = table.rows[rowCount - 1].cells.length,
        col1,
        pic,
        html;

    if (rowCount == 0) {
        row.className = 'oddListRowS1';
    } else {
        lastRow = table.rows[rowCount - 1];
        if (lastRow.getAttribute('class') === 'evenListRowS1') {
            row.className = 'oddListRowS1';
        } else {
            row.className = 'evenListRowS1';
        }
    }

    row.id = args.not_id;

    if (args.user_picture == null) {
        pic = 'modules/ProcessMaker/img/default_user.png';
    } else {
        pic = 'index.php?entryPoint=download&amp;id=' + args.user_picture + '&amp;type=SugarFieldImage&amp;isTempFile=1';
    }

//    console.log(args.user_picture);

    html = '<div>';
    html += '<div style="float: left; margin-right: 3px; width: 50px; height: 50px;">';
    html += '<img style="max-width: 50px; max-height: 50px;" src="' + pic + '">';
    html += '</div>';
    html += '<div style="float: left; margin-right: 3px;">';
    html += '<strong>' + args.user_name + '</strong><br>';
    html += args.not_content + '<br>';
    html += '<span style="font-size: 11px; color: #7e7e7e;">' + args.date + '</span>';
    html += '</div>';
    html += '<div style="float: right; text-align: right;">';
    html += args.date_friendly + '<br>';
    html += '<a href="javascript:deleteRow(\'' + args.not_id + '\');">' + translate('LBL_PMSE_LABEL_DELETE') + '</a>';
    html += '</div>';
    html += '<div class="clear"></div>';
    html += '</div>';


    col1 = row.insertCell(0);
    col1.innerHTML = html;

//    col2 = row.insertCell(1);
//    col2.innerHTML = args.user_name;
//
//    col3 = row.insertCell(2);
//    col3.innerHTML = args.not_date;
//
//    col4 = row.insertCell(3);
//    col4.innerHTML = args.not_content;
//
//    col5 = row.insertCell(4);
//    col5.innerHTML = '<a href="javascript:deleteRow(\'' + args.not_id + '\');">Delete</a>';

};

deleteRow =  function (id) {
    try {
        var table = document.getElementById('tblNotes'),
            rowCount = table.rows.length,
            row,
            i;
        for (i = 0; i < rowCount; i++) {
            row = table.rows[i];
//            console.log(row.id);
            if (null != row.id && '' != row.id && id == row.id) {
                if (rowCount <= 1) {
                    break;
                }
                table.deleteRow(i);
                rowCount--;
                i--;
            }
        }
    } catch (e) {
        console.log(e);
    }
};