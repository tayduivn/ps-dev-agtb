/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
 
var CAL = {};

CAL.fillRepeatForm = function(data) {
    if(typeof data.repeat_parent_id != "undefined"){
        $("#cal-repeat-block").css('display', "none");
        $("#edit_all_recurrences_block").css('display', "");
        $("#edit_all_recurrences").val("");
        $("#repeat_parent_id").val(data.repeat_parent_id);
        return;
    }        
        
    $("#cal-repeat-block").css('display', "");
        
    var repeatType = "";
    var setDefaultRepeatUntil = true;
    if (typeof data.repeat_type != "undefined") {
        repeatType = data.repeat_type;
        
        document.forms['CalendarRepeatForm'].repeat_type.value = data.repeat_type;
        document.forms['CalendarRepeatForm'].repeat_interval.value = data.repeat_interval;
        if (data.repeat_count != '' && data.repeat_count != 0) {
            document.forms['CalendarRepeatForm'].repeat_count.value = data.repeat_count;
            $("#repeat_count_radio").prop('checked', true);
            $("#repeat_until_radio").prop('checked', false);
        } else {
            document.forms['CalendarRepeatForm'].repeat_until.value = data.repeat_until;
            $("#repeat_until_radio").prop('checked', true);
            $("#repeat_count_radio").prop('checked', false);
            setDefaultRepeatUntil = false;
        }
        if (data.repeat_type == "Weekly") {
            var arr = data.repeat_dow.split("");
            $.each(arr, function(i,d){
                $("#repeat_dow_" + d).prop('checked', true);
            });
        }
        
        $("#cal-repeat-block").css('display', "");
        $("#edit_all_recurrences_block").css('display', "none");
        toggle_repeat_type();
    }
    
    $("#edit_all_recurrences").val("true");
    
    if(typeof data.current_dow != "undefined" && repeatType != "Weekly") {
        $("#repeat_dow_" + data.current_dow).prop('checked', true);
    }
    
    if(typeof data.default_repeat_until != "undefined" && setDefaultRepeatUntil) {
        $("#repeat_until_input").val(data.default_repeat_until);
    }
}

CAL.editAllRecurrences = function() {
    disableOnUnloadEditView();
    document.forms['EditView'].elements['action'].value = 'editAllRecurrences';
    document.forms['EditView'].submit();
}

CAL.removeAllRecurrences = function() {
    if (confirm(SUGAR.language.get(document.forms['EditView'].elements['module'].value, 'LBL_CONFIRM_REMOVE_ALL_RECURRENCES'))) {
        disableOnUnloadEditView();
        document.forms['EditView'].elements['action'].value = 'removeAllRecurrences';
        document.forms['EditView'].submit();
    }
}

CAL.fillRepeatData = function() {
    document.forms['EditView'].repeat_type.value = '';
    if (repeatType = document.forms['CalendarRepeatForm'].repeat_type.value) {
        document.forms['EditView'].repeat_type.value = repeatType;
        document.forms['EditView'].repeat_interval.value = document.forms['CalendarRepeatForm'].repeat_interval.value;
        if (document.getElementById("repeat_count_radio").checked) {
            document.forms['EditView'].repeat_count.value = document.forms['CalendarRepeatForm'].repeat_count.value;
            document.forms['EditView'].repeat_until.value = "";
        } else {
            document.forms['EditView'].repeat_until.value = document.forms['CalendarRepeatForm'].repeat_until.value;
            document.forms['EditView'].repeat_count.value = "";
        }
        if (repeatType == 'Weekly') {
            var repeatDow = "";
            for (var i = 0; i < 7; i++) {
                if ($("#repeat_dow_" + i).prop('checked')) {
                    repeatDow += i.toString();
                }
            }
            $("#repeat_dow").val(repeatDow);
        }
    }
}

CAL.checkRecurrenceForm = function() {
    lastSubmitTime = lastSubmitTime - 2001;
    return check_form('CalendarRepeatForm');
}
