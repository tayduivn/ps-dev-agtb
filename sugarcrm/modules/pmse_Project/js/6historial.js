/*global translate, Window, Image, HtmlPanel*/
var App = (window.parent.App) ? window.parent.App : App;

function ShowLog(code) {
    var f, w, hp, img, ih, iw, a;
    // TODO the img object add a variable with the url path
    //console.log(code);
    img = new Image();
    //code = 'ce240f48-ff16-1b38-cb69-53593dd9c053';
    img.src = 'index.php?module=pmse_Inbox&action=showPNG&case=' + code;
    //console.log(img.src);
    img.onload = function () {
        if (img.width < 760) {
            ih = img.height;
            iw = img.width;
        } else {
            ih = parseInt(img.height * (760 / img.width), 10);
            iw = 760;
        }
        a = '<a href="index.php?module=pmse_Inbox&action=showPNG&case=' + code + '" target="_blank"><img width="' + iw + '" src="' + img.src + '" /></a>';
        hp = new HtmlPanel({
//            source: SBPM_HISTORICAL,
            source: a,
            scroll: ((ih + 45) > 400) ? true : false
        });

        w = new Window({
            width: iw + 40,
            height: ((ih + 45) < 400) ?  ih + 45 : 400,
            modal: true,
            title: 'Case' + ' # ' + code + ': ' +'Current Status'
        });

        w.addPanel(hp);
        w.show();
    };
}


function getGET(){
    var loc = App.controller.context.attributes.url;
    var getString = loc.split('?')[1];
    var GET = getString.split('&');
    var get = {};//this object will be filled with the key-value pairs and returned.

    for(var i = 0, l = GET.length; i < l; i++){
        var tmp = GET[i].split('=');
        get[tmp[0]] = unescape(decodeURI(tmp[1]));
    }

    return get;
}
urlCase = getGET();