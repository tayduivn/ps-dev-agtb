(function($) {
    var b = {
        init: function(c) {
            var d = $.extend({
                top:0,
                height: 50,
                instruction: "Pull down to refresh...",
                message: "Release to refresh...",
                confirmation: "Refreshing..."
            }, c);

            var e = this, f = 0, g = 2, h = 0, i = d.height, j = 0, k = 0;

            this.find('.items').eq(0).before('<div id="pull-to-refresh" style="z-index: 1;position: relative;top:'+ parseInt(d.top) +'px;">' +
                '<div style="text-align: center;position: relative;top:15px;display: none;">' + d.instruction + "</div></div>");

            b.$controlEl = $("#pull-to-refresh");

            var txtContainer = b.$controlEl.find("div");

            b.marginAdjustment = parseInt(b.$controlEl.css("margin-top"));

            i -= b.marginAdjustment;

            b.$controlEl.css({height: i, marginTop: -i});
            b.marginTop = parseInt(b.$controlEl.css("margin-top"));
            b.marginTopAbs = Math.abs(b.marginTop);

            this.on("touchstart.ptr", function(b) {
                txtContainer.show();
                if (b.targetTouches.length == 1) {
                    f = b.targetTouches[0].pageY;
                }
                j = e.offset().top;
                k = e.get(0).scrollTop;
            });

            this.on("touchend.ptr", function() {
                if (h > b.marginTopAbs + b.marginAdjustment) {
                    b.$controlEl.css({'margin-top': b.marginAdjustment, 'padding-top': 0});
                    txtContainer.text(d.confirmation);
                    d.callback();

                } else {
                    b.$controlEl.css({'margin-top': b.marginTop, 'padding-top': 0});
                }
                h = 0;
            });

            this.on("touchmove.ptr", function(a) {

                if (a.targetTouches.length == 1) {

                    var c = a.targetTouches[0].pageY;

                    if (j >= k) {
                        if (c > f) {
                            h = Math.round((c - f) / g);
                            //console.log('c:' + c + ' j:' + j + ' k:' + k + ' f:' + f + ' h:' + h);
                            if (h < b.marginTopAbs && b.marginTop + h < b.marginAdjustment) {
                                b.$controlEl.css('margin-top', b.marginTop + h);
                                txtContainer.text(d.instruction);
                            } else {
                                b.$controlEl.css({'margin-top': b.marginAdjustment, 'padding-top': h - b.marginTopAbs + Math.abs(b.marginAdjustment)});
                                txtContainer.text(d.message);
                            }
                            return false;
                        }
                    }
                }
            })

        },

        hide: function() {
            b.$controlEl.css({'margin-top': b.marginTop, 'padding-top': b.marginTop});
        }
    };

    $.fn.pullToRefresh = function(c) {
        if (b[c]) {
            return b[c].apply(this, Array.prototype.slice.call(arguments, 1))
        } else if (typeof c === "object" || !c) {
            return b.init.apply(this, arguments);
        }
    }

})(Zepto);
