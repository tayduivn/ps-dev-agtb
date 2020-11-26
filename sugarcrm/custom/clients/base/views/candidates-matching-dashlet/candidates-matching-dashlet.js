({
    extendsFrom: 'DashablelistView',
    /**
     * @inheritdoc
     * Don't load data if dashlet filter is not accessible.
     */
    loadData: function(options) {
        if(!this.already) {
            this.model.on('change:country', this.loadData, this);
            this.model.on('change:pos_function', this.loadData, this);
            this.model.on('change:gtb_cluster', this.loadData, this);
            this.already = true;
        }
        this._super('loadData', [options]);
        filterDef = [];
        if(typeof this.context.get('collection') !== 'undefined') {
            this._displayDashlet(filterDef);
        }
    },

    _buildFilterDef: function(fieldName, operator, searchTerm) {
        var def = {};
        var filter = {};
        filter[operator] = searchTerm;
        def[fieldName] = filter;
        return def;
    },

    _displayDashlet: function(filterDef) {
        var posCluster = this.model.get('gtb_cluster');
        var posFunction = this.model.get('pos_function');
        var posCountry = this.model.get('country');
        var filterOptions1 = this._buildFilterDef('gtb_function_match_c', '$contains', posFunction);
        var filterOptions2 = this._buildFilterDef('gtb_country_match_c', '$contains', posCountry);
        var filterOptions3 = this._buildFilterDef('gtb_cluster_match_c', '$equals', posCluster);
        filterDef = _.extend({}, filterDef, filterOptions1);
        filterDef = _.extend({}, filterDef, filterOptions2);
        filterDef = _.extend({}, filterDef, filterOptions3);
        this._super('_displayDashlet', [filterDef]);
    },

    _dispose: function() {
        this.model.on('change:country', this.loadData);
        this.model.on('change:pos_function', this.loadData);
        this.model.on('change:gtb_cluster', this.loadData);

        this._super('_dispose');
    },
})
