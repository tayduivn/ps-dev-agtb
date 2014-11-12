function ShowLog(app, code) {
    var pmseInboxUrl = app.api.buildFileURL({
        module: 'pmse_Inbox',
        id: code,
        field: 'id'
    }, {cleanCache: true});
    App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
    viewImage(pmseInboxUrl, code);
}

function viewImage(url, code){
    var f, w, hp, img, ih, iw, a;
    img = new Image();
    img.src = url;
    img.onload = function () {
        if (img.width < 760) {
            ih = img.height;
            iw = img.width;
        } else {
            ih = parseInt(img.height * (760 / img.width), 10);
            iw = 760;
        }
        a = '<img width="' + iw + '" src="' + img.src + '" />';
        hp = new HtmlPanel({
            source: a,
            scroll: ((ih + 45) > 400) ? true : false
        });

        w = new Window({
            width: iw + 40,
            height: ((ih + 45) < 400) ?  ih + 45 : 400,
            modal: true,
            title: translate('LBL_PMSE_TITLE_CASE') + ' # ' + code + ': ' + translate('LBL_PMSE_TITLE_CURRENT_STATUS')
        });
        w.addPanel(hp);
        w.show();
        App.alert.dismiss('upload');
    };
}
