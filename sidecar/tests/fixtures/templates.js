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
        "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
        "<form name='{{name}}' class='well'>" +
            "{{#each meta.buttons}}" +
                "{{sugar_field ../context ../this ../model}}" +
            "{{/each}}" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "{{#each fields}}" +
                "<div>{{sugar_field ../../context ../../this}}</div>" +
            "{{/each}}" +
            "</div>" +
        "{{/each}}</form>",
    "editView" :
        "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
        "<form name='{{name}}' class='well'>" +
        "{{#each meta.buttons}}" +
            "{{sugar_field ../context ../this ../model}}" +
        "{{/each}}" +
        "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "{{#each fields}}" +
                "<div>{{sugar_field ../../context ../../this}}</div>" +
            "{{/each}}" +
            "</div>" +
        "{{/each}}</form>",
    "subpanelView" :
        "",
    "listView" :
        '<h3 class="view_title">{{context.state.module}}</h3>' +
        "{{#each meta.panels}}" +
            '<div class="{{../name}} panel hero-unit">' +
            '<table class="table table-bordered table-striped"><thead><tr>' +
            '{{#each fields}}' +
                '<th width="{{width}}%">{{label}}</th>' +
            '{{/each}}' +
            '</tr></thead><tbody>' +
            '{{#each ../context.state.collection.models}}' +
                '<tr name="{{beanType}}_{{attributes.id}}">' +
                '{{#each ../fields}}' +
                    // SugarField requires the current context, field name, and the current bean in the context
                    // since we are pulling from the collection rather than the default bean in the context
                    '<td>{{sugar_field ../../../context ../../../this ../this}}</td>' +
                '{{/each}}' +
                '</tr>' +
            '{{/each}}' +
            '</tbody></table>' +
        '{{/each}}'
};