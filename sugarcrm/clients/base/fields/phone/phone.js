/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    plugins: ['EllipsisInline'],
    /**
     * @override
     * @param options
     */
    initialize: function (options) {
        var serverInfo = app.metadata.getServerInfo();

        this.skypeEnabled = serverInfo.system_skypeout_on ? true : false;

        app.view.Field.prototype.initialize.call(this, options);
    },
    /**
     * @override
     * @param value
     * @returns {*}
     */
    format: function (value) {
        if ((this.action === 'list' || this.action === 'detail' || this.action === 'record')
            && this.isSkypeFormatted(value)
            && this.skypeEnabled) {
            this.skypeValue = this.skypeFormat(value);
        }
        return value;
    },
    /**
     * checks if value should be skype formatted + 00 or 011 leading is necessary
     * @param value {String}
     * @returns {boolean}
     */
    isSkypeFormatted: function (value) {
        if (_.isString(value)) {
            return value.substr(0, 1) === '+' || value.substr(0, 2) === '00' || value.substr(0, 3) === '011';
        } else {
            return false;
        }
    },
    /**
     * strips extra characters from phone number for skype
     *
     * Document: https://support.skype.com/en/faq/FA12006/how-do-i-script-webpages-to-find-phone-numbers-using-click-to-call
     *
     * @param value {String}
     * @returns {String}
     */
    skypeFormat: function (value) {
        if (_.isString(value)) {
            var number = value.replace(/[^\d\(\)\.\-\/ ]/g, '');

            if(null !== number.match(/[\-]/g) && number.match(/[\-]/g).length >= 2) {
                // ensure format is "+CC-NDC-SN"
                number = number.replace(/[^\d\-]/g, '')
                    .replace(/(\d+)\-(\d+)\-([\d\-]+)/g, function($0, $1, $2, $3) {
                        return [$1, $2, $3.replace(/\D/g, '')].join('-');
                    });
            } else if(null !== number.match(/[\.]/g) && number.match(/[\.]/g).length >= 2) {
                // ensure format is "+CC.NDC.SN"
                number = number.replace(/[^\d\.]/g, '')
                    .replace(/(\d+)\.(\d+)\.([\d\.]+)/g, function($0, $1, $2, $3) {
                        return [$1, $2, $3.replace(/\D/g, '')].join('.');
                    });
            } else if(null !== number.match(/\(\D*\d+\D*\)/g)) {
                // ensure format is "+CC(NDC)SN"
                number = number.replace(/[^\d\(\)]+/g, '')
                    .replace(/(\d+)\((\d+)\)([0-9\(\)]+)/g, function($0, $1, $2, $3) {
                        return $1 + '(' + $2 + ')' + $3.replace(/\D/g, '');
                    })
            } else if(null !== number.match(/[\/]/g) && number.match(/[\/]/g).length >= 2) {
                // ensure format is "+CC/NDC/SN"
                number = number.replace(/[^\d\/]/g, '')
                    .replace(/(\d+)\/(\d+)\/([\d\/]+)/g, function($0, $1, $2, $3) {
                        return [$1, $2, $3.replace(/\D/g, '')].join('/');
                    });
            } else if(null !== number.match(/\S+\s+\S+\s+[\S\s]+/g)) {
                // ensure format is "+CC NDC SN"
                number = number.replace(/(\S+)\s+(\S+)\s+([\S\s]+)/g, function($0, $1, $2, $3) {
                    return _.map([$1, $2, $3], function(s) {
                        return s.replace(/\D/g, '');
                    }).join(' ');
                })
            } else {
                number = number.replace(/\D/g, '');
            }
            if(value.substr(0, 1) === '+' || (number.substr(0, 2) !== '00' && number.substr(0, 3) !== '011')) {
                number = '+' + number;
            }
            return number;

        } else if (_.isNumber(value)) {
            if(value.substr(0, 2) !== '00' && value.substr(0, 3) !== '011') {
                value = '+' + value;
            }
        }
        return value;
    }
})