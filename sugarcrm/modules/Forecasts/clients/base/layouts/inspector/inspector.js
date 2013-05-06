({
    /**
     * We extendFrom the base Inspector Layout
     */
    extendsFrom: 'inspector',

    /**
     * The Rows We Support
     */
    rows: [],
    /**
     * Total Number of Rows
     */
    maxIndex: 0,
    /**
     * Which Index was the initial selection
     */
    selectedIndex: -1,
    /**
     * What is the current selection
     */
    currentIndex: -1,

    /**
     * Do we need to toggle on and off the sidebar?
     */
    toggleSidebar: false,

    /**
     * Initialize
     * @param options
     */
    initialize: function (options) {
        app.view.invoke(this, 'layout', 'inspector', 'initialize', {args:[options]});

        // setup the bind events
        this.bind();
    },

    /**
     * Watch for specific events to happen on the context
     */
    bindDataChange: function () {
        var self = this;
        this.context.on('forecasts:commitButtons:sidebarHidden', function (value) {
            // set the value of the hiddenSidecar to we can stop the render if the sidebar is hidden
            self.toggleSidebar = value;
        });
        this.context.on("change:selectedTimePeriod", function(context, timePeriod) {
            // when the time-period changes, we need to hide this.
            self.hide();
        });
        this.context.on('forecasts:change:worksheetRows', function(newRows) {
            var row = self.removeHighlight(self.findHighlighted());
            self.setRows(newRows, row);
        });
    },

    /**
     * Setup all the local listeners
     */
    bind: function () {
        this.on('show', this.onShow);
        this.on('hide', this.onHide);
        this.on('next', this.onNext);
        this.on('previous', this.onPrevious);

        this.before('show', this.onBeforeShow);
        this.before('hide', this.onBeforeHide);
    },

    /**
     * On Show Event
     * @param layout
     */
    onShow: function (layout) {
        this.removeHighlight(this.findHighlighted());
        // highlight the new row
        this.highlight(this.selectedIndex);
        this.handleButtons(this.selectedIndex);

        this.layout.trigger("inspectorVisible", true);
    },

    /**
     * On Hide Event
     *
     * @param layout
     */
    onHide: function (layout) {
        this.checkSidebarVisibility('hide');

        this.layout.trigger("inspectorVisible", false);
    },

    /**
     * On Before Show Event
     */
    onBeforeShow: function () {
        this.checkSidebarVisibility('show');

        $('div.tab-pane-contents').addClass('hide').removeClass('show');
        $('#forecasts').addClass('preview')
    },

    /**
     * On Before Hide Event
     */
    onBeforeHide: function () {
        this.removeHighlight(this.findHighlighted());
        $('div.tab-pane-contents').addClass('show').removeClass('hide');
        $('#forecasts').removeClass('preview')
    },

    /**
     * On Next Click Event
     */
    onNext: function () {
        var row = this.moveHighlightedRow('next');
        this.updateOpportunity(row);
    },

    /**
     * On Previous Click Event
     */
    onPrevious: function () {
        var row = this.moveHighlightedRow('previous');
        this.updateOpportunity(row);
    },

    /**
     * Show Inspector Method
     *
     * @param params
     */
    showInspector: function (params) {
        this.setRows(params.dataset || []);
        if(this.selectedIndex == params.selectedIndex) {
            this.selectedIndex = -1;
            this.hide()
        } else {
            this.selectedIndex = params.selectedIndex;
            this.display(params);
        }
    },

    /**
     * Update the rows that the inspector knows about
     * @param rows                  The New Set of Rows
     * @param rowToHighlight        (optional)A specific row to highlight if found in the rows
     */
    setRows: function (rows, rowToHighlight) {
        this.rows = rows;
        this.maxIndex = rows.length - 1;


        if (!_.isUndefined(rowToHighlight)) {
            var rowIndex = -1;
            _.each(this.rows, function (row, index) {
                if (row == rowToHighlight) {
                    rowIndex = index
                }
            });

            if (rowIndex != -1) {
                // highlight the new row
                this.highlight(rowIndex);
                // update the buttons
                this.handleButtons(rowIndex);
            } else {
                // it's not found again, so lets, just hide the row
                this.hide();
            }
        }
    },


    /**
     * Handle the buttons on the inspector-header component
     *
     * @param newIndex
     */
    handleButtons: function (newIndex) {
        cmp = this.getComponent('inspector-header');
        if (cmp) {
            cmp.disablePrevious(false);
            cmp.disableNext(false);
            if (newIndex == 0) {
                cmp.disablePrevious(true);
            }
            if (newIndex == this.maxIndex) {
                cmp.disableNext(true);
            }
        }
    },

    /**
     * Find the highlighted row in the set of rows
     * @return {*}
     */
    findHighlighted: function () {
        var foundIndex = -1;
        _.each(this.rows, function (element, index) {
            if ($(element).hasClass('current highlighted')) {
                foundIndex = index;
            }
        }, this);

        // set the current index to the foundIndex
        this.currentIndex = foundIndex;

        return this.currentIndex;
    },

    /**
     * Highlight a specific row
     *
     * @param index
     * @return {*}
     */
    highlight: function (index) {
        $(this.rows[index]).addClass('current highlighted');
        if (index != 0) {
            $(this.rows[index - 1]).addClass('highlighted above');
        }
        if (index != this.maxIndex) {
            $(this.rows[index + 1]).addClass('highlighted below');
        }

        // save which row is selected
        this.selectedIndex = index;

        return this.rows[index];
    },

    /**
     * Remove the highlight from a specific row
     *
     * @param index
     * @return {*}
     */
    removeHighlight: function (index) {
        $(this.rows[index]).removeClass('current highlighted');
        if (index != 0) {
            $(this.rows[index - 1]).removeClass('highlighted above');
        }
        if (index != this.maxIndex) {
            $(this.rows[index + 1]).removeClass('highlighted below');
        }

        return this.rows[index];
    },

    /**
     * Move the highlight row in a specific direction if allowed,
     * @param direction         Valid Options are "previous" or "next"
     * @return {*}
     */
    moveHighlightedRow: function (direction) {
        var newIndex = this.findHighlighted();
        if (direction == "previous") {
            newIndex--;
            if (newIndex < 0) {
                newIndex = 0;
            }
        } else if (direction == "next") {
            newIndex++;
            if (newIndex > this.maxIndex) {
                newIndex = this.maxIndex;
            }
        }

        this.removeHighlight(this.currentIndex);
        var row = this.highlight(newIndex);

        this.handleButtons(newIndex);

        this.currentIndex = newIndex;

        return row;
    },

    /**
     * Update the one of the components
     * todo-thebeard: need to look into how to make this more dynamic
     * @param row
     */
    updateOpportunity: function (row) {
        var uid = $(row).find('a[rel="inspector"]>i').attr('data-uid');
        this.getComponent('forecastInspector').updateModelId(uid);
    },

    /**
     * Check weather or not we need to hide/show the sidebar when showing/hiding the inspector
     *
     * @param type              What type of operation is happening "show" is currently the only one handled
     * @return {boolean}
     */
    checkSidebarVisibility: function (type) {
        if (type == 'show' && this.isVisible()) return false;
        if (this.toggleSidebar) {
            var container = $('#contentflex').find('>div.row-fluid');
            container.find('>div:first').toggleClass('span8 span12');
            container.find('>div:last').toggleClass('span4 hide');
            this.toggleSidebar = false;
        }

        return true;
    }

})
