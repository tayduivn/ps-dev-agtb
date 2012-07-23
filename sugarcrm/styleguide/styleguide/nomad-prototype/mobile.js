//     Zepto.js
//     (c) 2010-2012 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.

var Zepto = (function() {
  var undefined, key, $$, classList, emptyArray = [], slice = emptyArray.slice,
    document = window.document,
    elementDisplay = {}, classCache = {},
    getComputedStyle = document.defaultView.getComputedStyle,
    cssNumber = { 'column-count': 1, 'columns': 1, 'font-weight': 1, 'line-height': 1,'opacity': 1, 'z-index': 1, 'zoom': 1 },
    fragmentRE = /^\s*<(\w+)[^>]*>/,
    elementTypes = [1, 9, 11],
    adjacencyOperators = [ 'after', 'prepend', 'before', 'append' ],
    table = document.createElement('table'),
    tableRow = document.createElement('tr'),
    containers = {
      'tr': document.createElement('tbody'),
      'tbody': table, 'thead': table, 'tfoot': table,
      'td': tableRow, 'th': tableRow,
      '*': document.createElement('div')
    },
    readyRE = /complete|loaded|interactive/,
    classSelectorRE = /^\.([\w-]+)$/,
    idSelectorRE = /^#([\w-]+)$/,
    tagSelectorRE = /^[\w-]+$/;

  function isF(value) { return ({}).toString.call(value) == "[object Function]" }
  function isO(value) { return value instanceof Object }
  function isA(value) { return value instanceof Array }
  function likeArray(obj) { return typeof obj.length == 'number' }

  function compact(array) { return array.filter(function(item){ return item !== undefined && item !== null }) }
  function flatten(array) { return array.length > 0 ? [].concat.apply([], array) : array }
  function camelize(str)  { return str.replace(/-+(.)?/g, function(match, chr){ return chr ? chr.toUpperCase() : '' }) }
  function dasherize(str){
    return str.replace(/::/g, '/')
           .replace(/([A-Z]+)([A-Z][a-z])/g, '$1_$2')
           .replace(/([a-z\d])([A-Z])/g, '$1_$2')
           .replace(/_/g, '-')
           .toLowerCase();
  }
  function uniq(array)    { return array.filter(function(item,index,array){ return array.indexOf(item) == index }) }

  function classRE(name){
    return name in classCache ?
      classCache[name] : (classCache[name] = new RegExp('(^|\\s)' + name + '(\\s|$)'));
  }

  function maybeAddPx(name, value) { return (typeof value == "number" && !cssNumber[dasherize(name)]) ? value + "px" : value; }

  function defaultDisplay(nodeName) {
    var element, display;
    if (!elementDisplay[nodeName]) {
      element = document.createElement(nodeName);
      document.body.appendChild(element);
      display = getComputedStyle(element, '').getPropertyValue("display");
      element.parentNode.removeChild(element);
      display == "none" && (display = "block");
      elementDisplay[nodeName] = display;
    }
    return elementDisplay[nodeName];
  }

  function fragment(html, name) {
    if (name === undefined) name = fragmentRE.test(html) && RegExp.$1;
    if (!(name in containers)) name = '*';
    var container = containers[name];
    container.innerHTML = '' + html;
    return slice.call(container.childNodes);
  }

  function Z(dom, selector){
    dom = dom || emptyArray;
    dom.__proto__ = Z.prototype;
    dom.selector = selector || '';
    return dom;
  }

  function $(selector, context){
    if (!selector) return Z();
    if (context !== undefined) return $(context).find(selector);
    else if (isF(selector)) return $(document).ready(selector);
    else if (selector instanceof Z) return selector;
    else {
      var dom;
      if (isA(selector)) dom = compact(selector);
      else if (elementTypes.indexOf(selector.nodeType) >= 0 || selector === window)
        dom = [selector], selector = null;
      else if (fragmentRE.test(selector))
        dom = fragment(selector.trim(), RegExp.$1), selector = null;
      else if (selector.nodeType && selector.nodeType == 3) dom = [selector];
      else dom = $$(document, selector);
      return Z(dom, selector);
    }
  }

  $.extend = function(target){
    slice.call(arguments, 1).forEach(function(source) {
      for (key in source) target[key] = source[key];
    })
    return target;
  }

  $.qsa = $$ = function(element, selector){
    var found;
    return (element === document && idSelectorRE.test(selector)) ?
      ( (found = element.getElementById(RegExp.$1)) ? [found] : emptyArray ) :
      (element.nodeType !== 1 && element.nodeType !== 9) ? emptyArray :
      slice.call(
        classSelectorRE.test(selector) ? element.getElementsByClassName(RegExp.$1) :
        tagSelectorRE.test(selector) ? element.getElementsByTagName(selector) :
        element.querySelectorAll(selector)
      );
  }

  function filtered(nodes, selector){
    return selector === undefined ? $(nodes) : $(nodes).filter(selector);
  }

  function funcArg(context, arg, idx, payload){
   return isF(arg) ? arg.call(context, idx, payload) : arg;
  }

  $.isFunction = isF;
  $.isObject = isO;
  $.isArray = isA;

  $.inArray = function(elem, array, i) {
		return emptyArray.indexOf.call(array, elem, i);
	}

  $.map = function(elements, callback) {
    var value, values = [], i, key;
    if (likeArray(elements))
      for (i = 0; i < elements.length; i++) {
        value = callback(elements[i], i);
        if (value != null) values.push(value);
      }
    else
      for (key in elements) {
        value = callback(elements[key], key);
        if (value != null) values.push(value);
      }
    return flatten(values);
  }

  $.each = function(elements, callback) {
    var i, key;
    if (likeArray(elements))
      for(i = 0; i < elements.length; i++) {
        if(callback.call(elements[i], i, elements[i]) === false) return elements;
      }
    else
      for(key in elements) {
        if(callback.call(elements[key], key, elements[key]) === false) return elements;
      }
    return elements;
  }

  $.fn = {
    forEach: emptyArray.forEach,
    reduce: emptyArray.reduce,
    push: emptyArray.push,
    indexOf: emptyArray.indexOf,
    concat: emptyArray.concat,
    map: function(fn){
      return $.map(this, function(el, i){ return fn.call(el, i, el) });
    },
    slice: function(){
      return $(slice.apply(this, arguments));
    },
    ready: function(callback){
      if (readyRE.test(document.readyState)) callback($);
      else document.addEventListener('DOMContentLoaded', function(){ callback($) }, false);
      return this;
    },
    get: function(idx){ return idx === undefined ? slice.call(this) : this[idx] },
    size: function(){ return this.length },
    remove: function () {
      return this.each(function () {
        if (this.parentNode != null) {
          this.parentNode.removeChild(this);
        }
      });
    },
    each: function(callback){
      this.forEach(function(el, idx){ callback.call(el, idx, el) });
      return this;
    },
    filter: function(selector){
      return $([].filter.call(this, function(element){
        return element.parentNode && $$(element.parentNode, selector).indexOf(element) >= 0;
      }));
    },
    end: function(){
      return this.prevObject || $();
    },
    andSelf:function(){
      return this.add(this.prevObject || $())
    },
    add:function(selector,context){
      return $(uniq(this.concat($(selector,context))));
    },
    is: function(selector){
      return this.length > 0 && $(this[0]).filter(selector).length > 0;
    },
    not: function(selector){
      var nodes=[];
      if (isF(selector) && selector.call !== undefined)
        this.each(function(idx){
          if (!selector.call(this,idx)) nodes.push(this);
        });
      else {
        var excludes = typeof selector == 'string' ? this.filter(selector) :
          (likeArray(selector) && isF(selector.item)) ? slice.call(selector) : $(selector);
        this.forEach(function(el){
          if (excludes.indexOf(el) < 0) nodes.push(el);
        });
      }
      return $(nodes);
    },
    eq: function(idx){
      return idx === -1 ? this.slice(idx) : this.slice(idx, + idx + 1);
    },
    first: function(){ var el = this[0]; return el && !isO(el) ? el : $(el) },
    last: function(){ var el = this[this.length - 1]; return el && !isO(el) ? el : $(el) },
    find: function(selector){
      var result;
      if (this.length == 1) result = $$(this[0], selector);
      else result = this.map(function(){ return $$(this, selector) });
      return $(result);
    },
    closest: function(selector, context){
      var node = this[0], candidates = $$(context || document, selector);
      if (!candidates.length) node = null;
      while (node && candidates.indexOf(node) < 0)
        node = node !== context && node !== document && node.parentNode;
      return $(node);
    },
    parents: function(selector){
      var ancestors = [], nodes = this;
      while (nodes.length > 0)
        nodes = $.map(nodes, function(node){
          if ((node = node.parentNode) && node !== document && ancestors.indexOf(node) < 0) {
            ancestors.push(node);
            return node;
          }
        });
      return filtered(ancestors, selector);
    },
    parent: function(selector){
      return filtered(uniq(this.pluck('parentNode')), selector);
    },
    children: function(selector){
      return filtered(this.map(function(){ return slice.call(this.children) }), selector);
    },
    siblings: function(selector){
      return filtered(this.map(function(i, el){
        return slice.call(el.parentNode.children).filter(function(child){ return child!==el });
      }), selector);
    },
    empty: function(){ return this.each(function(){ this.innerHTML = '' }) },
    pluck: function(property){ return this.map(function(){ return this[property] }) },
    show: function(){
      return this.each(function() {
        this.style.display == "none" && (this.style.display = null);
        if (getComputedStyle(this, '').getPropertyValue("display") == "none") {
          this.style.display = defaultDisplay(this.nodeName)
        }
      })
    },
    replaceWith: function(newContent) {
      return this.each(function() {
        $(this).before(newContent).remove();
      });
    },
    wrap: function(newContent) {
      return this.each(function() {
        $(this).wrapAll($(newContent)[0].cloneNode(false));
      });
    },
    wrapAll: function(newContent) {
      if (this[0]) {
        $(this[0]).before(newContent = $(newContent));
        newContent.append(this);
      }
      return this;
    },
    unwrap: function(){
      this.parent().each(function(){
        $(this).replaceWith($(this).children());
      });
      return this;
    },
    hide: function(){
      return this.css("display", "none")
    },
    toggle: function(setting){
      return (setting === undefined ? this.css("display") == "none" : setting) ? this.show() : this.hide();
    },
    prev: function(){ return $(this.pluck('previousElementSibling')) },
    next: function(){ return $(this.pluck('nextElementSibling')) },
    html: function(html){
      return html === undefined ?
        (this.length > 0 ? this[0].innerHTML : null) :
        this.each(function (idx) {
          var originHtml = this.innerHTML;
          $(this).empty().append( funcArg(this, html, idx, originHtml) );
        });
    },
    text: function(text){
      return text === undefined ?
        (this.length > 0 ? this[0].textContent : null) :
        this.each(function(){ this.textContent = text });
    },
    attr: function(name, value){
      var res;
      return (typeof name == 'string' && value === undefined) ?
        (this.length == 0 ? undefined :
          (name == 'value' && this[0].nodeName == 'INPUT') ? this.val() :
          (!(res = this[0].getAttribute(name)) && name in this[0]) ? this[0][name] : res
        ) :
        this.each(function(idx){
          if (isO(name)) for (key in name) this.setAttribute(key, name[key])
          else this.setAttribute(name, funcArg(this, value, idx, this.getAttribute(name)));
        });
    },
    removeAttr: function(name) {
      return this.each(function() { this.removeAttribute(name); });
    },
    data: function(name, value){
      return this.attr('data-' + name, value);
    },
    val: function(value){
      return (value === undefined) ?
        (this.length > 0 ? this[0].value : null) :
        this.each(function(idx){
          this.value = funcArg(this, value, idx, this.value);
        });
    },
    offset: function(){
      if(this.length==0) return null;
      var obj = this[0].getBoundingClientRect();
      return {
        left: obj.left + window.pageXOffset,
        top: obj.top + window.pageYOffset,
        width: obj.width,
        height: obj.height
      };
    },
    css: function(property, value){
      if (value === undefined && typeof property == 'string') {
        return(
          this.length == 0
            ? undefined
            : this[0].style[camelize(property)] || getComputedStyle(this[0], '').getPropertyValue(property)
        );
      }
      var css = '';
      for (key in property) css += dasherize(key) + ':' + maybeAddPx(key, property[key]) + ';';
      if (typeof property == 'string') css = dasherize(property) + ":" + maybeAddPx(property, value);
      return this.each(function() { this.style.cssText += ';' + css });
    },
    index: function(element){
      return element ? this.indexOf($(element)[0]) : this.parent().children().indexOf(this[0]);
    },
    hasClass: function(name){
      if (this.length < 1) return false;
      else return classRE(name).test(this[0].className);
    },
    addClass: function(name){
      return this.each(function(idx) {
        classList = [];
        var cls = this.className, newName = funcArg(this, name, idx, cls);
        newName.split(/\s+/g).forEach(function(klass) {
          if (!$(this).hasClass(klass)) {
            classList.push(klass)
          }
        }, this);
        classList.length && (this.className += (cls ? " " : "") + classList.join(" "))
      });
    },
    removeClass: function(name){
      return this.each(function(idx) {
        if(name === undefined)
          return this.className = '';
        classList = this.className;
        funcArg(this, name, idx, classList).split(/\s+/g).forEach(function(klass) {
          classList = classList.replace(classRE(klass), " ")
        });
        this.className = classList.trim()
      });
    },
    toggleClass: function(name, when){
      return this.each(function(idx){
        var newName = funcArg(this, name, idx, this.className);
        (when === undefined ? !$(this).hasClass(newName) : when) ?
          $(this).addClass(newName) : $(this).removeClass(newName);
      });
    }
  };

  'filter,add,not,eq,first,last,find,closest,parents,parent,children,siblings'.split(',').forEach(function(property){
    var fn = $.fn[property];
    $.fn[property] = function() {
      var ret = fn.apply(this, arguments);
      ret.prevObject = this;
      return ret;
    }
  });

  ['width', 'height'].forEach(function(dimension){
    $.fn[dimension] = function(value) {
      var offset, Dimension = dimension.replace(/./, function(m) { return m[0].toUpperCase() });
      if (value === undefined) return this[0] == window ? window['inner' + Dimension] :
        this[0] == document ? document.documentElement['offset' + Dimension] :
        (offset = this.offset()) && offset[dimension];
      else return this.each(function(idx){
        var el = $(this);
        el.css(dimension, funcArg(this, value, idx, el[dimension]()));
      });
    }
  });

  function insert(operator, target, node) {
    var parent = (operator % 2) ? target : target.parentNode;
    parent && parent.insertBefore(node,
      !operator ? target.nextSibling :      // after
      operator == 1 ? parent.firstChild :   // prepend
      operator == 2 ? target :              // before
      null);                                // append
  }

  function traverseNode (node, fun) {
    fun(node);
    for (var key in node.childNodes) {
      traverseNode(node.childNodes[key], fun);
    }
  }

  adjacencyOperators.forEach(function(key, operator) {
    $.fn[key] = function(html){
      var nodes = isO(html) ? html : fragment(html);
      if (!('length' in nodes) || nodes.nodeType) nodes = [nodes];
      if (nodes.length < 1) return this;
      var size = this.length, copyByClone = size > 1, inReverse = operator < 2;

      return this.each(function(index, target){
        for (var i = 0; i < nodes.length; i++) {
          var node = nodes[inReverse ? nodes.length-i-1 : i];
          traverseNode(node, function (node) {
            if (node.nodeName != null && node.nodeName.toUpperCase() === 'SCRIPT' && (!node.type || node.type === 'text/javascript')) {
              window['eval'].call(window, node.innerHTML);
            }
          });
          if (copyByClone && index < size - 1) node = node.cloneNode(true);
          insert(operator, target, node);
        }
      });
    };

    var reverseKey = (operator % 2) ? key+'To' : 'insert'+(operator ? 'Before' : 'After');
    $.fn[reverseKey] = function(html) {
      $(html)[key](this);
      return this;
    };
  });

  Z.prototype = $.fn;

  return $;
})();

