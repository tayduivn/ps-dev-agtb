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
        "<h3>{{name}}</h3>" +
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
        "<h3>{{name}}</h3>" +
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
        "SUBPANEL VIEW!!!!"

};