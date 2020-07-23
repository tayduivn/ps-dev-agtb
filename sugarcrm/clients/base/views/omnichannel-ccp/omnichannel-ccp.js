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
/**
 * The ccp container of the Omnichannel console.
 *
 * @class View.Layouts.Base.OmnichannelCcpView
 * @alias SUGAR.App.view.layouts.BaseOmnichannelCcpView
 * @extends View.View
 */
({
    className: 'omni-ccp',

    /**
     * Is the ccp loaded?
     */
    ccpLoaded: false,

    /**
     * Have we loaded the CCP library?
     */
    libraryLoaded: false,

    /**
     * Default CCP settings. Will be overridden by admin settings in the future
     */
    defaultCCPOptions: {
        loginPopupAutoClose: true,
        softphone: {
            allowFramedSoftphone: true
        }
    },

    /**
     * Prefix for AWS connect instance URLs
     */
    urlPrefix: 'https://',

    /**
     * Suffix for AWS connect instance URLs
     */
    urlSuffix: '.awsapps.com/connect/ccp-v2/',

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        // Load the CCP when console drawer opens
        this.layout.on('omniconsole:open', this.loadCCP, this);
    },

    /**
     * Load the CCP library if needed, then initialize the CCP. Show an alert
     * message if loading the CCP fails. We expect it to fail in IE and Safari,
     * as the CCP itself is not compatible with those browsers.
     */
    loadCCP: function() {
        if (!this._loadAdminConfig()) {
            this._showNonConfiguredWarning();
            return;
        }
        if (this.libraryLoaded) {
            this.initializeCCP();
            return;
        }
        try {
            var self = this;
            // Load the connect-streams library and initialize the CCP
            $.getScript('include/javascript/amazon-connect/amazon-connect-1.4.9-1-gf9242a0.js', function() {
                self.libraryLoaded = true;
                self.initializeCCP();
            });
            // Load chat library here once required
        } catch (error) {
            app.alert.show(error.name, {
                level: 'error',
                messages: 'ERROR_OMNICHANNEL_LOAD_FAILED'
            });
            App.logger.error('Loading connect-streams library failed: ' + error);
        }
    },

    /**
     * Initialize library with options defined above, and load event listeners
     * for different CCP objects.
     */
    initializeCCP: function() {
        if (!this.ccpLoaded) {
            connect.core.initCCP(_.first(this.$('#containerDiv')), this.defaultCCPOptions);
            this.loadAgentEventListeners();
            this.loadContactEventListeners();
            this.loadGeneralEventListeners();
            this.ccpLoaded = true;
        }
    },

    /**
     * Tear down the CCP instance when an agent logs out. We have to terminate
     * the instance via the Amazon library, and completely remove the iFrame from
     * the DOM so we can load a new one when the drawer is re-opened.
     */
    tearDownCCP: function() {
        this.styleFooterButton('logged-out');
        connect.core.terminate();
        this.layout.close();
        this.$el.find('#containerDiv').empty();
        this.ccpLoaded = false;
    },

    /**
     * Load agent event listeners.
     */
    loadAgentEventListeners: function() {
        var self = this;
        connect.agent(function(agent) {
            // When CCP agent is authenticated, we set the footer style
            self.styleFooterButton('logged-in');
        });
    },

    /**
     * Load contact event listeners.
     */
    loadContactEventListeners: function() {
        var self = this;
        connect.core.onViewContact(function(event) {
            self.layout.trigger('contact:view', event.contactId);
        });
        connect.contact(function(contact) {
            // Attach contact event listeners here
            contact.onConnecting(function(contact) {
                self._handleIncomingContact(contact);
            });
        });
    },

    /**
     * Load general event listeners. If the connect-streams API exposes an
     * object.onEvent function, we should prefer that method of event listening.
     * The EventBus should only be used for low-level events that aren't exposed
     * via the agent, contact, etc. object APIs.
     */
    loadGeneralEventListeners: function() {
        var self = this;
        var eventBus = connect.core.getEventBus();
        // This event is fired when an agent logs out, or the connection is lost
        eventBus.subscribe(connect.EventType.TERMINATED, function() {
            self.tearDownCCP();
            self.layout.trigger('ccp:terminated');
        });
        // This event is fired if we cannot synchronize with the CCP server
        eventBus.subscribe(connect.EventType.ACK_TIMEOUT, function() {
            self._showConnectionWarning();
        });
        // This event is triggered when 'Clear Contact' button is clicked
        eventBus.subscribe(connect.ContactEvents.DESTROYED, function(event) {
            self.layout.trigger('contact:destroyed', event.contactId);
        });
    },

    /**
     * Util to trigger the footer style update
     *
     * @param status
     */
    styleFooterButton: function(status) {
        this.layout.context.trigger('omnichannel:auth', status);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.layout.off('omniconsole:open', null, this);
        this._super('_dispose');
    },

    /**
     * Warn users if their admin hasn't added Amazon Connect settings
     * @private
     */
    _showNonConfiguredWarning: function() {
        app.alert.show('omnichannel-not-configured', {
            level: 'warning',
            messages: 'ERROR_OMNICHANNEL_NOT_CONFIGURED'
        });
    },

    /**
     * Warn users if the attempt to contact their Connect instance timed out
     * @private
     */
    _showConnectionWarning: function() {
        app.alert.show('omnichannel-timeout', {
            level: 'warning',
            messages: 'ERROR_OMNICHANNEL_TIMEOUT'
        });
    },

    /**
     * Load admin configuration for AWS Connect. Return true if successful, else
     * false.
     *
     * @return {boolean} whether or not config was loaded
     * @private
     */
    _loadAdminConfig() {
        var instanceName = App.config.awsConnectInstanceName;
        var region = App.config.awsConnectRegion;
        if (_.isEmpty(instanceName) || _.isEmpty(region)) {
            return false;
        }

        this.defaultCCPOptions.ccpUrl = this.urlPrefix + instanceName + this.urlSuffix;
        this.defaultCCPOptions.region = region;
        return true;
    },

    /**
     * Open the Omnichannel drawer if it is closed to allow agent to accept
     * incoming call/chat.
     *
     * @param {Object} contact Contact making call/chat
     * @private
     */
    _handleIncomingContact: function(contact) {
        this.layout.open();
    },
})