window.Zepto = Zepto;
'$' in window || (window.$ = Zepto);


//     Zepto.js
//     (c) 2010-2012 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.

(function($){
  var $$ = $.qsa, handlers = {}, _zid = 1, specialEvents={};

  specialEvents.click = specialEvents.mousedown = specialEvents.mouseup = specialEvents.mousemove = 'MouseEvents';

  function zid(element) {
    return element._zid || (element._zid = _zid++);
  }
  function findHandlers(element, event, fn, selector) {
    event = parse(event);
    if (event.ns) var matcher = matcherFor(event.ns);
    return (handlers[zid(element)] || []).filter(function(handler) {
      return handler
        && (!event.e  || handler.e == event.e)
        && (!event.ns || matcher.test(handler.ns))
        && (!fn       || handler.fn == fn)
        && (!selector || handler.sel == selector);
    });
  }
  function parse(event) {
    var parts = ('' + event).split('.');
    return {e: parts[0], ns: parts.slice(1).sort().join(' ')};
  }
  function matcherFor(ns) {
    return new RegExp('(?:^| )' + ns.replace(' ', ' .* ?') + '(?: |$)');
  }

  function eachEvent(events, fn, iterator){
    if ($.isObject(events)) $.each(events, iterator);
    else events.split(/\s/).forEach(function(type){ iterator(type, fn) });
  }

  function add(element, events, fn, selector, getDelegate, capture){
    capture = !!capture;
    var id = zid(element), set = (handlers[id] || (handlers[id] = []));
    eachEvent(events, fn, function(event, fn){
      var delegate = getDelegate && getDelegate(fn, event),
        callback = delegate || fn;
      var proxyfn = function (event) {
        var result = callback.apply(element, [event].concat(event.data));
        if (result === false) event.preventDefault();
        return result;
      };
      var handler = $.extend(parse(event), {fn: fn, proxy: proxyfn, sel: selector, del: delegate, i: set.length});
      set.push(handler);
      element.addEventListener(handler.e, proxyfn, capture);
    });
  }
  function remove(element, events, fn, selector){
    var id = zid(element);
    eachEvent(events || '', fn, function(event, fn){
      findHandlers(element, event, fn, selector).forEach(function(handler){
        delete handlers[id][handler.i];
        element.removeEventListener(handler.e, handler.proxy, false);
      });
    });
  }

  $.event = { add: add, remove: remove }

  $.fn.bind = function(event, callback){
    return this.each(function(){
      add(this, event, callback);
    });
  };
  $.fn.unbind = function(event, callback){
    return this.each(function(){
      remove(this, event, callback);
    });
  };
  $.fn.one = function(event, callback){
    return this.each(function(i, element){
      add(this, event, callback, null, function(fn, type){
        return function(){
          var result = fn.apply(element, arguments);
          remove(element, type, fn);
          return result;
        }
      });
    });
  };

  var returnTrue = function(){return true},
      returnFalse = function(){return false},
      eventMethods = {
        preventDefault: 'isDefaultPrevented',
        stopImmediatePropagation: 'isImmediatePropagationStopped',
        stopPropagation: 'isPropagationStopped'
      };
  function createProxy(event) {
    var proxy = $.extend({originalEvent: event}, event);
    $.each(eventMethods, function(name, predicate) {
      proxy[name] = function(){
        this[predicate] = returnTrue;
        return event[name].apply(event, arguments);
      };
      proxy[predicate] = returnFalse;
    })
    return proxy;
  }

  // emulates the 'defaultPrevented' property for browsers that have none
  function fix(event) {
    if (!('defaultPrevented' in event)) {
      event.defaultPrevented = false;
      var prevent = event.preventDefault;
      event.preventDefault = function() {
        this.defaultPrevented = true;
        prevent.call(this);
      }
    }
  }

  $.fn.delegate = function(selector, event, callback){
    var capture = false;
    if(event == 'blur' || event == 'focus'){
      if($.iswebkit)
        event = event == 'blur' ? 'focusout' : event == 'focus' ? 'focusin' : event;
      else
        capture = true;
    }

    return this.each(function(i, element){
      add(element, event, callback, selector, function(fn){
        return function(e){
          var evt, match = $(e.target).closest(selector, element).get(0);
          if (match) {
            evt = $.extend(createProxy(e), {currentTarget: match, liveFired: element});
            return fn.apply(match, [evt].concat([].slice.call(arguments, 1)));
          }
        }
      }, capture);
    });
  };
  $.fn.undelegate = function(selector, event, callback){
    return this.each(function(){
      remove(this, event, callback, selector);
    });
  }

  $.fn.live = function(event, callback){
    $(document.body).delegate(this.selector, event, callback);
    return this;
  };
  $.fn.die = function(event, callback){
    $(document.body).undelegate(this.selector, event, callback);
    return this;
  };

  $.fn.on = function(event, selector, callback){
    return selector === undefined || $.isFunction(selector) ?
      this.bind(event, selector) : this.delegate(selector, event, callback);
  };
  $.fn.off = function(event, selector, callback){
    return selector === undefined || $.isFunction(selector) ?
      this.unbind(event, selector) : this.undelegate(selector, event, callback);
  };

  $.fn.trigger = function(event, data){
    if (typeof event == 'string') event = $.Event(event);
    fix(event);
    event.data = data;
    return this.each(function(){ this.dispatchEvent(event) });
  };

  // triggers event handlers on current element just as if an event occurred,
  // doesn't trigger an actual event, doesn't bubble
  $.fn.triggerHandler = function(event, data){
    var e, result;
    this.each(function(i, element){
      e = createProxy(typeof event == 'string' ? $.Event(event) : event);
      e.data = data; e.target = element;
      $.each(findHandlers(element, event.type || event), function(i, handler){
        result = handler.proxy(e);
        if (e.isImmediatePropagationStopped()) return false;
      });
    });
    return result;
  };

  // shortcut methods for `.bind(event, fn)` for each event type
  ('focusin focusout load resize scroll unload click dblclick '+
  'mousedown mouseup mousemove mouseover mouseout '+
  'change select keydown keypress keyup error').split(' ').forEach(function(event) {
    $.fn[event] = function(callback){ return this.bind(event, callback) };
  });

  ['focus', 'blur'].forEach(function(name) {
    $.fn[name] = function(callback) {
      if (callback) this.bind(name, callback);
      else if (this.length) try { this.get(0)[name]() } catch(e){};
      return this;
    };
  });

  $.Event = function(type, props) {
    var event = document.createEvent(specialEvents[type] || 'Events'), bubbles = true;
    if (props) for (var name in props) (name == 'bubbles') ? (bubbles = !!props[name]) : (event[name] = props[name]);
    event.initEvent(type, bubbles, true, null, null, null, null, null, null, null, null, null, null, null, null);
    return event;
  };

})(Zepto);


