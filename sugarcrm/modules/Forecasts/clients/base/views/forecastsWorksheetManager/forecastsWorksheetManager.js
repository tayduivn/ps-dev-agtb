/**
 * View that displays header for current app
 * @class View.Views.WorksheetView
 * @alias SUGAR.App.layout.WorksheetView
 * @extends View.View
 */
({
	url: 'rest/v10/ForecastManagerWorksheets',
    show: false,
    viewModule: {},
    selectedUser: {},
    gTable:'',
    // boolean for enabled expandable row behavior
    isExpandableRows:'',
    _collection:{},
    
    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        this.viewModule = app.viewModule;
        var self = this;
        //set expandable behavior to false by default
        this.isExpandableRows = false;
        
        app.view.View.prototype.initialize.call(this, options);

        //set up base selected user
    	this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};
        this.timePeriod = app.defaultSelections.timeperiod_id.id
        this.category = app.defaultSelections.category.id

        this._collection = this.context.forecasts.worksheetmanager;
        this._collection.url = this.createURL();
        this._collection.isDirty = false;
    	
    	//Setup total subview
    	var TotalModel = Backbone.Model.extend({

        });

        this.totalModel = new TotalModel(
            {
                amount : 0,
                quota : 0,
                best_case : 0,
                best_adjusted : 0,
                likely_case : 0,
                likely_adjusted : 0
            }
        );


        var TotalView = Backbone.View.extend({
            id : 'summaryManager',

            tagName : 'tfoot',

            /*initialize: function() {
                self.context.on("change:selectedToggle", function(context, data) {
                    self.refresh();
                });
            },*/

            render: function() {
                var self = this;
                var source = $("#overall_manager_template").html();
                var hb = Handlebars.compile(source);
                $('#summaryManager').html(hb(self.model.toJSON()));
                return this;
            }
        });

        this.totalView = new TotalView({
            model : this.totalModel
        });
    },

    /**
     * Event Handler for updating the worksheet by a selected user
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedUser:function (selectedUser) {
        this.selectedUser = selectedUser;
        if(!this.showMe()){
        	return false;
        }
        this._collection = this.context.forecasts.worksheetmanager;
        this._collection.url = this.createURL();
        this.safeFetch();
    },

    bindDataChange: function() {
        if(this._collection)
        {
            this._collection.on("reset", function(){
            	this.render();
            }, this);
        }
        // listening for updates to context for selectedUser:change
        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
                function(context, selectedUser) {
                    this.updateWorksheetBySelectedUser(selectedUser);
                }, this);
            this.context.forecasts.on("change:selectedTimePeriod",
                function(context, timePeriod) {
                    this.updateWorksheetBySelectedTimePeriod(timePeriod);
                }, this);
            this.context.forecasts.on("change:selectedCategory",
                function(context, category) {
                    this.updateWorksheetBySelectedCategory(category);
                },this);
            this.context.forecasts.worksheetmanager.on("change", function() {
            	this.calculateTotals();
                this.totalView.render();
            }, this);
            this.context.forecasts.on("change:reloadWorksheetFlag", function(){
            	
            	if(this.context.forecasts.get('reloadWorksheetFlag') && this.showMe()){
            		var model = this.context.forecasts.worksheetmanager;
            		model.url = this.createURL();
            		this.safeFetch();
            		this.context.forecasts.set({reloadWorksheetFlag: false});
            	}
            	
            }, this);
            var worksheet = this;
            $(window).bind("beforeunload",function(){
            	worksheet.safeFetch();
            });
        }
    },
    
    /**
     * This function checks to see if the worksheet is dirty, and gives the user the option
     * of saving their work before the sheet is fetched.
     */
    safeFetch: function(){
    	var collection = this._collection; 
    	var self = this;
    	if(collection.isDirty){
    		//unsaved changes, ask if you want to save.
    		if(confirm(app.lang.get("LBL_WORKSHEET_SAVE_CONFIRM", "Forecasts"))){
    			_.each(collection.models, function(model, index){
					var isDirty = model.get("isDirty");
					if(typeof(isDirty) == "boolean" && isDirty ){
        				model.set({draft: 1}, {silent:true});
        				model.save();
        				model.set({isDirty: false}, {silent:true});
        			}  
				});
    			collection.isDirty = false;
				$.when(!collection.isDirty).then(function(){
	    			self.context.forecasts.set({reloadCommitButton: true});
	    			collection.fetch();
    		});
			
		}
    		else{
    			//ignore, fetch still
    			collection.isDirty = false;
    			self.context.forecasts.set({reloadCommitButton: true});
    			collection.fetch();
    		}
    	}
    	else{
    		//no changes, fetch like normal.
    		collection.fetch();	
    	}    	
    },

    /**
     * Renders a field.
     *
     * This method sets field's view element and invokes render on the given field.  If clickToEdit is set to true
     * in metadata, it will also render it as clickToEditable.
     * @param {View.Field} field The field to render
     * @protected
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        if (field.viewName !="edit" && field.def.clickToEdit === true && _.isEqual(this.selectedUser.id, app.user.get('id'))) {
            field = new app.view.ClickToEditField(field, this);
        }
    },
    
    /**
     * Renders view
     */
    _renderHtml:function (ctx, options) {
        var self = this;
        var enableCommit = false;
        
        if(!this.showMe()){
        	return false;
        }
        $("#view-sales-rep").hide();
        $("#view-manager").show();
        this.context.forecasts.set({currentWorksheet: "worksheetmanager"});
        app.view.View.prototype._renderHtml.call(this, ctx, options);
        
        // parse metadata into columnDefs
        // so you can sort on the column's "name" prop from metadata
        var columnDefs = [];
        var fields = this.meta.panels[0].fields;
        // define vars for use in loop, created outside loop
        var def = {};
        var name = '';
        var colWidth = '';

        for( var i = 0; i < fields.length; i++ )  {
            name = fields[i].name;
            // explicitly looking for column "name" instead of the first column
            // in case we add column rearranging
            if(name == "name") {
                colWidth = '40%';
            } else {
                colWidth = '10%';
            }
            def = {
                "sName": name,
                "aTargets": [ i ],
                "sWidth" : colWidth
            };
            columnDefs.push( def );
        }

        this.gTable = this.$(".view-forecastsWorksheetManager").dataTable(
            {
                "bAutoWidth": false,
                "aoColumnDefs": columnDefs,
                "bInfo":false,
                "bPaginate":false

            }
        );

        // if isExpandable, add expandable row behavior
        if (this.isExpandableRows) {
            $('.worksheetManagerTable tr').on('click', function () {
                if (self.gTable.fnIsOpen(this)) {
                    self.gTable.fnClose(this);
                } else {
                    self.gTable.fnOpen(this, self.formatAdditionalDetails(this), 'details');
                }
            });
        }
        
        //see if anything in the model is a draft version
        _.each(this._collection.models, function(model, index){
        	if(model.get("version") == 0){
        		enableCommit = true;
        	}
        });
        if(enableCommit){
        	self.context.forecasts.set({commitButtonEnabled: true});
        }
        else{
        	self.context.forecasts.set({commitButtonEnabled: false});
        }
        
        this.calculateTotals();
        this.totalView.render();
    },
    
    calculateTotals: function() {
        var self = this;
        var amount = 0;
        var quota = 0;
        var best_case = 0;
        var best_adjusted = 0;
        var likely_case = 0;
        var likely_adjusted = 0;
        var worst_adjusted = 0;
        var worst_case = 0;

        if(!this.showMe()){
            // if we don't show this worksheet set it all to zero
        	this.context.forecasts.set("updatedManagerTotals", {
                'amount' : amount,
                'quota' : quota,
                'best_case' : best_case,
                'best_adjusted' : best_adjusted,
                'likely_case' : likely_case,
                'likely_adjusted' : likely_adjusted,
                'worst_adjusted' : worst_adjusted,
                'worst_case' : worst_case
            });
            return false;
        }


        _.each(self._collection.models, function (model) {

           amount 			+= parseFloat(model.get('amount'));
           quota 			+= parseFloat(model.get('quota'));
           best_case 		+= parseFloat(model.get('best_case'));
           best_adjusted 	+= parseFloat(model.get('best_adjusted'));
           likely_case 		+= parseFloat(model.get('likely_case'));
           likely_adjusted 	+= parseFloat(model.get('likely_adjusted'));
           worst_case       += parseFloat(model.get('worst_case'));
           worst_adjusted 	+= parseFloat(model.get('worst_adjusted'));
                
        });

        self.totalModel.set({
            amount : amount,
            quota : quota,
            best_case : best_case,
            best_adjusted : best_adjusted,
            likely_case : likely_case,
            likely_adjusted : likely_adjusted,
            worst_case : worst_case,
            worst_adjusted : worst_adjusted
        });
        
        //in case this is needed later..
        var totals = {
            'amount' : amount,
            'quota' : quota,
            'best_case' : best_case,
            'best_adjusted' : best_adjusted,
            'likely_case' : likely_case,
            'likely_adjusted' : likely_adjusted,
            'worst_case' : worst_case,
            'worst_adjusted' : worst_adjusted
        };

        this.context.forecasts.set("updatedManagerTotals", totals);
    },
    
    
    /**
     * Determines if this Worksheet should be rendered
     */
    showMe: function(){
    	var selectedUser = this.selectedUser;
    	this.show = false;
    	
    	if(!selectedUser.showOpps && selectedUser.isManager){
    		this.show = true;
    	}
    	
    	return this.show;
    },

    /**
     * Event Handler for updating the worksheet by a selected category
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedCategory:function (params) {
        // INVESTIGATE:  this needs to be more dynamic and deal with potential customizations based on how filters are built in admin and/or studio
        if (app.config.show_buckets == 1) {
            // TODO: this.
        } else {
            this.category = _.first(params);
        }

        var model = this.context.forecasts.worksheetmanager;
        if(!this.showMe()){
            return false;
        }
        model.url = this.createURL();
        this.safeFetch();
    },

    /**
     * Event Handler for updating the worksheet by a timeperiod id
     *
     * @param params is always a context
     */
    updateWorksheetBySelectedTimePeriod:function (params) {
    	this.timePeriod = params.id;
        var model = this.context.forecasts.worksheetmanager;
        if(!this.showMe()){
        	return false;
        }
        model.url = this.createURL();
        this.safeFetch();
    },

    createURL:function() {
        var url = this.url;
        var args = {};
        if(this.timePeriod) {
           args['timeperiod_id'] = this.timePeriod;
        }

        if(this.category) {
            args['category'] = this.category;
        }

        if(this.selectedUser)
        {
           args['user_id'] = this.selectedUser.id;
        }

        url = app.api.buildURL('ForecastManagerWorksheets', '', '', args);
        return url;
    },

    /**
     * Returns an array of column headings
     *
     * @param dTable datatable param so we can grab all the column headings from it
     * @param onlyVisible -OPTIONAL, defaults true- if we want to return only visible column headings or not
     * @return {Array} column heading title strings in an array ["heading","heading2"...]
     */
    getColumnHeadings:function (dTable, onlyVisible) {
        // onlyVisible needs to default to true if it is not false
        if (onlyVisible !== false) {
            onlyVisible = typeof onlyVisible !== 'undefined' ? onlyVisible : true;
        }

        var cols = dTable.fnSettings().aoColumns;
        var retColumns = [];

        for (var i in cols) {

            var title = this.app.lang.get(cols[i].sTitle);

            if (onlyVisible) {
                if (cols[i].bVisible) {
                    retColumns.push(title);
                }
            } else {
                retColumns.push(title);
            }
        }

        return retColumns;
    },

    /***
     * Checks current gTable to see if a particular column name exists
     * @param columnName the column sName you're checking for.  NOT the Column sTitle/heading
     * @return {Boolean} true if it exists, false if not
     */
    hasColumn:function(columnName) {
        var containsColumnName = false;
        var cols = this.gTable.fnSettings().aoColumns;

        for (var i in cols) {
            if(cols[i].sName == columnName)  {
                containsColumnName = true;
                break;
            }
        }

        return containsColumnName;
    }
})
