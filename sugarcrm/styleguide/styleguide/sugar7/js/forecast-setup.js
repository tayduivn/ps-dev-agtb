/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: dashlets.js 24711 2007-07-27 01:51:57Z awu $





forecastSetup = function() {
    var tourModal;
    var step = 1;
    var navMenu = new Array("Time Period","Categories","Range","Variables","General");
    return {
        init: function(params) {

            tourModal = $('<div id="'+params.id+params.navigation+'"  class="modal setup">' +
                '</div>').modal({backdrop: true});

            var tourIdSel = "#"+params.id+params.navigation;

            $.ajax({
                url: params.modalUrl,
                success: function(data){
//                    console.log(data);
                    $(tourIdSel)
                        .append(header())
                        .append(navigation())
                        .append(data)
                        .append(footer(tourIdSel));


                    if(params.navigation == "tabs") {
                        $('.navigation').toggle();
                        $(tourIdSel).width(798);
                        $(tourIdSel+ " .modal-body").height(400);
                        $("#screen1").css("display","none");
                        $("#screen2").css("display","inline");
                        var navselector = ".nav-tabs a";
                    } else {
                        var navselector = ".breadcrumb a";

                    }

                   centerModal();



                    $(window).resize(function() {
                        centerModal();
                    });

                    $(navselector).each(function(index){
                        $(this).click(function(e){
                            var breadcrumb = index+2;
                            e.preventDefault();
                            $("#screen" + breadcrumb).css("display","inline");
                            $(this).parent().addClass("active");
                            $(navselector).each(function(otherIndex){
                                if(otherIndex != index) {
                                    $(this).parent().removeClass("active");
                                    $("#screen" + (otherIndex+2)).css("display","none");
                                }
                            });
//                            $("#breadcrumb"+step).toggleClass("current");
                            step = breadcrumb;

                            var numScreens = $(".screen").length;

                            if(step == numScreens) {
                                $("#finish").css("display","inline");
                                $("#next").css("display","none");
                            } else {
                                $("#finish").css("display","none");
                                $("#next").css("display","inline");
                            }


                        });
                    });



                    function centerModal() {
                        $(tourIdSel).css("left",$(window).width()/2 - $(tourIdSel).width()/2);
                        $(tourIdSel).css("margin-top",-$(tourIdSel).height()/2);
                    }

                    function header() {
                        var content = '<div class="modal-header">' +
                            ' <a class="close" data-dismiss="modal">×</a>' +
                            '<h3>Forecasting Setup</h3>' +
                            '</div>' ;
                        return content;
                    }

                    function navigation(){
                        var content = '<div class="navigation" style="display: none; width: 798px;">';

                            if(params.navigation == "breadcrumb") {
                                content += '<ul class="breadcrumb two">';
                            }  else {
                                content += '<ul class="nav nav-tabs">';
                            }


                            for(var i=0;i<navMenu.length;i++) {
                                var screenid = i+2;
                               content += '<li ';
                                   if(i==0) {
                                    content += 'class="active"';
                                   }

                                content += '><a href="" id="navigation'+screenid+'">'+navMenu[i]+'</a></li>';
                            }

                            content += '</ul>' +
                            '</div>';

                         return content;
                    }


                    function footer (tourIdSel) {

                        if(params.navigation =="breadcrumb") {
                            var footer = "<div class=\"modal-footer\">" +
                                "<a href=\"#\" class=\"btn btn-invisible\" id=\"cancel\">Cancel</a>" +
                                '<a class="btn btn-primary" id="next" href="#">Next</a>' +
                                '<a class="btn btn-primary" id="finish" href="#" style="display: none;">Finish</a>' +
                                "</div>";

                            $("#cancel").live("click",function(){
                                $(tourIdSel).modal('hide');
                            })

                            $('#next').live("click", function(){
                                $("#screen" + step).toggle();

                                step++;
                                if(step == 2) {
                                    $('.navigation').toggle();
                                    $(tourIdSel).width(798);
                                    $(tourIdSel+ " .modal-body").height(400);
                                    $("#navigation"+step).parent().toggleClass("active");
                                }
                                centerModal();
                                $("#screen" + step).toggle();
                                $("#navigation"+(step-1)).parent().toggleClass("active");
                                $("#navigation"+step).parent().toggleClass("active");

                                var numScreens = $(".screen").length;

                                if(step == numScreens) {
                                    $("#finish").css("display","inline");
                                    $("#next").css("display","none");
                                } else {
                                    $("#finish").css("display","none");
                                    $("#next").css("display","inline");
                                }

                            });
                        } else {
                            var footer = "<div class=\"modal-footer\">" +
                                "<a href=\"#\" class=\"btn btn-invisible\" id=\"cancel\">Cancel</a>" +
                                '<a class="btn btn-primary" id="finish" href="#">Save</a>' +
                                "</div>";

                                $("#cancel").live("click",function(){
                                    $(tourIdSel).modal('hide');
                                })
                        }

                        return footer;
                    }





                }
            });
        }




    };
}();
