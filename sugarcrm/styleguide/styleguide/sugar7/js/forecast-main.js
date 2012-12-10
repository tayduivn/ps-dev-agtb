/* Data set - can contain whatever information you want */
function quarter(val) {
    var quarter = "";
    switch (val){
        case "2012-01-01 - 2012-03-30":
            quarter = "q1";
            break;
        case "2012-04-01 - 2012-06-30":
            quarter = "q2";
            break;
        case "2012-07-01 - 2012-09-30":
            quarter = "q3";
            break;
        case "2012-10-01 - 2012-12-30":
            quarter = "q4";
            break;
    }
    return quarter;
}

function invokeSaveButtons(){
    $('#headerbar .form-change-actions .btn').show();
}

$(document).ready(function() {

    // using chosen plugin for multi select as a single select, due to styling needed from multiselect.
    // https://sugarcrm.atlassian.net/browse/UIUX-117
    $("#sales_stage_filter_chzn .chzn-results > li").bind('click',function(){
      var li_active = $("ul.chzn-choices").find('.search-choice').first().attr('id'),
          li_orig = li_active.replace("_chzn_c_","_chzn_o_");

      // if not active filter "pills" exist, do not remove pill from dom
      if($("ul.chzn-choices").find('.search-choice').length > 1) {
        $("ul.chzn-choices").find('.search-choice').first().remove();
      }

      $("#"+li_orig).removeClass("result-selected").addClass("active-result");
      if($('.chzn-container').hasClass('chzn-container-active') === true ) {
        $('.chzn-container').removeClass('chzn-container-active');
      }
    });

    var chzn_legend = '<legend class="chzn-select-legend">Filter <i class="icon-caret-down"></i></legend>';
    $('#sales_stage_filter_chzn .chzn-choices').prepend(chzn_legend);

    $('.chzn-results').find('li').after("<span class='icon-ok' />");

    $('form.editable input').live('change',function(){
        invokeSaveButtons();
    });


    // toggle drawer hide - forecast
    $('.drawer').toggle(
      function () {
        $(this).next('.extend').removeClass('hide');
        $(this).find('.toggle').html('<i class="icon-caret-up"></i>');
        return false;
      },
      function () {
        $(this).next('.extend').addClass('hide');
        $(this).find('.toggle').html('<i class="icon-caret-down"></i>');
        return false;
      }
    );

    //$('#date_filter').ready(function(){
    $.fn.dataTableExt.afnFiltering.push(
        function( oSettings, aData, iDataIndex ) {
            var probability =100;
            //var probability = parseFloat(aData[4].replace(/<div class='sugareditable'><span class='click'>/,'').replace(/<\/span><\/div>/,''));
            var probability_select = 0;
            //var probability_select = $('#probability_filter')[0].value;

            //date filters
            var dateRange = $('#date_filter')[0].value;

            // parse the range from a single field into min and max, remove " - "
            var dateMin = dateRange.substring(0,4) + dateRange.substring(5,7)  + dateRange.substring(8,10);
            var dateMax = dateRange.substring(13,17) + dateRange.substring(18,20) + dateRange.substring(21,23);

            // 4 here is the column where my dates are.
            var date = aData[2];

            // remove the time stamp out of my date
            // 2010-04-11 20:48:22 -> 2010-04-11
            date = date.substring(0,10);
            // remove the "-" characters
            // 2010-04-11 -> 20100411
            date = date.substring(0,4) + date.substring(5,7) + date.substring( 8,10 )

            // run through cases
            if ( (dateMin == "" && date <= dateMax) && (probability >= probability_select)){
                return true;
            }
            else if ( (dateMin =="" && date <= dateMax)  && (probability >= probability_select)){
                return true;
            }
            else if ( (dateMin <= date && "" == dateMax)  && (probability >= probability_select)){
                return true;
            }
            else if ( (dateMin <= date && date <= dateMax)  && (probability >= probability_select)){
                return true;
            }

            return false;
        }
    );

    function fnFormatDetails ( oTable, nTr ) {
        var aData = oTable.fnGetData( nTr );
        var sOut = '<div class="extend results">'+aData[8]+'</div>';

        return sOut;
    }

    $('#dynamic').html( '<table cellpadding="0" cellspacing="0" border="0" class="table table-striped datatableInput data2" id="example"><thead></thead>' +
        '<tbody></tbody>' +
        '<tfoot>' +
            '<tr class="overall">' +
            '<th></th>' +
            '<th>Overall Total</th>' +
            '<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>' +
            '</tr>' +
        '</tfoot></table>' );

    var oTable = $('#example').dataTable({
        // "bFilter": true,
        "bInfo":false,
        "bPaginate": false,
        "bProcessing": true,
        "bAutoWidth": false,
        "sAjaxSource": 'data/sabra_json.txt' ,
        // "sScrollX": "100%",
        // "sScrollXInner": "120%",
        // "bScrollCollapse": false,
        "aoColumns": [
            {
                "sTitle": "Commit",
                "bVisible": false,
                "bSortable": false,
                "sClass": "tcenter",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var ret = "<input class='tog' type='checkbox' name='checkbox-"+sReturn+"' id='checkbox-"+sReturn+"' checked />";
                    return ret;
                }
            },
            {
                "sTitle": "Name",
                "sWidth": "15%",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var ret = "<div class='sugareditable'>" + sReturn + "</div>";
                    return ret;
                }
            },
            { "sTitle": "Close Date",
               "bVisible" : false
            },
            { "sTitle": "Quota",
               "sClass": "number",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var ret = "<div class='sugareditable'><span class='format'>" + sReturn + "</span></div>";
                    return ret;
                }
            },
            { "sTitle": "Best",
                "sClass": "number best",
                "sWidth": "20%",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var conversion = obj.aData[ obj.iDataColumn ] * 1.3;
                    var ret = "<label class='original'>"+conversion+"</label><div class='converted'><div class='sugareditable'><span class='click format'>" + sReturn + "</span></div></div>";
                    return ret;
                }
            },
            { "sTitle": "Best (adjusted)",
                "sClass": "number",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var ret = "<div class='sugareditable'><span class='format'>" + sReturn + "</span></div>";
                    return ret;
                }
            },
            { "sTitle": "Likely" ,
                "sClass": "number likely",
                "sWidth": "20%",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var conversion = obj.aData[ obj.iDataColumn ] * 1.3;
                    var ret = "<label class='original'>"+conversion+"</label><div class='converted'><div class='sugareditable'><span class='click format'>" + sReturn + "</span></div></div>";
                    return ret;
                }
            },
            { "sTitle": "Likely (adjusted)" ,
                "sClass": "number",
                "fnRender": function(obj) {
                    var sReturn = obj.aData[ obj.iDataColumn ];
                    var ret = "<div class='sugareditable'><span class='format'>" + sReturn + "</span></div>";
                    return ret;
                }
            }
        ],
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
            // Bold the grade for all 'A' grade browsers
        },
        "fnInitComplete": function(oSettings, json) {

            var nCloneTh = document.createElement( 'th' );
            var nCloneTh2 = document.createElement( 'th' );
            // variable for current side-pane content file
            var currentSidebarDetails = 'partial/forecast/sabra_details.html';
            var sideSelector = '.side.sidebar-content.span4';
            var contentSelector = '#content';

            $('#example thead tr').each( function () {
                $(this).append(nCloneTh);
                $(this).append(nCloneTh2);
            } );

            // toggle detail display of row level data preview
            $('#example .sales-rep').live('click', function () {
                var nTr = $(this).parents('tr')[0];
                var aData = oTable.fnGetData( nTr );
                $(contentSelector).removeClass("preview");
                $("td",oTable).removeClass("highlighted");
                $("td,th",oTable).removeClass("preview");
                currentSidebarDetails = ( currentSidebarDetails === 'partial/forecast/sabra_details.html' ) ? 'partial/forecast/mark_details.html' : 'partial/forecast/sabra_details.html';
                jQuery.ajax({
                    url: currentSidebarDetails + "?r=" + new Date().getTime(),
                    dataType:"text",
                    async: false,
                    success: function(data) {
                        if(data !== undefined){
                            $(sideSelector).replaceWith(data);
                        }
                    }
                });
            } );

            $('#example tbody tr').each( function () {
                var aData = oTable.fnGetData( $(this)[0] );
                var nCloneTd = document.createElement( 'td' );
                var nCloneTd2 = document.createElement( 'td' );
                if(aData[8] !== "") {
                    nCloneTd.innerHTML = '<i class="icon-exclamation-sign label label-warn" style="cursor: pointer;"></i>';
                }

                nCloneTd2.innerHTML += ' <i class="icon-eye-open" style="cursor: pointer;"></i>';

                nCloneTd.className = "center";
                $(this).append(nCloneTd);
                $(this).append(nCloneTd2);
            } );

            $('#example tbody td i.icon-exclamation-sign').live('click', function () {
                var nTr = $(this).parents('tr')[0];
                if ( oTable.fnIsOpen(nTr) )
                {
                    // This row is already open - close it *
                    //$(this).attr("class","icon-plus-sign");
                    oTable.fnClose( nTr );
                }
                else
                {
                    //Open this row
                    //$(this).attr("class","icon-minus-sign");
                    var colspan = $(nTr).children('td').length;

                    oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
                    $(nTr).next().children("td").attr("colspan",colspan);
                }
            } );

            $('.closeSubdetail').live('click', function(){
                $(contentSelector).removeClass('preview');
                $('td').removeClass('highlighted');
                $('td,th').removeClass('preview above below');
                jQuery.ajax({
                    url: currentSidebarDetails + '?r=' + new Date().getTime(),
                    dataType:'text',
                    async: false,
                    success: function(data) {
                        if(data !== undefined){
                            $(sideSelector).replaceWith(data);
                        }
                    }
                });
            });

            // preview previous/next record in table
            $('.previous-row,.next-row').live('click', function() {
                var cTr = $('td.highlighted').parent('tr');
                var aTr = ($(this).hasClass('previous-row')) ? cTr.prev('tr') : cTr.next('tr');
                if (aTr.length!==0)
                {
                    $(aTr).find('.icon-eye-open').trigger('click');
                    $('.previous-row').toggleClass('disabled', (!aTr.prev('tr').length)?true:false);
                    $('.next-row').toggleClass('disabled', (!aTr.next('tr').length)?true:false);
                }
            });

            // toggle preview display of row level data preview
            $('#example tbody td i.icon-eye-open').live('click', function () {
                var cTr = $(this).parents('tr')[0],
                    pTr = $(this).parents('tr').prev('tr'),
                    nTr = $(this).parents('tr').next('tr');
                //hand condition when first row selected
                if (pTr.length===0) {
                    pTr = $(this).parents('table').find('thead tr');
                }
                if (nTr.length===0) {
                    nTr = $(this).parents('table').find('tfoot tr');
                }
                var aData = oTable.fnGetData( cTr );

                //console.log(cTr)

                if($('td',cTr).hasClass('highlighted')) {
                    $(contentSelector).removeClass('preview');
                    $('td',cTr).removeClass('highlighted');
                    $('td,th',pTr).removeClass('preview above below');
                    $('td,th',nTr).removeClass('preview below above');
                    jQuery.ajax({
                        url: currentSidebarDetails + '?r=' + new Date().getTime(),
                        dataType:'text',
                        async: false,
                        success: function(data) {
                            if(data !== undefined){
                                $(sideSelector).replaceWith(data);
                            }
                        }
                    });
                } else  {
                    $(contentSelector).addClass('preview');
                    $('td',oTable).removeClass('highlighted');
                    $('td',cTr).addClass('highlighted');
                    $('td,th',oTable).removeClass('preview above below');
                    $('td,th',pTr).addClass('preview above');
                    $('td,th',nTr).addClass('preview below');
                    jQuery.ajax({
                        url: 'partial/forecast/' + aData[9]+'_preview.html?r=' + new Date().getTime(),
                        dataType:'text',
                        async: false,
                        success: function(data) {
                            if(data !== undefined){
                                $(sideSelector).replaceWith(data);
                            }
                        }
                    });
                }
            } );

            $('input.tog').click(function(){

                var nTr = $(this).parents('tr')[0];

                if(!$(this).attr('checked')) {
                    $(nTr).addClass('disabled');
                } else {
                    $(nTr).removeClass('disabled');
                }

                var iStart = oSettings._iDisplayStart;
                var iEnd = oSettings._iDisplayEnd;
                var aiDisplay = oSettings.aiDisplay;
                var amountTotal = 0;
                var bestTotal = 0;
                var likelyTotal = 0;

                $('#example tbody').children('tr').each(function(i) {
                    var tdInclude = $(this).children('td')[0];
                    var isChecked = $(tdInclude).find('input').attr('checked');
                    //var tdAmount =$(this).children('td')[5];
                    var tdBest =$(this).children('td')[5];
                    var tdLikely =$(this).children('td')[6];

                    if(isChecked) {
                        //amountTotal += parseFloat($(tdAmount).find('span').html().replace(/,/,""));
                        bestTotal += parseFloat($(tdBest).find('span').html().replace(/,/,""));
                        likelyTotal += parseFloat($(tdLikely).find('span').html().replace(/,/,""));
                    }
                });

                var foot = $(oTable).children('tfoot')[0];
                var trs = $(foot).children('tr');
                var included = $(trs)[0];
                var includedCells =  $(included).children('th');

                //$(includedCells[1]).html(amountTotal).prettynumber({delimiter : ','});
                $(includedCells[1]).html(bestTotal).prettynumber({delimiter : ','});
                $(includedCells[2]).html(likelyTotal).prettynumber({delimiter : ','});

                $('h2#best').html(bestTotal).prettynumber({delimiter : ','});
                $('h2#likely').html(likelyTotal).prettynumber({delimiter : ','});
            });

            $('[rel="clickover"]').clickover() ;
            $('[rel="clickoverTop"]').clickover({placement: "top"}) ;
            $('[rel="clickoverBottom"]').clickover({placement: "bottom"})
            $('td .format').prettynumber({delimiter : ','}).prepend('$');
            $('td label.original').prettynumber({delimiter : ','}).prepend('€');

            // Function to get the Max value in Array
            Array.max = function( array ){
                return Math.max.apply( Math, array );
            };

            // Function to get the Min value in Array
            Array.min = function( array ){
                return Math.min.apply( Math, array );
            };

            var likelyWidths= $('.likely .converted').map(function() {
                return $(this).width();
            }).get();

            var likelyLabelWidths= $('.likely label.original').map(function() {
                return $(this).width();
            }).get();

            var bestWidths= $('.best .converted').map(function() {
                return $(this).width();
            }).get();

            var bestLabelWidths= $('.best label.original').map(function() {
                return $(this).width();
            }).get();

            $('.likely .converted').width(Array.max(likelyWidths));
            $('.likely label.original').width(Array.max(likelyLabelWidths));
            $('.best .converted').width(Array.max(bestWidths));
            $('.best label.original').width(Array.max(bestLabelWidths));
        },
        "fnFooterCallback": function ( nFoot, aaData, iStart, iEnd, aiDisplay ) {
            var quotaTotal = 0;
            var bestAdTotal = 0;
            var bestTotal = 0;
            var likelyTotal = 0;
            var likelyAdTotal = 0;
            for ( var i=iStart ; i<iEnd ; i++ )
            {
                //amountTotal += parseFloat(aaData[ aiDisplay[i] ][5].replace(/<span class='format'>/,'').replace(/<\/span>/,''));
                quotaTotal += parseFloat(aaData[ aiDisplay[i] ][3].replace(/<div class='sugareditable'><span class='format'>/,'').replace(/<\/span><\/div>/,''));
                bestAdTotal += parseFloat(aaData[ aiDisplay[i] ][5].replace(/<div class='sugareditable'><span class='format'>/,'').replace(/<\/span><\/div>/,''));
                likelyAdTotal += parseFloat(aaData[ aiDisplay[i] ][7].replace(/<div class='sugareditable'><span class='format'>/,'').replace(/<\/span><\/div>/,''));
                bestTotal += parseFloat(aaData[ aiDisplay[i] ][4].replace(/<label class='original'>[0-9]*<\/label>/,'').replace(/<div class='converted'><div class='sugareditable'><span class='click format'>/,'').replace(/<\/span><\/div><\/div>/,''));
                likelyTotal += parseFloat(aaData[ aiDisplay[i] ][6].replace(/<label class='original'>[0-9]*<\/label>/,'').replace(/<div class='converted'><div class='sugareditable'><span class='click format'>/,'').replace(/<\/span><\/div><\/div>/,''));
            }

            /* Modify the footer row to match what we want */
            // var nCells = nFoot.getElementsByTagName('th');
            var foot = $(oTable).children('tfoot')[0];
            var trs = $(foot).children('tr');
            var overall = $(trs)[0];
            //var included = $(trs)[0];
            var overallCells =  $(overall).children('th');
            //var includedCells =  $(included).children('th');
            //console.log($(overall).children('th'))
            //$(overallCells[1]).html(amountTotal).prettynumber({delimiter : ','});
            $(overallCells[1]).html(quotaTotal).prettynumber({delimiter : ','}).prepend("$");
            $(overallCells[2]).html(bestTotal).prettynumber({delimiter : ','}).prepend("$");
            $(overallCells[3]).html(bestAdTotal).prettynumber({delimiter : ','}).prepend("$");
            $(overallCells[4]).html(likelyTotal).prettynumber({delimiter : ','}).prepend("$");
            $(overallCells[5]).html(likelyAdTotal).prettynumber({delimiter : ','}).prepend("$");
            //$(includedCells[1]).html(amountTotal).prettynumber({delimiter : ','});
            //$(includedCells[1]).html(bestTotal).prettynumber({delimiter : ','});
            //$(includedCells[2]).html(likelyTotal).prettynumber({delimiter : ','});

            $('h2#best').html(bestTotal).prettynumber({delimiter : ','}).prepend("$");
            $('h2#likely').html(likelyTotal).prettynumber({delimiter : ','}).prepend("$");
            /* var minWidth =  $('.datapoint2').css("min-width").replace(/px/,'');
            var dp1Delta = ($('.datapoint1').width() - minWidth) - 20;
            var dp1Width = ($('.datapoint1').width());
            var dp2Delta = ($('.datapoint2').width() - minWidth);

            var dp2Right =   dp1Width + dp2Delta;
            $('.datapoint1').css('right',dp1Delta+"px")
            $('.datapoint2').css('right',dp2Right+"px")   */
        },
        "fnDrawCallback": function( oSettings ) {
            $('#example tbody td .click').editable('save.php',{
                //loadurl: "load.php",
                indicator : 'Saving ...',
                cssclass   : "editable",
                // onblur : "ignore",
                callback : function(value, settings) {
                    var val  = parseInt(value.replace(/,/,''));
                    //updateChart("data/forecast-json-update.js",forecast.chartObject);

                    var cellIndex = $(this).parent().parent()[0].cellIndex;
                    var rowIndex = $(this).parent().parent().parent()[0]._DT_RowIndex;

                    //console.log(forecast.chartObject.json)
                    oTable.fnUpdate( val, rowIndex, cellIndex);

                    var likely = $('h2#likely').html().replace(/,/,"")
                    //var json =  forecast.chartObject.json;
                    // var json = d3ChartContainer.datum();

                    // $(json.values).each( function(i) {
                    //     if(rowIndex == i) {
                    //         this.goalmarkervalue[1] =  val/1000;
                    //         this.goalmarkervaluelabel[1] =  val/1000+"K";
                    //     }
                    // });

                    // updateChart(forecast.chartObject);
                    oTable.fnDraw();
                    //return (value);
                }
            });
        }
    });

    // new FixedColumns( oTable, {
    //     "iLeftColumns": 0,
    //     "iRightColumns": 1
    // });

    $('span.click input').live({
        focus:function() {
            var divParent = $(this).parents('div')[0],
                divParentWidth = $(divParent).width();
            $(divParent).addClass('focus');
        },
        blur: function() {
            var divParent = $(this).parents('div')[0],
                divParentWidth = $(divParent).width();
            setTimeout(function(){
               $(divParent).removeClass('focus')
            }, 500)
        }
    });

    // sugareditable example

    var addEditIcon = function() {
        $("body").on("mouseenter","span.click", function() {
            $(this).before('<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>');
        });
        $("body").on("mouseleave","span.click", function() {
            $('span.edit-icon').remove();
        });
    };

    var removeEditIcon = function() {
        $("body").off('mouseenter',"span.click");
        $("body").off('mouseleave',"span.click");
    };

    addEditIcon();

    /*
    $('#edit-worksheet').click(function(){
        var ison = $('div.sugareditable').hasClass('edit-mode');
        if(ison) {
            $('div.sugareditable').removeClass('edit-mode');
            $(this).removeClass('down');
            showEditIcon();
        } else {
            $('div.sugareditable').addClass('edit-mode');
            $(this).addClass('down');
        }
    });
    */
    //$('.datatableInput ').dataTable({ aoColumnDefs: [{ bSortable: false, aTargets: [0,8]}],"bPaginate": false,"bFilter": true,"bInfo": false,"bAutoWidth": false });

    //init chart

    //chart types
    // $('.icon-play').parent().click(function(e){
    //   e.preventDefault();
    //   chartConfig["chartType"] = 'funnelChart';
    //     chartConfig["funnelType"] = 'basic';
    //   forecast = swapChart(chartId,'data/forecast-json-funnel.js',css,chartConfig);
    //   var that = this;
    //   $('#chartType .btn-group').children("a").each(function() {
    //     $(this).removeClass('active');
    //     $(that).addClass('active');
    //   });
    // });

    // $('.icon-signal').parent().click(function(e){
    //   e.preventDefault();
    //   chartConfig["chartType"] = 'barChart';
    //   chartConfig["barType"] = 'stacked';
    //   chartConfig["orientation"] = 'vertical';

    //   forecast = swapChart(chartId,'data/forecast-json-month.js',css,chartConfig);
    //   var that = this;
    //   $('#chartType .btn-group').children("a").each(function() {
    //     $(this).removeClass('active');
    //     $(that).addClass('active');
    //   });
    // });

    $("#date_filter").chosen().change(function(formData){
        var q = quarter(formData.target.value);
        oTable.fnDraw();
        chartFile =  'data/forecast-json-'+q+'-month.js';
        //forecast = swapChart(chartId,chartFile,css,chartConfig);
    }).ready(function(){
         var q = quarter($("#date_filter")[0].value);
        chartFile =  'data/forecast-json-'+q+'-month.js';
        //forecast = swapChart(chartId,chartFile,css,chartConfig);
    });

    $("#group_by").chosen().change(function(data){
        forecast = swapChart(chartId,'data/forecast-json-'+data.target.value+'.js',css,chartConfig);
    });

    $("#probability_filter").chosen().change(function(formData){
        //forecast = swapChart(chartId,'data/forecast-json-'+data.target.value+'.js',css,chartConfig);
        //console.log( oTable.fnFilter( data.target.value, 4 ))

        oTable.fnDraw();
        jQuery.ajax({
            url: "data/forecast-json-month.js?r=" + new Date().getTime(),
            dataType:"text",
            async: false,
            success: function(data) {

                if(data !== undefined && data != "No Data"){
                    var json = eval('('+data+')');

                    var likely = $('h2#likely').html().replace(/,/,"");
                    $(json.values).each( function(i)    {
                        var probability = this.probability;
                        var that = this;
                        // console.log(probability)
                        $.each(probability , function(j,prob) {
                            if(prob < formData.target.value)    {
                                that.values[j] = 0;
                                that.goalmarkervalue[1] =  likely/1000;
                                that.goalmarkervaluelabel[1] =  likely/1000+"K";
                            }
                        });
                    });
                    //forecast.chartObject.json = json;
                    //updateChart(forecast.chartObject);
                }
            }
        });
    });

    $("#people").jstree({
        "json_data" : {
            "data" : [
                {
                    "data" : "Parent"
                },
                {
                    "data" : "Sabra Khan",
                    "state" : "open",
                    "metadata" : { id : 1 },
                    "children" : [
                        {"data" : "Mark Gibson","metadata" : { id : 2 }},
                        {"data" : "James Joplin","metadata" : { id : 3 }},
                        {"data" : "Terrence Li","metadata" : { id : 4 }},
                        {"data" : "Amy McCray","metadata" : { id : 5 }, "state" : "closed"}
                    ]
                }
            ]
        },
        // "themes" : { "theme" : "default", "dots" : false },
        "plugins" : [ "json_data", "ui", "types"]
    })
    .bind("loaded.jstree", function () {
        // do stuff when tree is loaded
        $("#people").addClass("jstree-sugar");
        $("#people > ul").addClass("list");
        $("#people > ul > li:first-child").addClass("parent");
        $("#people > ul > li > a").addClass("jstree-clicked");
    })
    .bind("select_node.jstree", function (e, data) {
        console.log("load data for id: " + jQuery.data(data.rslt.obj[0], "id"));
        data.inst.toggle_node(data.rslt.obj);
    });


    $("#sales_stage_filter").chosen().change( function(formData){

        oTable.fnFilter( formData.target.value, 3 );

        jQuery.ajax({
            url: "data/forecast-json-month.js?r=" + new Date().getTime(),
            dataType:"text",
            async: false,
            success: function(data) {

                if(data !== undefined && data != "No Data"){
                    var json = eval('('+data+')');

                    var likely = $('h2#likely').html().replace(/,/,"");
                    $(json.values).each( function(i)    {
                        var sales_stage = this.sales_stage;
                        var that = this;
                        // console.log(probability)
                        $.each(sales_stage , function(j,stage) {
                            if(stage != formData.target.value && formData.target.value != "")    {
                                that.values[j] = 0;
                                that.goalmarkervalue[1] =  likely/1000;
                                that.goalmarkervaluelabel[1] =  likely/1000+"K";
                            }
                        });
                    });
                    //forecast.chartObject.json = json;
                    //updateChart(forecast.chartObject);
                }
            }
        });
    });

    $("#x-axis").chosen().change(function(data){
      //forecast = swapChart(chartId,'data/forecast-json-'+data.target.value+'.js',css,chartConfig);
    });
    $("#groupby").chosen().change(function(data){
      //forecast = swapChart(chartId,'data/forecast-json-q1-'+data.target.value+'.js',css,chartConfig);
    });
    $("#dataset").chosen().change(function(data){
        //forecast = swapChart(chartId,'data/forecast-json-q1-month'+data.target.value+'.js',css,chartConfig);
    });

    /*$("#sales_stage_filter_chzn input").on("focus",function(){
        var left = $('#sales_stage_filter_chzn .chzn-drop').css('left');

        //console.log(left)
        if(left == "-9000px") {
            $('#sales_stage_filter_chzn .chzn-drop').
            width(0)
        } else {
            $('#sales_stage_filter_chzn .chzn-drop').
            width(100).
            css("left","auto").
            css("right","0px")
        }
    });

    $("#sales_stage_filter_chzn .chzn-drop").on("click",function(){
        $(this).
        css("right","auto")
     });

    $("#sales_stage_filter_chzn").css("width","100%").append('<div class="indicator">Filter <span class="caret"></span></div>');
    $("#sales_stage_filter_chzn .chzn-drop").addClass('carettop');
    $("#date_filter_chzn .chzn-drop").addClass('carettop');           */
    //$("#date_filter_chzn .chzn-results").append('<li class="active-result divider"></li><li class="active-result">+ Add timeframe</li>');

    $("#setup_btn").on('click', function() {
        wizard.init({
            id: 'setup',
            modalUrl: "partial/setup1.html",
            className: 'setup',
            headerText: "Forecasting Setup",
            navMenu: new Array("1. Time Period","2. Categories","3. Range"),
            'onWizardStart': function () {
                $('#setup .start').live("click", function(){
                    $("#setup .manual").css("display","none");
                });
            },
            defaults: {
                startText: "Setup Wizard",
                'footer': function () {
                    return '<div class="modal-footer">' +
                            '<a href="#" class="btn btn-invisible btn-link pull-left cancel">'+ this.cancelText+'</a>' +
                            '<a class="btn back" href="#">'+ this.backText+'</a>' +
                            '<a class="btn btn-primary next" href="#">'+ this.nextText+'</a>' +
                            '<a class="btn btn-primary finish" href="#">'+ this.finishText+'</a>' +
                            '<a href="#" class="btn manual">Manual Setup</a>' +
                            '<a class="btn btn-primary start" href="#">'+ this.startText+'</a>' +
                            '</div>';
                }
            }
        });
    });

    $('.dropdown-menu .datasetOptions label.radio').each(function(index){
        $(this).click(function(e){
              $(this).toggleClass("checked");
            $('.dropdown-menu .datasetOptions label.radio').each(function(otherIndex){
                if(otherIndex != index) {
                    $(this).removeClass("checked");
                }
            });
        });
    });
});
