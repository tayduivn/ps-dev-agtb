
$('#groups-form').attr("novalidate", "novalidate")
    .on('submit', function (e) {
        var restClient,
            result = false,
            action = $('#action').val(),
            valid = validateForm('groups-form'),
            mp = new MessagePanel({
                title: 'Warning',
                wtype: 'Warning'
            });

        if (valid.valid) {
//            $(this).submit();
            restClient = new RestClient ();
            restClient.setRestfulBehavior(SUGAR_REST);
            if (!SUGAR_REST) {
                restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
            }
            if (action === 'new') {
                restClient.postCall({
                    url: SUGAR_URL + '/rest/v10/Groups',
                    id: '',
                    data: {'grp_name': $('#grp_name').val(), 'grp_description': $('#grp_description').val()},
                    success: function (xhr, response) {
//                        console.log(response);
                        if (response.success) {
                            location.href = './index.php?action=groups&module=ProcessMaker';
                        }
                    },
                    failure: function (xhr, response) {
                        //TODO Process HERE error at loading project
                    }
                });
            }
            if (action === 'edit') {
                restClient.putCall({
                    url: SUGAR_URL + '/rest/v10/Groups',
                    id: $('#grp_id').val(),
                    data: {'grp_name': $('#grp_name').val(), 'grp_description': $('#grp_description').val()},
                    success: function (xhr, response) {
//                        console.log(response);
                        if (response.success) {
                            location.href = './index.php?action=groups&module=ProcessMaker';
                        }
                    },
                    failure: function (xhr, response) {
                        //TODO Process HERE error at loading project
                    }
                });
            }

        } else {
            e.preventDefault();
            mp.setTitle('Warning');
            mp.setMessageType('Warning');
            mp.setMessage(valid.message);
            mp.show();
            $('#btnSubmit').removeAttr('disabled');
        }
        return false;
    });