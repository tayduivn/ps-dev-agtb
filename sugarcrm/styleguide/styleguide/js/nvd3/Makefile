JS_FILES = \
	src/intro.js \
	src/core.js \
	src/tooltip.js \
	src/utils.js \
	src/models/axis.js \
	src/models/funnel.js \
	src/models/funnelChart.js \
	src/models/gauge.js \
	src/models/gaugeChart.js \
	src/models/legend.js \
	src/models/line.js \
	src/models/lineChart_sugar.js \
	src/models/multiBar_sugar.js \
	src/models/multiBarChart_sugar.js \
	src/models/paretoChart.js \
	src/models/paretoLegend.js \
	src/models/multiBarHorizontal_sugar.js \
	src/models/multiBarHorizontalChart_sugar.js \
	src/models/pie.js \
	src/models/pieChart.js \
	src/models/scatter.js \
	src/models/scatterChart.js \
	src/models/sparkline.js \
	src/models/sparklinePlus.js \
	src/models/stackedArea.js \
	src/models/stackedAreaChart_sugar.js \
	src/models/treemap.js \
	src/models/treemapChart.js \
	src/models/tree.js \
	src/models/bubble.js \
	src/models/bubbleChart.js \
	src/outro.js

JS_COMPILER = \
	uglifyjs

all: nv.d3.js nv.d3.min.js
nv.d3.js: $(JS_FILES)
nv.d3.min.js: $(JS_FILES)
d3.min: d3.v2.min.js

nv.d3.js: Makefile
	rm -f $@
	cat $(filter %.js,$^) >> $@

nv.d3.min.js: Makefile
	rm -f ../../../assets/js/nvd3/$@
	cat $(filter %.js,$^) | $(JS_COMPILER) >> ../../../assets/js/nvd3/$@

d3.v2.min.js: Makefile
	rm -f ../../../assets/js/nvd3/lib/$@
	cat lib/d3.v2.js | $(JS_COMPILER) >> ../../../assets/js/nvd3/lib/$@

clean:
	rm -rf nv.d3.js ../../../assets/js/nvd3/nv.d3.min.js