//     Zepto.js
//     (c) 2010-2012 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.

(function($){
  var touch = {}, touchTimeout;

  function parentIfText(node){
    return 'tagName' in node ? node : node.parentNode;
  }

  function swipeDirection(x1, x2, y1, y2){
    var xDelta = Math.abs(x1 - x2), yDelta = Math.abs(y1 - y2);
    if (xDelta >= yDelta) {
      return (x1 - x2 > 0 ? 'Left' : 'Right');
    } else {
      return (y1 - y2 > 0 ? 'Up' : 'Down');
    }
  }

  var longTapDelay = 750;
  function longTap(){
    if (touch.last && (Date.now() - touch.last >= longTapDelay)) {
      touch.el.trigger('longTap');
      touch = {};
    }
  }

  $(document).ready(function(){
    $(document.body).bind('touchstart', function(e){
      var now = Date.now(), delta = now - (touch.last || now);
      touch.el = $(parentIfText(e.touches[0].target));
      touchTimeout && clearTimeout(touchTimeout);
      touch.x1 = e.touches[0].pageX;
      touch.y1 = e.touches[0].pageY;
      if (delta > 0 && delta <= 250) touch.isDoubleTap = true;
      touch.last = now;
      setTimeout(longTap, longTapDelay);
    }).bind('touchmove', function(e){
      touch.x2 = e.touches[0].pageX;
      touch.y2 = e.touches[0].pageY;
    }).bind('touchend', function(e){
      if (touch.isDoubleTap) {
        touch.el.trigger('doubleTap');
        touch = {};
      } else if (touch.x2 > 0 || touch.y2 > 0) {
        (Math.abs(touch.x1 - touch.x2) > 30 || Math.abs(touch.y1 - touch.y2) > 30)  &&
          touch.el.trigger('swipe') &&
          touch.el.trigger('swipe' + (swipeDirection(touch.x1, touch.x2, touch.y1, touch.y2)));
        touch.x1 = touch.x2 = touch.y1 = touch.y2 = touch.last = 0;
      } else if ('last' in touch) {
        touch.el.trigger('tap');

        touchTimeout = setTimeout(function(){
          touchTimeout = null;
          touch.el.trigger('singleTap');
          touch = {};
        }, 250);
      }
    }).bind('touchcancel', function(){ touch = {} });
  });

  ['swipe', 'swipeLeft', 'swipeRight', 'swipeUp', 'swipeDown', 'doubleTap', 'tap', 'singleTap', 'longTap'].forEach(function(m){
    $.fn[m] = function(callback){ return this.bind(m, callback) }
  });
})(Zepto);



