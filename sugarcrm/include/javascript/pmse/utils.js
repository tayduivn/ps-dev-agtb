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
/*global SUGAR, canvas, Document*/

var translate = function (label, module, replace) {
    //var string = (SUGAR.language.languages.ProcessMaker[label]) ? SUGAR.language.languages.ProcessMaker[label] : label;
    var string, language, arr;
    if (!module){
        if (!window.CURRENT_MODULE) {
            module = 'pmse_Project';
        } else {
            module = window.CURRENT_MODULE;
        }
    }
    if (App) {
        string = (App.lang.get(module)[label]) ? App.lang.get(module)[label] : label;
    } else {
        language = SUGAR.language.languages;
        arr = language[module];
        string = (arr[label]) ? arr[label] : label;
    }
    if (!replace) {
        return string;
    } else {
        return string.toString().replace(/\%s/, replace);
    }
};


var evvv = $([]);
function validateForm(formID) {
    var form = $(document.getElementById(formID)),
        valid = true,
        req = [],
        msg = '<div>',
        v;
//    ev = $([]).add($('#name')).add($('#pro_module'));
//    console.log(ev);
    evvv.removeClass('required');
    form.find(':input').each(function () {
        if (this.required) {
            req.push(this);
            v = this.value.trim();
            if (v === null || v === false ||  v === "" ) {
                $(this).addClass('required');
                //console.log(this)
                msg += this.title + '<br>';
                valid = false;
            }
//            console.log($(this));
        }
    });
    msg += '</div>';
    evvv = $(req);
    return {
        valid: valid,
        message: valid ? '' : translate('LBL_PMSE_LABEL_ERROR_FIELDS_TO_FILL') + msg
    };
}


/**
 * Get diference between two dates and convert to literal format (Ej. 1 hour ago, 4 days ago)
 * if ago options is enabled in otherwise no chain with the ago word
 * @param {Date} ptime2
 * @param {Date} ptime1
 * @param {Boolean} ago
 * @return {String}
 */

var timeElapsedString = function (ptime2, ptime1, ago) {

    var etime = (ptime2 - ptime1)/1000,
        r,
        d,
        referTime,
        aux;
    if (etime < 1) {
        return '0 seconds';
    }

    referTime = {
        'year': 31104000,
        'month': 2592000,
        'day': 86400,
        'hour': 3600,
        'minute': 60,
        'second': 1
    };
    $.each(referTime, function (text, factor) {
        d = etime / factor;
        if (d >= 1) {
            r = Math.round(d);
            aux = text;
            return false;
        }
    });
    if (ago) {
        return r +' '+ aux +(r > 1 ? 's' : '')+ ' ago';
    } else {
        return r +' '+ aux +(r > 1 ? 's' : '');
    }

};
