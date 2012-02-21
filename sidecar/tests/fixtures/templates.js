/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */

fixtures = typeof(fixtures) == "object" ? fixtures : {};

fixtures.templates = {
    "detailView" :
        "<h3 class=\"view_title\">{{name}}</h3>" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "<form name='{{name}}' class='well'>" +
            "{{#each fields}}" +
                "<div>{{sugar_field ../../context ../../name}}</div>" +
            "{{/each}}" +
            "</form></div>" +
        "{{/each}}",
    "editView" :
        "<h3 class=\"view_title\">{{name}}</h3>" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "<form name='{{name}}' class='well'>" +
            "{{#each fields}}" +
                "<div>{{sugar_field ../../context ../../name}}</div>" +
            "{{/each}}" +
            "</form></div>" +
        "{{/each}}",
    "subpanelView" :
        "SUBPANEL VIEW!!!!",
    "listView" :
        '<h3 class="view_title">{{context.state.module}} {{name}}</h3>' +
        "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            '<table class="table table-bordered table-striped"><thead><tr>' +
            '{{#each fields}}' +
                '<th width="{{width}}%">{{label}}</th>' +
            '{{/each}}' +
            '</tr></thead><tbody>' +
            '{{#each ../context.state.collection.models}}' +
                '<tr>' +
                '{{#each ../fields}}' +
                    // SugarField requires the current context, field name, and the current bean in the context
                    // since we are pulling from the collection rather than the default bean in the context
                    '<td>{{sugar_field ../../../context ../../../name ../this}}</td>' +
                '{{/each}}' +
                '</tr>' +
            '{{/each}}' +
            '</tbody></table>' +
        '{{/each}}'
};