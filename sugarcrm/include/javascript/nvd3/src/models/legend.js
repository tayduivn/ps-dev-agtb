nv.models.legend = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 15, left: 10},
      width = 0,
      height = 0,
      align = 'right',
      direction = 'ltr',
      position = 'start',
      radius = 6, // size of dot
      diameter = radius * 2, // diamter of dot plus stroke
      gutter = 10, // horizontal gap between keys
      spacing = 12, // vertical gap between keys
      textGap = 5, // gap between dot and label accounting for dot stroke
      equalColumns = true,
      showAll = false,
      showMenu = false,
      collapsed = false,
      rowsCount = 3, //number of rows to display if showAll = false
      enabled = false,
      strings = {close: 'Hide legend', type: 'Show legend'},
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      getKey = function(d) {
        return d.key.length > 0 || (!isNaN(parseFloat(d.key)) && isFinite(d.key)) ? d.key : 'undefined';
      },
      color = function(d, i) { return nv.utils.defaultColor()(d, i); },
      classes = function(d, i) { return ''; },
      dispatch = d3.dispatch('legendClick', 'legendMouseover', 'legendMouseout', 'toggleMenu', 'closeMenu');

  // Private Variables
  //------------------------------------------------------------

  var legendOpen = 0;

  var useScroll = false,
      scrollEnabled = true,
      scrollOffset = 0,
      overflowHandler = function(d) { return; };

  //============================================================

  function legend(selection) {

    selection.each(function(data) {

      var container = d3.select(this),
          containerWidth = width,
          containerHeight = height,
          keyWidths = [],
          legendHeight = 0,
          dropdownHeight = 0,
          type = '',
          inline = position === 'start' ? true : false,
          rtl = direction === 'rtl' ? true : false;

      if (!data || !data.length || !data.filter(function(d) { return !d.values || d.values.length; }).length) {
        return legend;
      }

      enabled = true;

      type = !data[0].type || data[0].type === 'bar' ? 'bar' : 'line';
      align = rtl ? align === 'left' ? 'right' : 'left' : align;

      //------------------------------------------------------------
      // Setup containers and skeleton of legend

      var wrap = container.selectAll('g.nv-chart-legend').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nv-chart-legend');

      wrapEnter.append('defs')
        .append('clipPath').attr('id', 'nv-edge-clip-' + id)
        .append('rect');

      var defs = wrap.select('defs');
      var clip = wrap.select('#nv-edge-clip-' + id + ' rect');

      wrapEnter
        .append('rect')
          .attr('class', 'nv-legend-background');
      var back = wrap.select('.nv-legend-background');
      var backFilter = nv.utils.dropShadow('legend_back_' + id, defs, {blur: 2});

      wrapEnter
        .append('text').attr('class', 'nv-legend-link');
      var link = wrap.select('.nv-legend-link');

      wrapEnter
        .append('g').attr('class', 'nv-legend-mask')
        .append('g').attr('class', 'nv-legend');
      var mask = wrap.select('.nv-legend-mask');
      var g = wrap.select('g.nv-legend');
      g .attr('transform', 'translate(0,0)');

      var series = g.selectAll('.nv-series')
            .data(function(d) { return d; }, function(d) { return d.key; });
      var seriesEnter = series.enter().append('g').attr('class', 'nv-series');
      series.exit().remove();

      clip
        .attr('x', 0.5)
        .attr('y', 0.5)
        .attr('width', 0)
        .attr('height', 0);

      back
        .attr('x', 0.5)
        .attr('y', 0.5)
        .attr('width', 0)
        .attr('height', 0)
        .style('opacity', 0)
        .style('pointer-events', 'all')
        .on('click', function(d, i) {
          d3.event.stopPropagation();
        });

      link
        .text(legendOpen === 1 ? legend.strings().close : legend.strings().open)
        .attr('text-anchor', align === 'left' ? rtl ? 'end' : 'start' : rtl ? 'start' : 'end')
        .attr('dy', '.36em')
        .attr('dx', 0)
        .style('opacity', 0)
        .on('click', function(d, i) {
          dispatch.toggleMenu(d, i);
        });

      seriesEnter
        .on('mouseover', function(d, i) {
          dispatch.legendMouseover(d, i);  //TODO: Make consistent with other event objects
        })
        .on('mouseout', function(d, i) {
          dispatch.legendMouseout(d, i);
        })
        .on('click', function(d, i) {
          dispatch.legendClick(d, i);
          d3.event.stopPropagation();
          d3.event.preventDefault();
        });

      if (type === 'bar') {

        seriesEnter.append('rect')
          .attr('x', -radius - 2)
          .attr('y', -radius - 2)
          .attr('width', radius * 2 + 4)
          .attr('height', radius * 2 + 4)
          .style('fill', '#FFE')
          .style('opacity', 0.1);

        seriesEnter.append('circle')
          .attr('r', radius)
          .style('stroke-width', 2);

        series.selectAll('circle')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('fill', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        seriesEnter.append('text')
          .attr('dy', inline ? '.36em' : '.71em');
        series.select('text')
          .text(getKey);

      } else {

        seriesEnter.append('circle')
          .attr('r', function(d, i) {
            return d.type === 'dash' ? 0 : radius;
          })
          .style('stroke-width', 2);
        seriesEnter.append('line')
          .attr('x0', 0)
          .attr('y0', 0)
          .attr('y1', 0)
          .style('stroke-width', '4px');
        seriesEnter.append('circle')
          .attr('r', function(d, i) {
            return d.type === 'dash' ? 0 : radius;
          })
          .style('stroke-width', 2);

        series.select('line')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        series.selectAll('circle')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('fill', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        seriesEnter.append('text')
          .attr('dy', inline ? '.36em' : '.71em')
          .attr('dx', 0);
        series.select('text')
          .text(getKey)
          .attr('text-anchor', position);

      }

      series.classed('disabled', function(d) {
        return d.disabled;
      });

      //------------------------------------------------------------

      //TODO: add ability to add key to legend
      //var label = g.append('text').text('Probability:').attr('class','nv-series-label').attr('transform','translate(0,0)');

      // store legend label widths
      legend.calculateWidth = function() {

        var padding = gutter + (inline ? diameter + textGap : 0);
        keyWidths = [];

        g.style('display', 'inline');

        series.select('text').each(function(d, i) {
          var textWidth = d3.select(this).node().getBBox().width;
          keyWidths.push(Math.max(Math.floor(textWidth) + padding, 50));
        });

        legend.width(d3.sum(keyWidths) - gutter);

        return legend.width();
      };

      legend.getLineHeight = function() {
        g.style('display', 'inline');
        var lineHeightBB = Math.floor(series.select('text').node().getBBox().height);
        return lineHeightBB;
      };

      legend.arrange = function(containerWidth) {

        if (keyWidths.length === 0) {
          this.calculateWidth();
        }

        var keys = keyWidths.length,
            rows = 1,
            cols = keys,
            columnWidths = [],
            keyPositions = [],
            maxWidth = containerWidth - margin.left - margin.right,
            maxRowWidth = 0,
            minRowWidth = 0,
            lineSpacing = spacing * (inline ? 1 : 0.6),
            textHeight = this.getLineHeight(),
            lineHeight = diameter + (inline ? 0 : textHeight) + lineSpacing,
            menuMargin = {top: 7, right: 7, bottom: 7, left: 7}, // account for stroke width
            xpos = 0,
            ypos = 0,
            i,
            mod,
            shift;

        if (equalColumns) {

          //keep decreasing the number of keys per row until
          //legend width is less than the available width
          while (cols > 0) {
            columnWidths = [];

            for (i = 0; i < keys; i += 1) {
              if (keyWidths[i] > (columnWidths[i % cols] || 0)) {
                columnWidths[i % cols] = keyWidths[i];
              }
            }

            if (d3.sum(columnWidths) - gutter < maxWidth) {
              break;
            }
            cols -= 1;
          }
          cols = cols || 1;

          rows = Math.ceil(keys / cols);
          maxRowWidth = d3.sum(columnWidths) - gutter;

          for (i = 0; i < keys; i += 1) {
            mod = i % cols;

            if (inline) {
              if (mod === 0) {
                xpos = rtl ? maxRowWidth : 0;
              } else {
                xpos += columnWidths[mod - 1] * (rtl ? -1 : 1);
              }
            } else {
              if (mod === 0) {
                xpos = (rtl ? maxRowWidth : 0) + (columnWidths[mod] - gutter) / 2 * (rtl ? -1 : 1);
              } else {
                xpos += (columnWidths[mod - 1] + columnWidths[mod]) / 2 * (rtl ? -1 : 1);
              }
            }

            ypos = Math.floor(i / cols) * lineHeight;
            keyPositions[i] = {x: xpos, y: ypos};
          }

        } else {

          if (rtl) {

            xpos = maxWidth;

            for (i = 0; i < keys; i += 1) {
              if (xpos - keyWidths[i] + gutter < 0) {
                maxRowWidth = Math.max(maxRowWidth, keyWidths[i] - gutter);
                xpos = maxWidth;
                if (i) {
                  rows += 1;
                }
              }
              if (xpos - keyWidths[i] + gutter > maxRowWidth) {
                maxRowWidth = xpos - keyWidths[i] + gutter;
              }
              keyPositions[i] = {x: xpos, y: (rows - 1) * (lineSpacing + diameter)};
              xpos -= keyWidths[i];
            }

          } else {

            xpos = 0;

            for (i = 0; i < keys; i += 1) {
              if (i && xpos + keyWidths[i] - gutter > maxWidth) {
                xpos = 0;
                rows += 1;
              }
              if (xpos + keyWidths[i] - gutter > maxRowWidth) {
                maxRowWidth = xpos + keyWidths[i] - gutter;
              }
              keyPositions[i] = {x: xpos, y: (rows - 1) * (lineSpacing + diameter)};
              xpos += keyWidths[i];
            }

          }

        }

        if (!showMenu && (showAll || rows <= rowsCount)) {

          legendOpen = 0;
          collapsed = false;
          useScroll = false;

          legend
            .width(margin.left + maxRowWidth + margin.right)
            .height(margin.top + rows * lineHeight - lineSpacing + margin.bottom);

          switch (align) {
            case 'left':
              shift = 0;
              break;
            case 'center':
              shift = (containerWidth - legend.width()) / 2;
              break;
            case 'right':
              shift = 0;
              break;
          }

          clip
            .attr('y', 0)
            .attr('width', legend.width())
            .attr('height', legend.height());

          back
            .attr('x', shift)
            .attr('width', legend.width())
            .attr('height', legend.height())
            .attr('rx', 0)
            .attr('ry', 0)
            .attr('filter', 'none')
            .style('display', 'inline')
            .style('opacity', 0);

          mask
            .attr('clip-path', 'none')
            .attr('transform', function(d, i) {
              var xpos = shift + margin.left + (inline ? radius * (rtl ? -1 : 1) : 0),
                  ypos = margin.top + menuMargin.top;
              return 'translate(' + xpos + ',' + ypos + ')';
            });

          g
            .style('opacity', 1)
            .style('display', 'inline');

          series
            .attr('transform', function(d, i) {
              var pos = keyPositions[i];
              return 'translate(' + pos.x + ',' + pos.y + ')';
            });

          series.selectAll('rect')
            .attr('x', -radius - gutter / 2)
            .attr('y', -radius - lineSpacing / 2)
            .attr('width', function(d, i) {
              // var index = d.series % columnWidths.length;
              // return columnWidths[index];
              return keyWidths[d.series];
            })
            .attr('height', radius * 2 + lineSpacing);
          series.selectAll('text')
            .attr('text-anchor', position)
            .attr('transform', function(d, i) {
              var xpos = inline ? (radius + textGap) * (rtl ? -1 : 1) : 0,
                  ypos = inline ? 0 : radius + 3;
              return 'translate(' + xpos + ',' + ypos + ')';
            });
          series.selectAll('circle')
            .attr('transform', function(d, i) {
              var xpos = inline || type === 'bar' ? 0 : radius * 3 * (i ? 1 : -1);
              return 'translate(' + xpos + ',0)';
            });
          series.selectAll('line')
            .attr('x1', function(d, i) {
              return d.type === 'dash' ? radius * 8 : radius * 6;
            })
            .attr('transform', function(d, i) {
              var xpos = radius * (d.type === 'dash' ? -4 : -3);
              return 'translate(' + xpos + ',0)';
            })
            .style('stroke-dasharray', function(d, i) {
              return d.type === 'dash' ? '8, 8' : '0,0';
            });

        } else {

          collapsed = true;
          useScroll = true;

          legend
            .width(menuMargin.left + d3.max(keyWidths) - gutter + menuMargin.right)
            .height(margin.top + diameter + margin.top); //don't use bottom here because we want vertical centering

          legendHeight = menuMargin.top + diameter * keys + spacing * (keys - 1) + menuMargin.bottom;
          dropdownHeight = Math.min(containerHeight - legend.height(), legendHeight);

          clip
            .attr('x', 0.5 - menuMargin.top - radius)
            .attr('y', 0.5 - menuMargin.top - radius)
            .attr('width', legend.width())
            .attr('height', dropdownHeight);

          back
            .attr('x', 0.5)
            .attr('y', 0.5 + legend.height())
            .attr('width', legend.width())
            .attr('height', dropdownHeight)
            .attr('rx', 2)
            .attr('ry', 2)
            .attr('filter', backFilter)
            .style('opacity', legendOpen * 0.9)
            .style('display', legendOpen ? 'inline' : 'none');

          link
            .attr('transform', function(d, i) {
              var xpos = align === 'left' ? 0.5 : 0.5 + legend.width(),
                  ypos = margin.top + radius;
              return 'translate(' + xpos + ',' + ypos + ')';
            })
            .style('opacity', 1);

          mask
            .attr('clip-path', 'url(#nv-edge-clip-' + id + ')')
            .attr('transform', function(d, i) {
              var xpos = menuMargin.left + radius,
                  ypos = legend.height() + menuMargin.top + radius;
              return 'translate(' + xpos + ',' + ypos + ')';
            });

          g
            .style('opacity', legendOpen)
            .style('display', legendOpen ? 'inline' : 'none')
            .attr('transform', function(d, i) {
              var xpos = rtl ? d3.max(keyWidths) - gutter - diameter : 0;
              return 'translate(' + xpos + ',0)';
            });

          series
            .attr('transform', function(d, i) {
              var ypos = i * (diameter + spacing);
              return 'translate(0,' + ypos + ')';
            });
          series
            .selectAll('circle')
              .attr('transform', '');
          series
            .selectAll('line')
              .attr('x1', 16)
              .attr('transform', 'translate(-8,0)') //TODO: why is this hard coded?
              .style('stroke-dasharray', 'inherit');
          series
            .selectAll('text')
              .attr('text-anchor', 'start')
              .attr('transform', function(d, i) {
                var xpos = (radius + textGap) * (rtl ? -1 : 1);
                return 'translate(' + xpos + ',0)';
            });

        }
        //------------------------------------------------------------
        // Enable scrolling
        if (scrollEnabled) {
          var diff = dropdownHeight - legendHeight;

          var assignScrollEvents = function(enable) {
            var pan = enable ? panLegend : null;
            var zoom = d3.behavior.zoom()
                  .on('zoom', pan);
            var drag = d3.behavior.drag()
                  .origin(function(d) { return d; })
                  .on('drag', pan);

            back.call(zoom);
            g.call(zoom);

            back.call(drag);
            g.call(drag);
          };

          var panLegend = function() {
            var distance = 0,
                overflowDistance = 0,
                translate = '',
                x = 0,
                y = 0;

            // don't fire on events other than zoom and drag
            // we need click for handling legend toggle
            if (d3.event) {
              if (d3.event.type === 'zoom') {
                x = d3.event.sourceEvent.deltaX || 0;
                y = d3.event.sourceEvent.deltaY || 0;
                distance = (Math.abs(x) > Math.abs(y) ? x : y) * -1;
              } else if (d3.event.type === 'drag') {
                x = d3.event.dx || 0;
                y = d3.event.dy || 0;
                distance = y;
              } else if (d3.event.type !== 'click') {
                return 0;
              }
              overflowDistance = (Math.abs(y) > Math.abs(x) ? y : 0);
            }

            // reset value defined in panMultibar();
            scrollOffset = Math.min(Math.max(scrollOffset + distance, diff), -1);
            translate = 'translate(0,' + scrollOffset + ')';

            if (scrollOffset + distance > 0 || scrollOffset + distance < diff) {
              overflowHandler(overflowDistance);
            }

            g.attr('transform', translate);
          };

          assignScrollEvents(useScroll);
        }

      };

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      function displayMenu() {
        back
          .style('opacity', legendOpen * 0.9)
          .style('display', legendOpen ? 'inline' : 'none');
        g
          .style('opacity', legendOpen)
          .style('display', legendOpen ? 'inline' : 'none');
        link
          .text(legendOpen === 1 ? legend.strings().close : legend.strings().open);
      }

      dispatch.on('toggleMenu', function(d) {
        d3.event.stopPropagation();
        legendOpen = 1 - legendOpen;
        displayMenu();
      });

      dispatch.on('closeMenu', function(d) {
        if (legendOpen === 1) {
          legendOpen = 0;
          displayMenu();
        }
      });

    });

    return legend;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  legend.dispatch = dispatch;

  legend.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    !== 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  !== 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom !== 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   !== 'undefined' ? _.left   : margin.left;
    return legend;
  };

  legend.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = Math.round(_);
    return legend;
  };

  legend.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = Math.round(_);
    return legend;
  };

  legend.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return legend;
  };

  legend.key = function(_) {
    if (!arguments.length) {
      return getKey;
    }
    getKey = _;
    return legend;
  };

  legend.color = function(_) {
    if (!arguments.length) {
      return color;
    }
    color = nv.utils.getColor(_);
    return legend;
  };

  legend.classes = function(_) {
    if (!arguments.length) {
      return classes;
    }
    classes = _;
    return legend;
  };

  legend.align = function(_) {
    if (!arguments.length) {
      return align;
    }
    align = _;
    return legend;
  };

  legend.position = function(_) {
    if (!arguments.length) {
      return position;
    }
    position = _;
    return legend;
  };

  legend.showAll = function(_) {
    if (!arguments.length) { return showAll; }
    showAll = _;
    return legend;
  };

  legend.showMenu = function(_) {
    if (!arguments.length) { return showMenu; }
    showMenu = _;
    return legend;
  };

  legend.collapsed = function(_) {
    return collapsed;
  };

  legend.rowsCount = function(_) {
    if (!arguments.length) {
      return rowsCount;
    }
    rowsCount = _;
    return legend;
  };

  legend.spacing = function(_) {
    if (!arguments.length) {
      return spacing;
    }
    spacing = _;
    return legend;
  };

  legend.gutter = function(_) {
    if (!arguments.length) {
      return gutter;
    }
    gutter = _;
    return legend;
  };

  legend.radius = function(_) {
    if (!arguments.length) {
      return radius;
    }
    radius = _;
    return legend;
  };

  legend.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    strings = _;
    return legend;
  };

  legend.equalColumns = function(_) {
    if (!arguments.length) {
      return equalColumns;
    }
    equalColumns = _;
    return legend;
  };

  legend.enabled = function(_) {
    if (!arguments.length) {
      return enabled;
    }
    enabled = _;
    return legend;
  };

  legend.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return legend;
  };

  //============================================================


  return legend;
};
