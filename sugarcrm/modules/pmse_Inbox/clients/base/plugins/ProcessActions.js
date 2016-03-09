/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {

        /**
         * ProcessActions plugin is for process records that requires to use panels
         * which contains history, status or notes within list and record views.
         *
         */
        app.plugins.register('ProcessActions', ['view', 'layout'], {
            /**
             * Show history for process by cas_id
             *
             * @param {integer} cas_id related to process.
             */
            getHistory: function(cas_id) {
                var logPanel, w2, label;
                var pmseHistoryUrl = app.api.buildURL('pmse_Inbox/historyLog', null, {id: cas_id});

                logPanel = new HistoryPanel({
                    logType: 'difList',
                    items: [],
                    callback: {
                        'loaded': function(data) {
                            var i, newLog, duration;

                            app.api.call('read', pmseHistoryUrl, {}, {
                                success: function(logs) {
                                    if (logs) {
                                        for (i = 0; i < logs.result.length; i += 1) {
                                            var log = logs.result[i];

                                            var end_date = log.end_date;
                                            var delegate_date = log.delegate_date;

                                            label = log.data_info;
                                            if (log.completed) {
                                                duration = '<strong>( ';
                                                if (end_date) {
                                                    duration += app.date(end_date).fromNow();
                                                } else {
                                                    duration += app.date(delegate_date).fromNow();
                                                }
                                                duration += ' )</strong>';
                                            } else {
                                                duration = '<strong>';
                                                duration += app.lang.get('LBL_PMSE_HISTORY_LOG_NO_YET_STARTED', 'pmse_Inbox');
                                                duration += '</strong>';
                                            }

                                            var pictureUrl = app.api.buildFileURL({
                                                module: 'Users',
                                                id: log.cas_user_id,
                                                field: 'picture'
                                            });

                                            newLog = {
                                                name: 'log' + i,
                                                label: label,
                                                user: log.user,
                                                startDate: app.date(delegate_date).formatUser(),
                                                picture: (log.script) ? log.image : pictureUrl,
                                                duration: duration,
                                                completed: log.completed,
                                                script: (log.script) ? log.script : false
                                            };

                                            logPanel.addLog(newLog);
                                        }
                                    }
                                    app.alert.dismiss('upload');
                                    w2.html.style.display = 'inline';
                                },
                                error: function(error) {
                                    app.alert.dismiss('upload');
                                    var message = (error && error.message) ? error.message : 'EXCEPTION_FATAL_ERROR';
                                    app.alert.show('error_history', {
                                        level: 'error',
                                        messages: message
                                    });
                                }
                            });
                        }
                    }
                });

                w2 = new Window({
                    width: 800,
                    height: 350,
                    modal: true,
                    title: '# ' + cas_id + ': ' + app.lang.get('LBL_PMSE_TITLE_HISTORY', 'pmse_Inbox')
                });

                w2.addPanel(logPanel);
                w2.show();
                w2.html.style.display = 'none';
                app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoClose: false});
            },

            /**
             * Shows current status of a process by cas_id.
             *
             * @param {integer} cas_id related to process.
             */
            showStatus: function(cas_id) {
                var url, w, hp, img, ih, iw, a;
                var id = cas_id;
                url = app.api.buildFileURL({
                    module: 'pmse_Inbox',
                    id: id,
                    field: 'id'
                }, {cleanCache: true});
                app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});

                img = new Image();
                img.src = url;
                img.onload = function() {
                    if (img.width < 760) {
                        ih = img.height;
                        iw = img.width;
                    } else {
                        ih = parseInt(img.height * (760 / img.width), 10);
                        iw = 760;
                    }
                    a = '<img width="' + iw + '" src="' + img.src + '" />';
                    hp = new HtmlPanel({
                        source: a,
                        scroll: ((ih + 45) > 400)
                    });

                    w = new Window({
                        width: iw + 40,
                        height: ((ih + 45) < 400) ? ih + 45 : 400,
                        modal: true,
                        title: app.lang.get('LBL_PMSE_TITLE_IMAGE_GENERATOR_OBJ', 'pmse_Inbox', {'id': id})
                    });
                    w.addPanel(hp);
                    w.show();
                    app.alert.dismiss('upload');
                };
            },
            /**
             * Show notes panel for determined process by cas_id
             *
             * @param {integer} caseId related to process.
             * @param {integer} caseIndex related to bpm process.
             */
            showNotes: function(caseId, caseIndex) {
                var w, np, notesTextArea, proxy, log, newLog, pictureUrl, i, url;
                url = app.api.buildURL('pmse_Inbox/note_list/' + caseId);

                notesTextArea = new TextareaField({
                    name: 'notesTextArea',
                    label: '',
                    fieldWidth: '80%'
                });

                app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
                np = new NotePanel({
                    items: [notesTextArea],
                    caseId: caseId,
                    caseIndex: caseIndex,
                    callback: {
                        'loaded': function(data) {
                            app.api.call('read', url, {}, {
                                success: function(notes) {
                                    for (i = 0; i < notes.rowList.length; i += 1) {
                                        log = notes.rowList[i];
                                        pictureUrl = app.api.buildFileURL({
                                            module: 'Users',
                                            id: log.not_user_id,
                                            field: 'picture'
                                        });

                                        var currentDate = Date.parse(notes.currentDate);
                                        var dateEntered = Date.parse(log.date_entered);
                                        if (isNaN(currentDate)) {
                                            currentDate = Date.parse(notes.currentDate.replace(/\s/g, 'T'));
                                        }
                                        if (isNaN(dateEntered)) {
                                            dateEntered = Date.parse(log.date_entered.replace(/\s/g, 'T'));
                                        }
                                        newLog = {
                                            name: 'log',
                                            label: log.not_content,
                                            user: log.last_name,
                                            picture: pictureUrl,
                                            duration: '<strong> ' + app.date(log.date_entered).fromNow() + ' </strong>',
                                            startDate: app.date(log.date_entered).formatUser(),
                                            logId: log.id
                                        };
                                        np.addLog(newLog);

                                    }
                                    app.alert.dismiss('upload');
                                },
                                error: function(error) {
                                    app.alert.dismiss('upload');
                                    var message = (error && error.message) ? error.message : 'EXCEPTION_FATAL_ERROR';
                                    app.alert.show('error_note', {
                                        level: 'error',
                                        messages: message
                                    });
                                }
                            });
                        }
                    }
                });
                w = new Window({
                    width: 800,
                    height: 380,
                    modal: true,
                    title: app.lang.get('LBL_PMSE_TITLE_PROCESS_NOTES', 'pmse_Inbox') + ' # ' + caseId
                });

                w.addPanel(np);
                w.show();
            },

            /**
             * Helper for select2 field of showForm
             *
             * @param {string} url
             * @param {id} flowId
             */
            getUserSearchURL: function (url, flowId) {
                return url + '/users/' + flowId + '?filter={%TERM%}&max_num={%PAGESIZE%}&offset={%OFFSET%}';
            },

            /**
             * Returns a new HiddenField object with the desired name and value.
             * @param name
             * @param value
             * @private
             */
            _getHiddenFieldObject: function (name, value) {
                return new HiddenField({name: name, value: value});
            },

            /**
             * Show form to reassign user or change assigned user
             *
             * @param {id} casId
             * @param {id} casIndex
             * @param {string} wtype
             * @param {id} flowId
             * @param {id} pmseInboxId
             * @param {string} taskName
             * @param {Object} [values]
             */
            showForm: function (casId, casIndex, wtype, flowId, pmseInboxId, taskName, values) {
                var f,
                    w,
                    combo_users,
                    items,
                    proxy,
                    textArea,
                    url,
                    wtitle,
                    wWidth,
                    wHeight,
                    casIdField,
                    casIndexField,
                    combo_type,
                    casFlowId,
                    casInboxId,
                    task_Name,
                    user_Name,
                    module_Name,
                    bean_Id,
                    full_Name,
                    valAux,
                    reassignForm;

                module_Name = this._getHiddenFieldObject('moduleName', values.moduleName);
                bean_Id = this._getHiddenFieldObject('beanId', values.beanId);
                if (values.name) {
                    valAux = values.name;
                } else {
                    valAux = values.full_name;
                }
                full_Name = this._getHiddenFieldObject('full_name', valAux);
                task_Name = this._getHiddenFieldObject('taskName', taskName);

                casIdField = this._getHiddenFieldObject('cas_id', casId);

                casIndexField = this._getHiddenFieldObject('cas_index', casIndex);
                casFlowId = this._getHiddenFieldObject('idFlow', flowId);

                casInboxId = this._getHiddenFieldObject('idInbox', pmseInboxId);
                combo_type = new ComboboxField({
                    name: 'adhoc_type',
                    label: app.lang.get('LBL_PMSE_FORM_LABEL_TYPE', 'pmse_Inbox'),
                    options: [
                        {text: 'Round Trip', value: 'ROUND_TRIP'},
                        {text: 'One Way', value: 'ONE_WAY'}
                    ],
                    initialValue: 'ROUND_TRIP',
                    required: true
                });

                textArea = new TextareaField({
                    name: 'adhoc_comment',
                    label: app.lang.get('LBL_PMSE_FORM_LABEL_NOTE', 'pmse_Inbox'),
                    fieldWidth: '300px',
                    fieldHeight: '100px'
                });
                user_Name = this._getHiddenFieldObject('user_name', '');

                reassignForm = this._getHiddenFieldObject('reassign_form', true);

                if (wtype === 'reassign') {
                    url = 'pmse_Inbox/AdhocReassign';
                    wtitle = app.lang.get('LBL_PMSE_TITLE_AD_HOC', 'pmse_Inbox');
                    wWidth = 550;
                    wHeight = 300;

                    combo_users = new SearchableCombobox({
                        label: app.lang.get('LBL_PMSE_FORM_LABEL_USER', 'pmse_Inbox'),
                        name: 'adhoc_user',
                        submit: true,
                        required: true,
                        searchMore: {
                            module: "Users",
                            fields: ["id"]
                        },
                        searchURL: this.getUserSearchURL(url, flowId),
                        searchValue: 'id',
                        searchLabel: 'full_name',
                        placeholder: app.lang.get('LBL_PA_FORM_COMBO_ASSIGN_TO_USER_HELP_TEXT', 'pmse_Project'),
                        helpTooltip: {
                            message: app.lang.get('LBL_PMSE_FORM_TOOLTIP_SELECT_USER', 'pmse_Inbox')
                        }
                    });

                    items = [
                        casIdField,
                        casIndexField,
                        casFlowId,
                        casInboxId,
                        combo_users,
                        combo_type,
                        textArea,
                        task_Name,
                        user_Name,
                        module_Name,
                        bean_Id,
                        full_Name,
                        reassignForm
                    ];
                    combo_users.setName('adhoc_user');
                    textArea.setName('not_content');
                } else {
                    // If wtype is set to user selection, change the tooltip msg
                    url = 'pmse_Inbox/ReassignForm';
                    wtitle = app.lang.get('LBL_PMSE_TITLE_REASSIGN', 'pmse_Inbox');
                    wWidth = 500;
                    wHeight = 250;

                    combo_users = new SearchableCombobox({
                        label: app.lang.get('LBL_PMSE_FORM_LABEL_USER', 'pmse_Inbox'),
                        name: 'adhoc_user',
                        submit: true,
                        required: true,
                        searchMore: {
                            module: "Users",
                            fields: ["id"]
                        },
                        searchURL: this.getUserSearchURL(url, flowId),
                        searchValue: 'id',
                        searchLabel: 'full_name',
                        placeholder: app.lang.get('LBL_PA_FORM_COMBO_ASSIGN_TO_USER_HELP_TEXT', 'pmse_Project'),
                        helpTooltip: {
                            message: app.lang.get('LBL_PMSE_FORM_TOOLTIP_CHANGE_USER', 'pmse_Inbox')
                        }
                    });

                    items = [
                        casIdField,
                        casIndexField,
                        casFlowId,
                        casInboxId,
                        combo_users,
                        textArea,
                        task_Name,
                        user_Name,
                        module_Name,
                        bean_Id,
                        full_Name
                    ];
                    combo_users.setName('reassign_user');
                    textArea.setName('reassign_comment');
                }
                flowId = (flowId) ? flowId : urlCase.id;
                proxy = new SugarProxy({
                    url: url,
                    uid: '',
                    callback: null
                });
                f = new Form({
                    items: items,
                    closeContainerOnSubmit: true,
                    buttons: [
                        {
                            jtype: 'normal',
                            caption: app.lang.get('LBL_PMSE_BUTTON_SAVE', 'pmse_Inbox'),
                            cssClasses: ['btn', 'btn-primary'],
                            handler: function () {
                                if (f.validate()) {
                                    app.alert.show('upload', {level: 'process', title: 'LBL_SAVING', autoClose: false});
                                    var cbDate = combo_users.getSelectedText();
                                    if (combo_users.name == 'reassign_user') {
                                        items[6].setValue(cbDate);
                                    } else {
                                        items[7].setValue(cbDate);
                                    }
                                    var urlIni = app.api.buildURL(url, null, null);
                                    attributes = {
                                        data: f.getData()
                                    };
                                    $(w.html).remove();
                                    app.api.call('update', urlIni, attributes, {
                                        success: function (response) {
                                            app.alert.show('pmse_reassign_success', {
                                                autoClose: true,
                                                level: 'success',
                                                messages: app.lang.get('LBL_PMSE_ALERT_REASSIGN_SUCCESS', 'pmse_Inbox')
                                            });
                                            if (wtype == 'reassign') {
                                                w.close();
                                                app.router.redirect('Home');
                                            } else if (wtype == 'adhoc') {
                                                if ($('#assigned_user_name').length) {
                                                    $("#assigned_user_name").val(cbDate);
                                                    w.close();
                                                } else {
                                                    w.close();
                                                    if (!app.router.refresh()) {
                                                        window.location.reload();
                                                    }
                                                }
                                            }
                                            app.alert.dismiss('upload');
                                        },
                                        error: function(error) {
                                            app.alert.dismiss('upload');
                                            var message = (error && error.message) ? error.message : 'EXCEPTION_FATAL_ERROR';
                                            app.alert.show('pmse_reassign_error', {
                                                level: 'error',
                                                messages: message
                                            });
                                        }
                                    });
                                }
                            }
                        },
                        {
                            jtype: 'normal',
                            caption: app.lang.get('LBL_PMSE_BUTTON_CANCEL', 'pmse_Inbox'),
                            cssClasses: ['btn btn-invisible btn-link'],
                            handler: function () {
                                w.close();
                            }
                        }
                    ],
                    labelWidth: 300,
                    callback: {
                        'loaded': function (data) {
                            casIdField.setValue(casId);
                            casIndexField.setValue(casIndex);
                            f.setProxy(proxy);
                        }
                    }
                });
                w = new Window({
                    width: wWidth,
                    height: wHeight,
                    modal: true,
                    title: wtitle
                });
                w.addPanel(f);
                w.show();
            }
        });
    });
})(SUGAR.App);
