({
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
            return this.skypeFormat(value)
        } else {
            return value;
        }
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
     * @param value {String}
     * @returns {String}
     */
    skypeFormat: function (value) {
        if (_.isString(value)) {
            var number = value.replace(/[^\+0-9]/g, '');
            if (value.substr(0, 1) !== '+'){
                number = '+'+number;
            }
            return number;
        } else {
            return value;
        }
    }
})
