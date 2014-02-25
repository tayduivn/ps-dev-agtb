/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ('Company') that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    extendsFrom: 'DnbView',

    // idCounter used for jsTree metadata
    idCounter: 1,

    duns_num: null,

    initialize: function(options) {
        this._super('initialize', [options]);
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
        this.layout.on('dashlet:collapse', this.loadFamilyTree, this);
        app.events.on('dnbcompinfo:duns_selected', this.collapseDashlet, this);
    },

    loadData: function(options) {
        if(this.model.get("duns_num")){
            this.duns_num = this.model.get("duns_num");
        }
    },

    /**
     * Refresh dashlet once Refresh link clicked from gear button
     * To show updated contact information from DNB service
     */
    refreshClicked: function() {
        this.loadFamilyTree(false);
    },

    /**
     * Handles the dashlet expand | collapse events
     * @param  {Boolean} isCollapsed
     */
    loadFamilyTree: function(isCollapsed) {
        if (!isCollapsed) {
            //check if account is linked with a D-U-N-S
            if (this.duns_num) {
                this.getDNBFamilyTree(this.duns_num);
            } else if (!_.isUndefined(app.controller.context.get('dnb_temp_duns_num'))) {
                //check if D-U-N-S is set in context by refresh dashlet
                this.getDNBFamilyTree(app.controller.context.get('dnb_temp_duns_num'));
            } else {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) {
                    this.render();
                }
            }
        }
    },

    /**
     * obtain family tree for a given duns_num
     * @param  {String} duns_num
     */
    getDNBFamilyTree: function(duns_num) {
        var self = this;
        self.duns_num = duns_num;
        self.idCounter = 1;
        self.template = app.template.get(self.name);
        if (!self.disposed) {
            self.render();
            self.$('#dnb-family-tree-loading').show();
            self.$('#dnb-family-tree-details').hide();
        }
        //check if cache has this data already
        var cacheKey = 'dnb:familytree:' + self.duns_num;
        if (app.cache.get(cacheKey)) {
            _.bind(self.renderFamilyTreeFromCache, self, app.cache.get(cacheKey).product)();
        } else {
            var dnbFamilyTreeURL = app.api.buildURL('connector/dnb/familytree/' + duns_num, '', {},{});
            var resultData = {'product': null, 'errmsg': null};
            app.api.call('READ', dnbFamilyTreeURL, {},{
                success: function(data) {
                    var resultIDPath = 'OrderProductResponse.TransactionResult.ResultID';
                    var resultText = 'OrderProductResponse.TransactionResult.ResultText';

                    if (self.checkNested(data, resultIDPath) &&
                        data.OrderProductResponse.TransactionResult.ResultID === 'CM000') {
                        resultData.product = data;
                        app.cache.set(cacheKey, resultData);
                    } else if (self.checkNested(data, resultText)) {
                        resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                    } else {
                        resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                    }
                    if (self.disposed) {
                        return;
                    }
                    _.extend(self, resultData);
                    self.render();
                    if (!resultData.errmsg) {
                        self.renderFamilyTree(resultData.product);
                    }
                    self.$('#dnb-family-tree-loading').hide();
                    self.$('#dnb-family-tree-details').show();
                },
                error: _.bind(self.checkAndProcessError, self)
            });
        }
    },

    /**
     * Render the family tree from the cache
     * @param  {Object} familyTree
     */
    renderFamilyTreeFromCache: function(familyTree) {
        if (this.disposed) {
            return;
        }
        this.render();
        this.renderFamilyTree(familyTree);
        this.$('#dnb-family-tree-loading').hide();
        this.$('#dnb-family-tree-details').show();
    },

    /**
     * Check if a particular json path is valid
     * @param {Object} obj
     * @param {String} path
     */
    checkNested: function(obj, path) {
        var args = path.split('.');
        for (var i = 0; i < args.length; i++) {
            if (_.isNull(obj) || _.isUndefined(obj) || !obj.hasOwnProperty(args[i])) {
                return false;
            }
            obj = obj[args[i]];
        }
        return true;
    },

    /**
     * converting dnb data to jstree format
     * @param  {Object} data
     * @return {Object}
     */
    dnbToJSTree: function(data) {
        var jsTreeData = {};
        jsTreeData.data = [];
        var jsonPath = 'OrderProductResponse.OrderProductResponseDetail.Product.Organization';
        if (this.checkNested(data, jsonPath)) {
            jsTreeData.data.push(this.getDataRecursive(data.OrderProductResponse.OrderProductResponseDetail.Product.Organization));
        }
        return jsTreeData;
    },

    /**
     * Format family tree data recursively
     * in accordance with the jstree plugin
     * @param  {Object} data
     * @return {Object}
     */
    getDataRecursive: function(data) {
        var intermediateData = {};
        var orgNamePath = 'OrganizationName.OrganizationPrimaryName.OrganizationName.$';
        var cityNamePath = 'Location.PrimaryAddress.PrimaryTownName';
        var countryNamePath = 'Location.PrimaryAddress.CountryISOAlpha2Code';
        var stateNamePath = 'Location.PrimaryAddress.TerritoryOfficialName';
        var dunsPath = 'SubjectHeader.DUNSNumber';
        var childrenPath = 'Linkage.FamilyTreeMemberOrganization';
        var orgName = this.checkNested(data, orgNamePath) ? data.OrganizationName.OrganizationPrimaryName.OrganizationName['$'] : '';
        var dunsNum = this.checkNested(data, dunsPath) ? data.SubjectHeader.DUNSNumber : '';
        var countryName = this.checkNested(data, countryNamePath) ? data.Location.PrimaryAddress.CountryISOAlpha2Code : '';
        var stateName = this.checkNested(data, stateNamePath) ? data.Location.PrimaryAddress.TerritoryOfficialName : '';
        var cityName = this.checkNested(data, cityNamePath) ? data.Location.PrimaryAddress.PrimaryTownName : '';

        intermediateData.metadata = {'id' : this.idCounter};
        intermediateData.attr = {'id' : this.idCounter};
        this.idCounter++;
        intermediateData.data = orgName + ' (' + dunsNum + ')' + ((cityName !== '' && cityName !== null) ? (', ' + cityName) : '') + ((stateName !== '' && stateName !== null) ? (', ' + stateName) : '') + (countryName !== '' ? (', ' + countryName) : '');

        if (parseInt(dunsNum) === parseInt(this.duns_num)) {
            intermediateData.data = intermediateData.data + '&nbsp;&nbsp;<span class="label label-success pull-right">DUNS</span>';
            intermediateData.state = 'open';
            this.initialSelect = [1, intermediateData.metadata.id];
            this.initialOpen = [1, intermediateData.metadata.id];
        }

        if (intermediateData.metadata.id === 1) {
            intermediateData.state = 'open';
        }

        if (this.checkNested(data, childrenPath) &&
            data.Linkage.FamilyTreeMemberOrganization.length > 0) {
            var childRootData = data.Linkage.FamilyTreeMemberOrganization;
            intermediateData.children = [];
            //for each child do a getDataRecursive
            for (var childCounter = 0; childCounter < childRootData.length; childCounter++) {
                intermediateData.children.push(this.getDataRecursive(childRootData[childCounter]));
            }
        }
        return intermediateData;
    },

    /**
     * Renders the family tree using the jsTree plugin
     * @param  {Object} familyTreeData
     */
    renderFamilyTree: function(familyTreeData) {
        $('#dnb-family-tree').jstree({
            // generating tree from json data
            'json_data' : this.dnbToJSTree(familyTreeData),
            // plugins used for this tree
            'plugins' : ['json_data', 'ui', 'types'],
            'core' : {
                'html_titles' : true
            }
        }).bind('loaded.jstree', function() {
            // do stuff when tree is loaded
            $('#dnb-family-tree').addClass('jstree-sugar');
            $('#dnb-family-tree > ul').addClass('list');
            $('#dnb-family-tree > ul > li > a').addClass('jstree-clicked');
        }).bind('select_node.jstree', function(e, data) {
            // do stuff when a node is selected
            data.inst.toggle_node(data.rslt.obj);
        });
    }
})