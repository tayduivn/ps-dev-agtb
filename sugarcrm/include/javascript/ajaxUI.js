/**
 * 
 * Find more about the scrolling function at
 * http://cubiq.org/iscroll
 *
 * Copyright (c) 2010 Matteo Spinelli, http://cubiq.org/
 * Released under MIT license
 * http://cubiq.org/dropbox/mit-license.txt
 * 
 * Version 3.7.1 - Last updated: 2010.10.08
 * 
 */
SUGAR.ajaxUI = {
    callback : function(o)
    {
        var cont;
        if (typeof window.onbeforeunload == "function")
            window.onbeforeunload = null;
        try{
            var r = YAHOO.lang.JSON.parse(o.responseText);
            cont = r.content;
            if (r.moduleList)
            {
                SUGAR.themes.setModuleTabs(r.moduleList);
            }
            if (r.title)
            {
                document.title = r.title.replace(/&raquo;/g, '>').replace(/&nbsp;/g, ' ');
            }
            //SUGAR.themes.setCurrentTab(r.menu);
            var c = document.getElementById("content");
            c.innerHTML = cont;
            SUGAR.util.evalScript(cont);
        } catch (e){
            document.body.innerHTML = o.responseText;
            SUGAR.util.evalScript(document.body.innerHTML);
        }
    },

    canAjaxLoadModule : function(module)
    {
        var bannedModules = ['Emails', 'Administration', 'ModuleBuilder'];
        // Mechanism to allow for overriding or adding to this list
        if(typeof(SUGAR.addAjaxBannedModules) != 'undefined'){
            bannedModules.concat(SUGAR.addAjaxBannedModules);
        }
        if(typeof(SUGAR.overrideAjaxBannedModules) != 'undefined'){
            bannedModules = SUGAR.overrideAjaxBannedModules;
        }
        return bannedModules.indexOf(module) == -1;
    },

    loadContent : function(url, params)
    {
        //Don't ajax load certain modules
        var module = /module=(\w+)/.exec(url)[1];
        if (module && SUGAR.ajaxUI.canAjaxLoadModule(module))
        {
            YAHOO.util.History.navigate('ajaxUILoc',  url);
        } else {
            window.location = url;
        }
    },

    go : function(url, params)
    {
        //Reset the EmailAddressWidget before loading a new page
        if (SUGAR.EmailAddressWidget){
            SUGAR.EmailAddressWidget.instances = {};
            SUGAR.EmailAddressWidget.count = {};
        }

        var module = /module=([^&]*)/.exec(url)[1];
        var loadLanguageJS = '';
        if(module && typeof(SUGAR.language.languages[module]) == 'undefined'){
            loadLanguageJS = '&loadLanguageJS=1';
        }

        if (!/action=ajaxui/.exec(window.location))
            window.location = "index.php?action=ajaxui#ajaxUILoc=" + encodeURIComponent(url);
        else {
            YAHOO.util.Connect.asyncRequest('GET', url + '&ajax_load=1' + loadLanguageJS, {
                success: SUGAR.ajaxUI.callback
            });
        }
    },

    submitForm : function(formname, params)
    {
        var SA = SUGAR.ajaxUI;
        //Reset the EmailAddressWidget before loading a new page
        if (SUGAR.EmailAddressWidget){
            SUGAR.EmailAddressWidget.instances = {};
            SUGAR.EmailAddressWidget.count = {};
        }
        //Don't ajax load certain modules
        var form = YAHOO.util.Dom.get(formname) || document.forms[formname];
        if (SA.canAjaxLoadModule(form.module.value))
        {
            YAHOO.util.Connect.setForm(form);
            YAHOO.util.Connect.asyncRequest('POST', 'index.php?ajax_load=1', {
                success: SA.callback
            });
            return true;
        } else {
            // window.location = url;
            form.submit();
            return false;
        }
    },
    firstLoad : function()
    {
        //Setup Browser History
        var url = YAHOO.util.History.getBookmarkedState('ajaxUILoc');
        url = url ? url : 'index.php?module=Home&action=index';

        YAHOO.util.History.register('ajaxUILoc', url, SUGAR.ajaxUI.go);
        YAHOO.util.History.initialize("ajaxUI-history-field", "ajaxUI-history-iframe");
        SUGAR.ajaxUI.hist_loaded = true;
        SUGAR.ajaxUI.go(url);
    }
};