(function($) {
	// swipe for top nav
	$('#logo').bind('touchmove', function (e) {
		e.preventDefault();} 
	);
	$('#logo, h1.nomad').swipeRight(function () {
		closeBottomMenu();
		$('html').find('body').addClass('onL');
	});
	$('#logo').swipeLeft(function () {
		$('html').find('body').removeClassClass('onL');
	});
	$('.cube').swipeLeft(function () {
	      $('html').find('body').toggleClass('onL');
	      return false;
	});
	$('#create').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#create, h1.nomad').swipeLeft(function () {
		closeBottomMenu();
		$('html').find('body').addClass('onR');
	});
	$('#create').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#moduleList').swipeLeft(function () {
		$('html').find('body').removeClass('onL');
	});
	$('#createList').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#createList').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#search').bind('touchmove', function (e) {
		e.preventDefault();
	});
	$('#search').swipeDown(function () {
		$('body').find('#searchForm').toggleClass('hide');
	});

	$('.thrhld').on('click',function () {
		if($(this).parent().hasClass('teaser')) {
			$(this).parent().removeClass('teaser');
		} else {
			$(this).parent().toggleClass('exposed');
			//.css('height',window.innerWidth);
		}
	});
	$('.navbar-bottom .thrhld').swipeDown(function(){
	        $(this).parent().removeClass('exposed teaser');
	});
	$('.navbar-bottom .thrhld').swipeUp(function(){
	        $(this).parent().addClass('exposed').removeClass('teaser');
	});

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').live('click', function () {
	if($('body').hasClass('onL')){
	    $('#logo').trigger('swipeLeft');
	}else{
	    $('#logo').trigger('swipeRight');
	}
	return false;
    });

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.launch').live('click', function () {
	if($('body').hasClass('onR')===true){
	    $('#create').trigger('swipeRight');
	} else {
	    $('#create').trigger('swipeLeft');
	}
	return false;
    });
    $('article').live('swipeLeft',function () {
	var anchor=$(this);
	anchor.closest('.listing').find("article span[id^=listing-action] .grip.on").closest('article').trigger('swipeRight');
	anchor.find('.grip').addClass('on');
	anchor.find('[id^=listing-action] span').removeClass('hide').addClass('on');
    });	
    $('article').live('swipeRight',function () {
	$(this).find('.grip').removeClass('on');
	$(this).find('[id^=listing-action] span').addClass('hide').removeClass('on');
    });	
    $('article .grip').live('tap', function () {
	if($(this).hasClass('on')===false){
	    $(this).closest('article').trigger('swipeLeft');
	}else{
	    $(this).closest('article').trigger('swipeRight');
	}
    });
    // search toggle
    $('.navbar').find('#search').on('click', function () {
    	$('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    $('#searchForm').find('.cancel').on('click', function () {
	    $('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    // fake phone for prototype
    $('#record-action .icon-phone, .btn-group .btn.call, .controls .btn.call').on('click', function () {
	
	    $('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="javascript:return false;" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
	    return false;
    });
    $('.over #cancel').live('click tap', function () {
		$(this).closest('.over').remove();
		return false;
    });
    $('a[title=Remove]').live('click', function () {
		$(this).closest('article').addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
		setTimeout(function () {
		    $('.deleted').remove();
		}, 250);
		return false;
    });
    $('#tour').on('click', function () {
      $(this).remove();
    });
    $('#back .back, .back.btn').on('click', function(){
		if(history.length<=2) {
			window.location="./";
		}else{
		    window.history.back(-1);		
		}
    });
    $('.listing > article:last-child a.show_more_posts').live('click', function(e){
        $(this).closest('article').remove();
	inject_posts('append',$('.listing'),5);
	return false;
    });
    $('.listing > article:nth-child(3) a.show_more_posts').live('click', function(e){
	$(this).closest('article').remove();
	inject_posts('prepend',$('.listing'),5);
	return false;
    });
    $('.listing > article.nav').live('click', function(e){
        $(this).find('a').first().css('border','1px solid red').trigger('click');
    });

    var post_template = '<article><div title="Perkin Kleiners"><a href="perkin_kleiners.html">Perkin Kleiners</a> is a <a href="100seat.html">100 seat plan</a> of 75K closing in 20 days at <a href="">quality</a> stage  </div><span id="listing-action-item1"><i class="grip">|||</i><span class="hide actions"><a href="#l" title="Log"><i class="icon-share icon-md"></i><br>Reply</a><a href="#r" title="Remove"><i class="icon-trash icon-md"></i><br>Remove</a></span></span></article>',
	more_posts_link = '<article class="nav"><div><a class="show_more_posts" href="#more">Show more activity...</a></div></article>',
	listing_spacer = '<i></i>',
	posts_search_template = '\
	<section class="search topelbar">\
          <i class="icon-search"></i>\
          <div class="form-search row-fluid" action="" _lpchecked="1">\
            <input type="text" class="search-query" placeholder="       Search all activity">\
          </div>\
        </section>';

    function inject_posts(order,anchor,numberofrecords){
	var posts = '',
	    topspacer = '<i></i>',
	    domtopspacer = $('.listing > i');
        for(i=0;i<numberofrecords;i++){
		posts = posts + post_template;
		if(i===numberofrecords-1 && order==='append') {
		    posts = posts+more_posts_link;
		}
	}
	if(order==='prepend' || order==='update'){
	    anchor.children("section.search").remove();
	    anchor.children("i").remove();
	    anchor.children("article.nav").remove();
	    if(anchor.find('article:not(.nav)').size() > 25) {
	        anchor.find('article:not(.nav)').slice(20,25).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
	    }
	    anchor.prepend(listing_spacer + posts_search_template + more_posts_link + posts).append(more_posts_link);
        } else if(order==="append") {
	    anchor.find('article.nav:last-child').remove();
	    if(anchor.find('article:not(.nav)').size() > 25) {
		anchor.children("section.search").remove();
		anchor.children("i").remove();
		anchor.children("article.nav").remove();
	        anchor.find('article:not(.nav)').slice(0,5).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
		anchor.prepend(listing_spacer + posts_search_template + more_posts_link);
	    }
	    anchor.append(posts);
	}
	setTimeout(function () {
	    $('.deleted').remove();
	}, 250);
    }

    if($('.alert').size()){
	setTimeout(function(ia){
            $('.alert').anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert').hide() });
        }, 3000);
    }

    $(".icon-star-empty, .icon-star").live('click tap',function(){
	var rn=Math.floor(Math.random()*100);
	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-general" style="display:block;"><strong>Loading...</strong></div>');
	setTimeout(function(ia){
            $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
        }, 3000);
    });
      $("a[title=Remove]").live('click tap',function(){
  	var rn=Math.floor(Math.random()*100);
  	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-success" style="display:block;"><strong>Success!</strong> You removed an item!</div>');
  	setTimeout(function(ia){
              $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
          }, 3000);
      });


    $('.icon-star, .icon-star-empty').live('click tap',function(){
        $(this).toggleClass('icon-star-empty').toggleClass('icon-star');
    });
    
    $('form input').on('focus',function(){
	if($('.navbar-bottom').hasClass('teaser')) {
	    $('.navbar-bottom').removeClass('teaser');
	}
    });

})(window.Zepto);

function closeBottomMenu() {
	if($('.navbar-bottom').hasClass('exposed') || $('.navbar-bottom').hasClass('teaser')) {
		$('.navbar-bottom').removeClass('exposed teaser');
	}
}

function parseQueryString(){
    var qs = location.search.substring(1);
    qs = qs.split("&");
    if(qs.length === 2){
	return qs;
    }else{
	qs=qs[0].split('=');
	return qs[1];
    }
}



//     Zepto.js
//     (c) 2010-2012 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.

(function($){
  var $$ = $.qsa, handlers = {}, _zid = 1, specialEvents={};

  specialEvents.click = specialEvents.mousedown = specialEvents.mouseup = specialEvents.mousemove = 'MouseEvents';

  function zid(element) {
    return element._zid || (element._zid = _zid++);
  }
  function findHandlers(element, event, fn, selector) {
    event = parse(event);
    if (event.ns) var matcher = matcherFor(event.ns);
    return (handlers[zid(element)] || []).filter(function(handler) {
      return handler
        && (!event.e  || handler.e == event.e)
        && (!event.ns || matcher.test(handler.ns))
        && (!fn       || handler.fn == fn)
        && (!selector || handler.sel == selector);
    });
  }
  function parse(event) {
    var parts = ('' + event).split('.');
    return {e: parts[0], ns: parts.slice(1).sort().join(' ')};
  }
  function matcherFor(ns) {
    return new RegExp('(?:^| )' + ns.replace(' ', ' .* ?') + '(?: |$)');
  }

  function eachEvent(events, fn, iterator){
    if ($.isObject(events)) $.each(events, iterator);
    else events.split(/\s/).forEach(function(type){ iterator(type, fn) });
  }

  function add(element, events, fn, selector, getDelegate, capture){
    capture = !!capture;
    var id = zid(element), set = (handlers[id] || (handlers[id] = []));
    eachEvent(events, fn, function(event, fn){
      var delegate = getDelegate && getDelegate(fn, event),
        callback = delegate || fn;
      var proxyfn = function (event) {
        var result = callback.apply(element, [event].concat(event.data));
        if (result === false) event.preventDefault();
        return result;
      };
      var handler = $.extend(parse(event), {fn: fn, proxy: proxyfn, sel: selector, del: delegate, i: set.length});
      set.push(handler);
      element.addEventListener(handler.e, proxyfn, capture);
    });
  }
  function remove(element, events, fn, selector){
    var id = zid(element);
    eachEvent(events || '', fn, function(event, fn){
      findHandlers(element, event, fn, selector).forEach(function(handler){
        delete handlers[id][handler.i];
        element.removeEventListener(handler.e, handler.proxy, false);
      });
    });
  }

  $.event = { add: add, remove: remove }

  $.fn.bind = function(event, callback){
    return this.each(function(){
      add(this, event, callback);
    });
  };
  $.fn.unbind = function(event, callback){
    return this.each(function(){
      remove(this, event, callback);
    });
  };
  $.fn.one = function(event, callback){
    return this.each(function(i, element){
      add(this, event, callback, null, function(fn, type){
        return function(){
          var result = fn.apply(element, arguments);
          remove(element, type, fn);
          return result;
        }
      });
    });
  };

  var returnTrue = function(){return true},
      returnFalse = function(){return false},
      eventMethods = {
        preventDefault: 'isDefaultPrevented',
        stopImmediatePropagation: 'isImmediatePropagationStopped',
        stopPropagation: 'isPropagationStopped'
      };
  function createProxy(event) {
    var proxy = $.extend({originalEvent: event}, event);
    $.each(eventMethods, function(name, predicate) {
      proxy[name] = function(){
        this[predicate] = returnTrue;
        return event[name].apply(event, arguments);
      };
      proxy[predicate] = returnFalse;
    })
    return proxy;
  }

  // emulates the 'defaultPrevented' property for browsers that have none
  function fix(event) {
    if (!('defaultPrevented' in event)) {
      event.defaultPrevented = false;
      var prevent = event.preventDefault;
      event.preventDefault = function() {
        this.defaultPrevented = true;
        prevent.call(this);
      }
    }
  }

  $.fn.delegate = function(selector, event, callback){
    var capture = false;
    if(event == 'blur' || event == 'focus'){
      if($.iswebkit)
        event = event == 'blur' ? 'focusout' : event == 'focus' ? 'focusin' : event;
      else
        capture = true;
    }

    return this.each(function(i, element){
      add(element, event, callback, selector, function(fn){
        return function(e){
          var evt, match = $(e.target).closest(selector, element).get(0);
          if (match) {
            evt = $.extend(createProxy(e), {currentTarget: match, liveFired: element});
            return fn.apply(match, [evt].concat([].slice.call(arguments, 1)));
          }
        }
      }, capture);
    });
  };
  $.fn.undelegate = function(selector, event, callback){
    return this.each(function(){
      remove(this, event, callback, selector);
    });
  }

  $.fn.live = function(event, callback){
    $(document.body).delegate(this.selector, event, callback);
    return this;
  };
  $.fn.die = function(event, callback){
    $(document.body).undelegate(this.selector, event, callback);
    return this;
  };

  $.fn.on = function(event, selector, callback){
    return selector === undefined || $.isFunction(selector) ?
      this.bind(event, selector) : this.delegate(selector, event, callback);
  };
  $.fn.off = function(event, selector, callback){
    return selector === undefined || $.isFunction(selector) ?
      this.unbind(event, selector) : this.undelegate(selector, event, callback);
  };

  $.fn.trigger = function(event, data){
    if (typeof event == 'string') event = $.Event(event);
    fix(event);
    event.data = data;
    return this.each(function(){ this.dispatchEvent(event) });
  };

  // triggers event handlers on current element just as if an event occurred,
  // doesn't trigger an actual event, doesn't bubble
  $.fn.triggerHandler = function(event, data){
    var e, result;
    this.each(function(i, element){
      e = createProxy(typeof event == 'string' ? $.Event(event) : event);
      e.data = data; e.target = element;
      $.each(findHandlers(element, event.type || event), function(i, handler){
        result = handler.proxy(e);
        if (e.isImmediatePropagationStopped()) return false;
      });
    });
    return result;
  };

  // shortcut methods for `.bind(event, fn)` for each event type
  ('focusin focusout load resize scroll unload click dblclick '+
  'mousedown mouseup mousemove mouseover mouseout '+
  'change select keydown keypress keyup error').split(' ').forEach(function(event) {
    $.fn[event] = function(callback){ return this.bind(event, callback) };
  });

  ['focus', 'blur'].forEach(function(name) {
    $.fn[name] = function(callback) {
      if (callback) this.bind(name, callback);
      else if (this.length) try { this.get(0)[name]() } catch(e){};
      return this;
    };
  });

  $.Event = function(type, props) {
    var event = document.createEvent(specialEvents[type] || 'Events'), bubbles = true;
    if (props) for (var name in props) (name == 'bubbles') ? (bubbles = !!props[name]) : (event[name] = props[name]);
    event.initEvent(type, bubbles, true, null, null, null, null, null, null, null, null, null, null, null, null);
    return event;
  };

})(Zepto);