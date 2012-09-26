
    //dummy data for D3 chart example
var mark_gibson_Q1 = {
  'properties': {
    'title': 'Mark Gibson Forecast for Q1 2012',
    'quota': 200,
    'labels': [
      {group: 1, l: "Jan"},
      {group: 2, l: "Feb"},
      {group: 3, l: "Mar"}
    ],
    'values': [
      {group: 1, t: 110},
      {group: 2, t: 220},
      {group: 3, t: 260}
    ]
  },
  'data': [
    {
        'key': 'Qualified',
        'type': 'bar',
        'values': [
            {series: 0, x: 1, y: 50,  y0: 0},
            {series: 0, x: 2, y: 80,  y0: 0},
            {series: 0, x: 3, y: 100, y0: 0}
        ]
    },
    {
        'key': 'Proposal',
        'type': 'bar',
        'values': [
            {series: 1, x: 1, y: 50,  y0:  50},
            {series: 1, x: 2, y: 80,  y0:  80},
            {series: 1, x: 3, y: 100, y0: 100}
        ]
    },
    {
        'key': 'Negotiation',
        'type': 'bar',
        'values': [
            {series: 2, x: 1, y: 10, y0: 100},
            {series: 2, x: 2, y: 50, y0: 160},
            {series: 2, x: 3, y: 40, y0: 200}
        ]
    },
    {
        'key': 'Closed',
        'type': 'bar',
        'values': [
            {series: 3, x: 1, y: 0,  y0: 110},
            {series: 3, x: 2, y: 10, y0: 210},
            {series: 3, x: 3, y: 20, y0: 240}
        ]
    },
    {
        'key': 'Likely',
        'type': 'line',
        'values': [
            {x: 1, y: 120},
            {x: 2, y: 240},
            {x: 3, y: 290}
        ]
    }
  ]
};

