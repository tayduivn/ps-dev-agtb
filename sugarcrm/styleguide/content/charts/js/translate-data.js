
function translateDataToD3(json, chartType) {
    var data = [],
        value = 0,
        strUndefined = 'undefined';

    if (json.values.filter(function(d) { return d.values && d.values.length; }).length) {

        switch (chartType) {

            case 'barChart':
                data = chartConfig.barType === 'stacked' || chartConfig.barType === 'grouped' ?
                    json.label.map(function(d, i) {
                        return {
                            'key': d !== '' ? d : strUndefined,
                            'type': 'bar',
                            'values': json.values.map(function(e, j) {
                                return {
                                  'series': i,
                                  'x': j + 1,
                                  'y': parseFloat(e.values[i]) || 0,
                                  'y0': 0
                                };
                            })
                        };
                    }) :
                    json.values.map(function(d, i) {
                        return {
                            'key': d.values.length > 1 ? d.label : d.label[0] !== '' ? d.label[0] : strUndefined,
                            'type': 'bar',
                            'values': json.values.map(function(e, j) {
                                return {
                                  'series': i,
                                  'x': j + 1,
                                  'y': i === j ? e.values.length > 1 ? e.values.reduce(function(a, b) { return parseFloat(a) + parseFloat(b); }) : parseFloat(e.values[0]) : 0,
                                  'y0': 0
                                };
                            })
                        };
                    });
                break;

            case 'pieChart':
                data = json.values.map(function(d, i) {
                    var data = {
                        'key': [].concat(d.label)[0] !== '' ? [].concat(d.label)[0] : strUndefined,
                        'value': parseFloat(d.values.reduce(function(a, b) { return a + b; }, 0))
                    };
                    if (d.color !== undefined) {
                        data.color = d.color;
                    }
                    if (d.classes !== undefined) {
                        data.classes = d.classes;
                    }
                    return data;
                });
                break;

            case 'funnelChart':
                data = json.values.reverse().map(function(d, i) {
                    return {
                        'key': [].concat(d.label)[0] !== '' ? [].concat(d.label)[0] : strUndefined,
                        'values': [{
                          'series': i,
                          'label': d.valuelabels[0] ? d.valuelabels[0] : d.values[0],
                          'x': 0,
                          'y': parseFloat(d.values.reduce(function(a, b) { return a + b; }, 0)) || 0,
                          'y0': 0
                        }]
                    };
                });
                break;

            case 'lineChart':
                data = json.values.map(function(d, i) {
                    return {
                        'key': d.label !== '' ? d.label : strUndefined,
                        'values': d.values.map(function(e, j) {
                            return [j, parseFloat(e)];
                        })
                    };
                });
                break;

            case 'gaugeChart':
                value = json.values.shift().gvalue;
                var y0 = 0;

                data = json.values.map(function(d, i) {
                    var values = {
                        'key': d.label !== '' ? d.label : strUndefined,
                        'y': parseFloat(d.values[0]) + y0
                    };
                    y0 += parseFloat(d.values[0]);
                    return values;
                });
                break;
        }
    }

    return {
        'properties': {
            'title': json.properties[0].title,
            // bar group data (x-axis)
            'labels': !json.values.filter(function(d) { return d.values.length; }).length ? [] :
                json.values.map(function(d, i) {
                return {
                    'group': i + 1,
                    'l': d.label !== '' ? d.label : strUndefined
                };
            }),
            'values': chartType === 'gaugeChart' ?
                [{'group' : 1, 't': value}] :
                json.values.filter(function(d) { return d.values.length; }).length ?
                    json.values.map(function(d, i) {
                        return {
                            'group': i + 1,
                            't': d.values.reduce(function(a, b) {
                                return parseFloat(a) + parseFloat(b);
                            })
                        };
                    }) :
                    []
        },
        // series data
        'data': data
    };
}
