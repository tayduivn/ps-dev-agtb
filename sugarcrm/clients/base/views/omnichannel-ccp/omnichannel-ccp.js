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
 * @class View.Views.Base.OmnichannelCcpView
 * @alias SUGAR.App.view.views.BaseOmnichannelCcpView
 * @extends View.View
 */
({
    className: 'omni-ccp',

    /**
     * A map of contact type to module
     */
    contactTypeModule: {
        voice: 'Calls',
        chat: 'Messages',
    },

    /**
     * The list of connected contacts
     */
    connectedContacts: {},

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
            // Load chat library here once required, must be loaded after connect-streams
            $.getScript('include/javascript/amazon-connect/amazon-connect-chat.js');
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
     * Gets the active contacts.
     * @return {contacts} Active contacts
     */
    getContacts: function() {
        return new connect.Agent().getContacts();
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
            contact.onConnecting(function(contact) {
                self._handleIncomingContact(contact);
            });

            contact.onConnected(function(contact) {
                self.styleFooterButton('active-session');
                self.addContactToContactsList(contact);
            });

            contact.onDestroy(function(contact) {
                if (_.isEmpty(self.getContacts())) {
                    // no more active contacts
                    self.styleFooterButton('logged-in');
                }
                self.removeContactFromContactsList(contact);
            });

            contact.onACW(function(contact) {
                // do nothing if contact was from a previous session
                if (!_.has(self.connectedContacts, contact.getContactId())) {
                    return;
                }

                var data;
                var type = contact.getType();
                var module = self.contactTypeModule[type];

                switch (type) {
                    case connect.ContactType.VOICE:
                        data = self.getVoiceContactInfo(contact);
                        break;
                    case connect.ContactType.CHAT:
                        data = self.getChatContactInfo(contact);
                        break;
                    default:
                        app.logger.error(`Amazon Connect: Contact type (type: ${type}) is not voice or chat`);
                        return;
                }

                data = _.extendOwn(data, self.getGenericContactInfo(contact));

                var model = self.getNewModelForContact(module, data);
                self.openCreateDrawer(module, model);
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
    _loadAdminConfig: function() {
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

    /**
     * Add the contact to the list of connected contacts
     *
     * @param contact
     */
    addContactToContactsList: function(contact) {
        this.connectedContacts[contact.getContactId()] = {
            connectedTimestamp: contact.getStatus().timestamp,
        };
    },

    /**
     * Remove the contact from the list of connected contacts, if it exists
     *
     * @param contact
     */
    removeContactFromContactsList: function(contact) {
        var contactId = contact.getContactId();

        if (_.has(this.connectedContacts, contactId)) {
            this.connectedContacts = _.omit(this.connectedContacts, contactId);
        }
    },

    /**
     * Get generic contact info that all contact types should have
     *
     * @param contact
     * @return {Object}
     */
    getGenericContactInfo: function(contact) {
        var data = {};

        try {
            data.isContactInbound = contact.isInbound();
        } catch (err) {
            app.logger.error(`Amazon Connect: Unable to determine contact inbound/outbound direction`);
        }

        data.contactType = contact.getType();
        data.startTime = this.getContactConnectedTime(contact);

        return data;
    },

    /**
     * Get the relevant information for a voice type contact
     *
     * @param contact
     * @return {Object}
     */
    getVoiceContactInfo: function(contact) {
        var conn = contact.getInitialConnection();
        var endpoint = conn.getEndpoint();

        return {
            phoneNumber: endpoint.phoneNumber,
        };
    },

    /**
     * Get the relevant information for a chat type contact
     *
     * @param contact
     * @return {Object}
     */
    getChatContactInfo: function(contact) {
        return {
            name: app.lang.get('LBL_OMNICHANNEL_DEFAULT_CUSTOMER_NAME'),
        };
    },

    /**
     * Get the Utils/Date from the contact's timestamp
     *
     * @param contact
     * @return {Date}
     */
    getContactConnectedTime: function(contact) {
        var timestamp = this.connectedContacts[contact.getContactId()].connectedTimestamp;

        return app.date(timestamp);
    },

    /**
     * Get a readable title per the contact type
     *
     * @param module
     * @param data
     * @return {string}
     */
    getRecordTitle: function(module, data) {
        var contactType = data.contactType;
        var title = '';

        // if unfamiliar type, return empty
        if (!(contactType === connect.ContactType.VOICE || contactType === connect.ContactType.CHAT)) {
            return title;
        }

        var contactTypeStr = (contactType === connect.ContactType.VOICE) ? 'Call' : 'Chat';
        var identifier = (contactType === connect.ContactType.VOICE) ? data.phoneNumber : data.name;
        var direction = _.has(data, 'isContactInbound') ? (data.isContactInbound ? 'from' : 'to') : 'from';

        title = app.lang.get('TPL_OMNICHANNEL_NEW_RECORD_TITLE',
            module,
            {
                type: contactTypeStr,
                direction: direction,
                identifier: identifier,
                time: data.startTime.formatUser(),
            }
        );

        return title;
    },

    /**
     * Get the time in server format and calculate the duration
     *
     * @param data
     * @return {Object}
     */
    getTimeAndDuration: function(data) {
        var startTime = data.startTime;
        var nowTime = app.date();

        var timeDiff = nowTime.diff(startTime);
        var durationHours = Math.floor(app.date.duration(timeDiff).asHours());
        var durationMinutes = app.date.duration(timeDiff).minutes();

        return {
            startTime: startTime.formatServer(),
            nowTime: nowTime.formatServer(),
            durationHours: durationHours,
            durationMinutes: durationMinutes,
        };
    },

    /**
     * Create the model and set appropriate attributes for the contact
     *
     * @param module
     * @param data
     * @return {Object} the model
     */
    getNewModelForContact: function(module, data) {
        var model = app.data.createBean(module);

        var timeData = this.getTimeAndDuration(data);

        if (_.has(data, 'isContactInbound')) {
            model.set({
                direction: data.isContactInbound ? 'Inbound' : 'Outbound',
            });
        }

        if (data.contactType === connect.ContactType.VOICE) {
            model.set({
                duration_hours: timeData.durationHours,
                duration_minutes: timeData.durationMinutes,
                status: 'Held',
            });
        } else if (data.contactType === connect.ContactType.CHAT) {
            model.set({
                channel_type: 'Chat',
            });
        }

        model.set({
            name: this.getRecordTitle(module, data),
            date_start: timeData.startTime,
            date_end: timeData.nowTime,
        });

        return model;
    },

    /**
     * Open the create drawer with create-no-cancel-button layout
     *
     * @param module
     * @param model
     */
    openCreateDrawer: function(module, model) {
        app.drawer.open({
            layout: 'create-no-cancel-button',
            context: {
                create: true,
                module: module,
                model: model,
            },
        }, _.bind(function() {
            this.layout.open();
        }, this));
    },
})
