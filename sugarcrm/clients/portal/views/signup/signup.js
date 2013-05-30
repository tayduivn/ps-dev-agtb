/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (''License'') which can be viewed at
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
 *  (i) the ''Powered by SugarCRM'' logo and
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
    plugins: ['error-decoration'],

    /**
     * Sign Up form view.
     * @class View.Views.SignupView
     * @alias SUGAR.App.view.views.SignupView
     */
    events: {
        'click [name=cancel_button]': 'cancel',
        'click [name=signup_button]': 'signup',
        'change select[name=country]': 'render'
    },

    /**
     * Get the fields metadata from panels and declare a Bean with the metadata attached
     * @param meta
     * @private
     * @see View.Views.LoginView
     */
    _declareModel: function(meta) {
        meta = meta || {};

        var fields = {};
        _.each(_.flatten(_.pluck(meta.panels, "fields")), function(field) {
            fields[field.name] = field;
        });
        /**
         * Fields metadata needs to be converted to this format for App.data.declareModel
         *  {
          *     "first_name": { "name": "first_name", ... },
          *     "last_name": { "name": "last_name", ... },
          *      ...
          * }
         */
        app.data.declareModel('Signup', {fields: fields});
    },

    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        // Declare a Bean so we can process field validation
        this._declareModel(options.meta);

        // Reprepare the context because it was initially prepared without metadata
        options.context.prepare(true);

        // Manually injects app_list_strings
        app.metadata.set(this._metadata);

        app.view.View.prototype.initialize.call(this, options);
        this._showSignupSuccess = false;
        this.model.set({
            'email': [
                {
                    'email_address': '',
                    'primary_address': '1'
                }
            ]
        }, {silent: true});
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        if (app.config && app.config.logoURL) {
            this.logoURL = app.config.logoURL;
        }
        app.view.View.prototype._render.call(this);

        this.stateField = this.$('select[name=state]');
        this.countryField = this.$('select[name=country]');
        this.toggleStateField();
        return this;
    },

    /**
     * For USA country only we need to display the State dropdown
     */
    toggleStateField: function() {
        if (this.countryField.val() == 'USA') {
            this.stateField.parent().show();
        } else {
            this.stateField.parent().hide();
            this.context.get('model').set('state', undefined);
        }
    },

    /**
     * Basic cancel button
     */
    cancel: function() {
        app.router.goBack();
    },

    /**
     * Prepares Signup API request payload based on form's model and language preferences
     * @returns {{first_name: *, last_name: *, email: *, phone_work: *, primary_address_country: *, title: *, account_name: *}}
     * @private
     */
    _prepareRequestPayload: function() {
        var data = {
            first_name: this.model.get('first_name'),
            last_name: this.model.get('last_name'),
            email: this.model.get('email'),
            phone_work: this.model.get('phone_work'),
            primary_address_country: this.model.get('country'),
            title: this.model.get('title'),
            account_name: this.model.get('company')
        };
        if(data.primary_address_country === "USA"){
            data.primary_address_state = this.model.get('state');
        }
        // Sets the preferred language based on the current loaded language. Can be undefined.
        var language = app.lang.getLanguage();
        if (language) {
            data.preferred_language = language;
        }
        return data;
    },

    /**
     * Handles Sign Up
     */
    signup: function() {
        var self = this;

        self.clearValidationErrors();
        self.model.doValidate(null, function(isValid) {
            if (isValid) {
                app.$contentEl.hide();
                app.alert.show('signup', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SIGNUP_PROCESS'), autoClose: false});

                var payload = self._prepareRequestPayload();
                app.api.signup(payload, null,
                    {
                        success: function() {
                            // Flags to know when to render the success
                            self._showSignupSuccess = true;

                            // Replace buttons by a unique Back button
                            self.options.meta.buttons = self._backButton;
                            if (!self.disposed) {
                                self.render();
                            }
                        },
                        complete: function() {
                            app.alert.dismiss('signup');
                            app.$contentEl.show();
                        }
                    });
            }
        }, self);
    },

    /**
     * Really basic metadata for the Back button displayed on Sign Up success
     */
    _backButton: [
        {
            name: 'cancel_button',
            type: 'button',
            label: 'LBL_BACK',
            value: 'signup',
            primary: false
        }
    ],

    /**
     * The signup page is accessible when the user is not authenticated. As the call to Public API doesn't return app_list_strings we need
     * to hardcode it in this view and manually inject it.
     * This means this dropdown will always be displayed in english.
     */
    // Base metadata for Login module and login view
    _metadata: {
        app_list_strings: {
            'countries_dom': {
                'ABU DHABI': 'Abu Dhabi',
                'ADEN': 'Aden',
                'AFGHANISTAN': 'Afghanistan',
                'ALBANIA': 'Albania',
                'ALGERIA': 'Algeria',
                'AMERICAN SAMOA': 'American Samoa',
                'ANDORRA': 'Andorra',
                'ANGOLA': 'Angola',
                'ANTARCTICA': 'Antarctica',
                'ANTIGUA': 'Antigua',
                'ARGENTINA': 'Argentina',
                'ARMENIA': 'Armenia',
                'ARUBA': 'Aruba',
                'AUSTRALIA': 'Australia',
                'AUSTRIA': 'Austria',
                'AZERBAIJAN': 'Azerbaijan',
                'BAHAMAS': 'Bahamas',
                'BAHRAIN': 'Bahrain',
                'BANGLADESH': 'Bangladesh',
                'BARBADOS': 'Barbados',
                'BELARUS': 'Belarus',
                'BELGIUM': 'Belgium',
                'BELIZE': 'Belize',
                'BENIN': 'Benin',
                'BERMUDA': 'Bermuda',
                'BHUTAN': 'Bhutan',
                'BOLIVIA': 'Bolivia',
                'BOSNIA': 'Bosnia',
                'BOTSWANA': 'Botswana',
                'BOUVET ISLAND': 'Bouvet Island',
                'BRAZIL': 'Brazil',
                'BRITISH ANTARCTICA TERRITORY': 'British Antarctica Territory',
                'BRITISH INDIAN OCEAN TERRITORY': 'British Indian Ocean Territory',
                'BRITISH VIRGIN ISLANDS': 'British Virgin Islands',
                'BRITISH WEST INDIES': 'British West Indies',
                'BRUNEI': 'Brunei',
                'BULGARIA': 'Bulgaria',
                'BURKINA FASO': 'Burkina Faso',
                'BURUNDI': 'Burundi',
                'CAMBODIA': 'Cambodia',
                'CAMEROON': 'Cameroon',
                'CANADA': 'Canada',
                'CANAL ZONE': 'Canal Zone',
                'CANARY ISLAND': 'Canary Island',
                'CAPE VERDI ISLANDS': 'Cape Verdi Islands',
                'CAYMAN ISLANDS': 'Cayman Islands',
                'CEVLON': 'Cevlon',
                'CHAD': 'Chad',
                'CHANNEL ISLAND UK': 'Channel Island UK',
                'CHILE': 'Chile',
                'CHINA': 'China',
                'CHRISTMAS ISLAND': 'Christmas Island',
                'COCOS (KEELING) ISLAND': 'Cocos (Keeling) Island',
                'COLOMBIA': 'Colombia',
                'COMORO ISLANDS': 'Comoro Islands',
                'CONGO': 'Congo',
                'CONGO KINSHASA': 'Congo Kinshasa',
                'COOK ISLANDS': 'Cook Islands',
                'COSTA RICA': 'Costa Rica',
                'CROATIA': 'Croatia',
                'CUBA': 'Cuba',
                'CURACAO': 'Curacao',
                'CYPRUS': 'Cyprus',
                'CZECH REPUBLIC': 'Czech Republic',
                'DAHOMEY': 'Dahomey',
                'DENMARK': 'Denmark',
                'DJIBOUTI': 'Djibouti',
                'DOMINICA': 'Dominica',
                'DOMINICAN REPUBLIC': 'Dominican Republic',
                'DUBAI': 'Dubai',
                'ECUADOR': 'Ecuador',
                'EGYPT': 'Egypt',
                'EL SALVADOR': 'El Salvador',
                'EQUATORIAL GUINEA': 'Equatorial Guinea',
                'ESTONIA': 'Estonia',
                'ETHIOPIA': 'Ethiopia',
                'FAEROE ISLANDS': 'Faeroe Islands',
                'FALKLAND ISLANDS': 'Falkland Islands',
                'FIJI': 'Fiji',
                'FINLAND': 'Finland',
                'FRANCE': 'France',
                'FRENCH GUIANA': 'French Guiana',
                'FRENCH POLYNESIA': 'French Polynesia',
                'GABON': 'Gabon',
                'GAMBIA': 'Gambia',
                'GEORGIA': 'Georgia',
                'GERMANY': 'Germany',
                'GHANA': 'Ghana',
                'GIBRALTAR': 'Gibraltar',
                'GREECE': 'Greece',
                'GREENLAND': 'Greenland',
                'GUADELOUPE': 'Guadeloupe',
                'GUAM': 'Guam',
                'GUATEMALA': 'Guatemala',
                'GUINEA': 'Guinea',
                'GUYANA': 'Guyana',
                'HAITI': 'Haiti',
                'HONDURAS': 'Honduras',
                'HONG KONG': 'Hong Kong',
                'HUNGARY': 'Hungary',
                'ICELAND': 'Iceland',
                'IFNI': 'Ifni',
                'INDIA': 'India',
                'INDONESIA': 'Indonesia',
                'IRAN': 'Iran',
                'IRAQ': 'Iraq',
                'IRELAND': 'Ireland',
                'ISRAEL': 'Israel',
                'ITALY': 'Italy',
                'IVORY COAST': 'Ivory Coast',
                'JAMAICA': 'Jamaica',
                'JAPAN': 'Japan',
                'JORDAN': 'Jordan',
                'KAZAKHSTAN': 'Kazakhstan',
                'KENYA': 'Kenya',
                'KOREA': 'Korea',
                'KOREA, SOUTH': 'Korea, South',
                'KUWAIT': 'Kuwait',
                'KYRGYZSTAN': 'Kyrgyzstan',
                'LAOS': 'Laos',
                'LATVIA': 'Latvia',
                'LEBANON': 'Lebanon',
                'LEEWARD ISLANDS': 'Leeward Islands',
                'LESOTHO': 'Lesotho',
                'LIBYA': 'Libya',
                'LIECHTENSTEIN': 'Liechtenstein',
                'LITHUANIA': 'Lithuania',
                'LUXEMBOURG': 'Luxembourg',
                'MACAO': 'Macao',
                'MACEDONIA': 'Macedonia',
                'MADAGASCAR': 'Madagascar',
                'MALAWI': 'Malawi',
                'MALAYSIA': 'Malaysia',
                'MALDIVES': 'Maldives',
                'MALI': 'Mali',
                'MALTA': 'Malta',
                'MARTINIQUE': 'Martinique',
                'MAURITANIA': 'Mauritania',
                'MAURITIUS': 'Mauritius',
                'MELANESIA': 'Melanesia',
                'MEXICO': 'Mexico',
                'MOLDOVIA': 'Moldovia',
                'MONACO': 'Monaco',
                'MONGOLIA': 'Mongolia',
                'MOROCCO': 'Morocco',
                'MOZAMBIQUE': 'Mozambique',
                'MYANAMAR': 'Myanamar',
                'NAMIBIA': 'Namibia',
                'NEPAL': 'Nepal',
                'NETHERLANDS': 'Netherlands',
                'NETHERLANDS ANTILLES': 'Netherlands Antilles',
                'NETHERLANDS ANTILLES NEUTRAL ZONE': 'Netherlands Antilles Neutral Zone',
                'NEW CALADONIA': 'New Caladonia',
                'NEW HEBRIDES': 'New Hebrides',
                'NEW ZEALAND': 'New Zealand',
                'NICARAGUA': 'Nicaragua',
                'NIGER': 'Niger',
                'NIGERIA': 'Nigeria',
                'NORFOLK ISLAND': 'Norfolk Island',
                'NORWAY': 'Norway',
                'OMAN': 'Oman',
                'OTHER': 'Other',
                'PACIFIC ISLAND': 'Pacific Island',
                'PAKISTAN': 'Pakistan',
                'PANAMA': 'Panama',
                'PAPUA NEW GUINEA': 'Papua New Guinea',
                'PARAGUAY': 'Paraguay',
                'PERU': 'Peru',
                'PHILIPPINES': 'Philippines',
                'POLAND': 'Poland',
                'PORTUGAL': 'Portugal',
                'PORTUGUESE TIMOR': 'Portuguese Timor',
                'PUERTO RICO': 'Puerto Rico',
                'QATAR': 'Qatar',
                'REPUBLIC OF BELARUS': 'Republic of Belarus',
                'REPUBLIC OF SOUTH AFRICA': 'Republic of South Africa',
                'REUNION': 'Reunion',
                'ROMANIA': 'Romania',
                'RUSSIA': 'Russia',
                'RWANDA': 'Rwanda',
                'RYUKYU ISLANDS': 'Ryukyu Islands',
                'SABAH': 'Sabah',
                'SAN MARINO': 'San Marino',
                'SAUDI ARABIA': 'Saudi Arabia',
                'SENEGAL': 'Senegal',
                'SERBIA': 'Serbia',
                'SEYCHELLES': 'Seychelles',
                'SIERRA LEONE': 'Sierra Leone',
                'SINGAPORE': 'Singapore',
                'SLOVAKIA': 'Slovakia',
                'SLOVENIA': 'Slovenia',
                'SOMALILIAND': 'Somaliliand',
                'SOUTH AFRICA': 'South Africa',
                'SOUTH YEMEN': 'South Yemen',
                'SPAIN': 'Spain',
                'SPANISH SAHARA': 'Spanish Sahara',
                'SRI LANKA': 'Sri Lanka',
                'ST. KITTS AND NEVIS': 'St. Kitts And Nevis',
                'ST. LUCIA': 'St. Lucia',
                'SUDAN': 'Sudan',
                'SURINAM': 'Surinam',
                'SW AFRICA': 'SW Africa',
                'SWAZILAND': 'Swaziland',
                'SWEDEN': 'Sweden',
                'SWITZERLAND': 'Switzerland',
                'SYRIA': 'Syria',
                'TAIWAN': 'Taiwan',
                'TAJIKISTAN': 'Tajikistan',
                'TANZANIA': 'Tanzania',
                'THAILAND': 'Thailand',
                'TONGA': 'Tonga',
                'TRINIDAD': 'Trinidad',
                'TUNISIA': 'Tunisia',
                'TURKEY': 'Turkey',
                'UGANDA': 'Uganda',
                'UKRAINE': 'Ukraine',
                'UNITED ARAB EMIRATES': 'United Arab Emirates',
                'UNITED KINGDOM': 'United Kingdom',
                'UPPER VOLTA': 'Upper Volta',
                'URUGUAY': 'Uruguay',
                'US PACIFIC ISLAND': 'US Pacific Island',
                'US VIRGIN ISLANDS': 'US Virgin Islands',
                'USA': 'USA',
                'UZBEKISTAN': 'Uzbekistan',
                'VANUATU': 'Vanuatu',
                'VATICAN CITY': 'Vatican City',
                'VENEZUELA': 'Venezuela',
                'VIETNAM': 'Vietnam',
                'WAKE ISLAND': 'Wake Island',
                'WEST INDIES': 'West Indies',
                'WESTERN SAHARA': 'Western Sahara',
                'YEMEN': 'Yemen',
                'ZAIRE': 'Zaire',
                'ZAMBIA': 'Zambia',
                'ZIMBABWE': 'Zimbabwe'
            },
            'state_dom': {
                'AL': 'Alabama',
                'AK': 'Alaska',
                'AZ': 'Arizona',
                'AR': 'Arkansas',
                'CA': 'California',
                'CO': 'Colorado',
                'CT': 'Connecticut',
                'DE': 'Delaware',
                'DC': 'District Of Columbia',
                'FL': 'Florida',
                'GA': 'Georgia',
                'HI': 'Hawaii',
                'ID': 'Idaho',
                'IL': 'Illinois',
                'IN': 'Indiana',
                'IA': 'Iowa',
                'KS': 'Kansas',
                'KY': 'Kentucky',
                'LA': 'Louisiana',
                'ME': 'Maine',
                'MD': 'Maryland',
                'MA': 'Massachusetts',
                'MI': 'Michigan',
                'MN': 'Minnesota',
                'MS': 'Mississippi',
                'MO': 'Missouri',
                'MT': 'Montana',
                'NE': 'Nebraska',
                'NV': 'Nevada',
                'NH': 'New Hampshire',
                'NJ': 'New Jersey',
                'NM': 'New Mexico',
                'NY': 'New York',
                'NC': 'North Carolina',
                'ND': 'North Dakota',
                'OH': 'Ohio',
                'OK': 'Oklahoma',
                'OR': 'Oregon',
                'PA': 'Pennsylvania',
                'RI': 'Rhode Island',
                'SC': 'South Carolina',
                'SD': 'South Dakota',
                'TN': 'Tennessee',
                'TX': 'Texas',
                'UT': 'Utah',
                'VT': 'Vermont',
                'VA': 'Virginia ',
                'WA': 'Washington',
                'WV': 'West Virginia',
                'WI': 'Wisconsin',
                'WY': 'Wyoming'
            }
        }
    }
})
