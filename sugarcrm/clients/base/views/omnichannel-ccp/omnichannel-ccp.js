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
     * The active contact
     */
    activeContact: null,

    /**
     * The list of connected contacts
     */
    connectedContacts: {},

    /**
     * Chat controllers, keyed by contact ID
     */
    chatControllers: {},

    /**
     * Transcripts of chat messages, keyed by contact ID
     */
    chatTranscripts: {},

    /**
     * Is the ccp loaded?
     */
    ccpLoaded: false,

    /**
     * Have we loaded the CCP library?
     */
    libraryLoaded: false,

    /**
     * Is agent logged in?
     */
    agentLoggedIn: false,

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
                // Load chat library here, must be loaded after connect-streams
                $.getScript('include/javascript/amazon-connect/amazon-connect-chat.js', function() {
                    self.libraryLoaded = true;
                    self.initializeCCP();
                    self.initializeChat();
                });
            });

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
        } else if (!this.agentLoggedIn) {
            if (connect.core.loginWindow == null || connect.core.loginWindow.closed) {
                connect.core.loginWindow = window.open(this.defaultCCPOptions.ccpUrl, connect.MasterTopics.LOGIN_POPUP);
            } else {
                connect.core.loginWindow.focus();
            }
        }
    },

    /**
     * Provide initial chat config for use with amazon-connect-chatjs library
     */
    initializeChat: function() {
        var globalConfig = {
            region: this.defaultCCPOptions.region
        };
        connect.ChatSession.setGlobalConfig(globalConfig);
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
        this.agentLoggedIn = false;
    },

    /**
     * Load agent event listeners.
     */
    loadAgentEventListeners: function() {
        var self = this;
        connect.agent(function(agent) {
            // When CCP agent is authenticated, we set the footer style
            self.styleFooterButton('logged-in');
            self.agentLoggedIn = true;
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
     * Get the contact id for the active contact
     *
     * @return {string} the contact id or empty string if no active contact
     */
    getActiveContactId: function() {
        if (this.activeContact) {
            return this.activeContact.getContactId();
        }
        return '';
    },

    /**
     * Load contact event listeners.
     */
    loadContactEventListeners: function() {
        var self = this;

        connect.core.onViewContact(function(event) {
            if (self.connectedContacts[event.contactId]) {
                self._setActiveContact(event.contactId);
            }
        });

        connect.contact(function(contact) {

            var connection = contact.getAgentConnection();
            if (connection.getMediaType() === connect.MediaType.CHAT) {
                self.loadChatListeners(connection);
            }

            contact.onConnecting(function(contact) {
                self.layout.open();
            });

            contact.onConnected(function(contact) {
                self.styleFooterButton('active-session');
                self.addContactToContactsList(contact);
                self._setActiveContact(contact.contactId);
                if (contact.isInbound()) {
                    self._handleIncomingContact(contact);
                }
            });

            contact.onDestroy(function(contact) {
                if (_.isEmpty(self.getContacts())) {
                    // no more active contacts
                    self.styleFooterButton('logged-in');

                    // empty the active contact
                    self._unsetActiveContact();
                }
                self.removeStoredContactData(contact);
            });

            contact.onACW(function(contact) {
                // do nothing if contact type is unfamiliar
                if (!_.has(self.contactTypeModule, contact.getType())) {
                    app.logger.error('Amazon Connect: Contact type (type: ${type}) is not voice or chat');
                    return;
                }

                // do nothing if contact was from a previous session
                if (!_.has(self.connectedContacts, contact.getContactId())) {
                    return;
                }

                var data = _.extendOwn(
                    self.getContactInfo(contact),
                    self.getGenericContactInfo(contact)
                );
                var module = self.contactTypeModule[contact.getType()];
                var model = self.getNewModelForContact(module, data);

                self.openCreateDrawer(module, model);
            });
        });
    },

    /**
     * Get relevant contact information based on contact type
     *
     * @param contact
     * @return {Object}
     */
    getContactInfo: function(contact) {
        var data;
        var type = contact.getType();

        switch (type) {
            case connect.ContactType.VOICE:
                data = this.getVoiceContactInfo(contact);
                break;
            case connect.ContactType.CHAT:
                data = this.getChatContactInfo(contact);
                break;
        }

        return data;
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
            if (self.agentLoggedIn) {
                self._showConnectionWarning();
            }
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
     * Send an 'incoming' event to the console.
     *
     * @param {Object} contact Contact making call/chat
     * @private
     */
    _handleIncomingContact: function(contact) {
        var type = contact.getType();
        this.layout.trigger(type + ':incoming', contact);
    },

    /**
     * Caches the last viewed contact
     *
     * @param {string} id
     * @private
     */
    _setActiveContact: function(id) {
        this.activeContact = _.findWhere(this.getContacts(), {contactId: id});
        this.layout.trigger('contact:view', id);
    },

    /**
     * Unset the active contact and other relevant data
     *
     * @private
     */
    _unsetActiveContact: function() {
        this.activeContact = null;
        this.context.unset('quickcreateModelData');
        this.context.unset('quickcreateCreatedModel');
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
    removeStoredContactData: function(contact) {
        var contactId = contact.getContactId();

        if (_.has(this.connectedContacts, contactId)) {
            this.connectedContacts = _.omit(this.connectedContacts, contactId);
        }

        if (_.has(this.chatControllers, contactId)) {
            this.chatControllers = _.omit(this.chatControllers, contactId);
        }

        if (_.has(this.chatTranscripts, contactId)) {
            this.chatTranscripts = _.omit(this.chatTranscripts, contactId);
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
            app.logger.error('Amazon Connect: Unable to determine contact inbound/outbound direction');
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
            phone_work: endpoint.phoneNumber,
        };
    },

    /**
     * Get the relevant information for a chat type contact
     *
     * @param contact
     * @return {Object}
     */
    getChatContactInfo: function(contact) {
        var lastName = '';
        var data = contact._getData();

        var connectionInfo = _.findWhere(data.connections, {type: 'inbound'});
        if (connectionInfo) {
            lastName = connectionInfo.chatMediaInfo.customerName;
        }

        var chatTranscript = this._getTranscriptForContact(contact);

        return {
            last_name: lastName,
            name: (lastName) ? lastName : app.lang.get('LBL_OMNICHANNEL_DEFAULT_CUSTOMER_NAME'),
            conversation: chatTranscript
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
        var identifier = (contactType === connect.ContactType.VOICE) ? data.phone_work : data.name;
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
                conversation: data.conversation
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
            var drawers = app.drawer._getDrawers(true);
            // open the console if there is no open drawers
            if (!drawers.$top) {
                this.layout.open();
            }
        }, this));
    },

    /**
     * Load event listeners specific to chat sessions
     *
     * @param {Object} connection - connect-streams Connection object
     */
    loadChatListeners: function(connection) {
        var controllerHandler = _.bind(this._handleChatMediaController, this);
        connection.getMediaController().then(controllerHandler);
    },

    /**
     * Bind any event listeners onto chat media controllers.
     *
     * @param {Object} controller - ChatSessionController from connect-streams-chatjs
     * @private
     */
    _handleChatMediaController: function(controller) {
        var contactId = controller.controller.contactId;
        this.chatControllers[contactId] = controller;
        controller.onMessage(_.bind(this._handleChatMessage, this));
    },

    /**
     * ChatSessionController.onMessage event handler. Receives the API response
     * object from when messages are sent/received. Overwrites the existing chat
     * transcript for this contact with the most up-to-date version so whenever
     * the chat is ended we can save the transcript.
     *
     * @param {Object} response - connect-streams-chatjs API response
     * @private
     */
    _handleChatMessage: function(response) {
        var controller = this.chatControllers[response.chatDetails.contactId];
        controller.getTranscript({})
        .then(_.bind(this._setChatTranscript, this))
        .catch(function(error) {
            console.log(error);
        });
        if (response.data &&
            response.data.Type === 'MESSAGE' &&
            response.data.ParticipantRole === 'CUSTOMER') {
            this.layout.trigger('omnichannel:message');
        }
    },

    /**
     * Sets a chat transcript to this object's context for reference when the
     * chat session ends
     *
     * @param {Object} transcript - connect-streams-chatjs Transcript object
     * @private
     */
    _setChatTranscript: function(transcript) {
        var currentTranscript = this.chatTranscripts[transcript.data.InitialContactId];
        this.chatTranscripts[transcript.data.InitialContactId] = _.uniq(_.union(
            currentTranscript, transcript.data.Transcript
        ), function(message) {
            return message.Id;
        });
    },

    /**
     * Get a human-readable chat transcript for this contact. This function is
     * called when chat sessions end, and the return value is set on the model
     * when the Messages create drawer opens.
     *
     * @param {Object} contact - connect-streams Contact object
     * @return {string} readableTranscript - human readable chat transcript
     * @private
     */
    _getTranscriptForContact: function(contact) {
        var readableTranscript = '';

        if (!_.isUndefined(this.chatTranscripts[contact.contactId])) {
            var transcriptJson = this.chatTranscripts[contact.contactId];
            _.each(transcriptJson, function(message) {
                readableTranscript += this._formatChatMessage(message);
            }, this);
        }
        return readableTranscript.trim();
    },

    /**
     * Convert a single chat message from JSON to a human-readable format
     *
     * @param {Object} message - JSON-format chat message
     * @return {string} readableMessage - single human-readable chat message
     * @private
     */
    _formatChatMessage: function(message) {
        if (_.isEmpty(message.Content)) {
            return '';
        }
        var offset = app.user.getPreference('tz_offset_sec');
        var dateTime = app.date(message.AbsoluteTime).utcOffset(offset / 60);
        var timeStamp = dateTime.format(app.date.getUserTimeFormat());
        var header = '[' + message.ParticipantRole + ' ' + message.DisplayName + ']';
        header += ' ' + timeStamp;
        return header + '\n' + message.Content + '\n\n';
    },
})
