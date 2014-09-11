(function() {
  (function($, window, document) {
    var Plugin, defaults, pluginName;
    pluginName = "semcSubpages";
    defaults = {
      debug: false,
      orderSel: '[data-trigger="order"]',
      expandSel: '[data-trigger="expand"]'
    };
    Plugin = (function() {
      function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
      }

      Plugin.prototype.log = function(msg) {
        if (this.settings.debug) {
          return typeof console !== "undefined" && console !== null ? console.log(msg) : void 0;
        }
      };

      Plugin.prototype.init = function() {
        $(this.element).on('click', this.settings.orderSel, (function(_this) {
          return function(ev) {
            return _this.handleOrder(ev);
          };
        })(this));
        return $(this.element).on('click', this.settings.expandSel, (function(_this) {
          return function(ev) {
            return _this.handleExpandCollapse(ev);
          };
        })(this));
      };

      Plugin.prototype.handleOrder = function(ev) {
        var $list, $sorted, comp, link;
        ev.preventDefault();
        link = $(ev.target);
        switch (link.attr('data-order')) {
          case 'asc':
            comp = function(a, b) {
              var _ref;
              return (_ref = a > b) != null ? _ref : -{
                1: 1
              };
            };
            break;
          case 'desc':
            comp = function(a, b) {
              var _ref;
              return (_ref = a < b) != null ? _ref : -{
                1: 1
              };
            };
            break;
          default:
            this.log("Invalid sort order");
            return;
        }
        $list = link.closest('nav').next('ul');
        $sorted = $('>li', link.closest('nav').next('ul')).get().sort(function(l, r) {
          var left, right;
          left = $('.media__body', $(l)).text();
          right = $('.media__body', $(r)).text();
          return comp(left, right);
        });
        return $list.empty().append($sorted);
      };

      Plugin.prototype.handleExpandCollapse = function(ev) {
        var action, link;
        ev.preventDefault();
        link = $(ev.target);
        action = link.attr('data-action');
        if (action === 'expand') {
          link.parent().next('nav').show().next('ul').show();
          link.attr('data-action', 'collapse').text('Collapse');
        }
        if (action === 'collapse') {
          link.parent().next('nav').hide().next('ul').hide();
          return link.attr('data-action', 'expand').text('Expand');
        }
      };

      return Plugin;

    })();
    $.fn[pluginName] = function(options) {
      return this.each(function() {
        if (!$.data(this, "plugin_" + pluginName)) {
          return $.data(this, "plugin_" + pluginName, new Plugin(this, options));
        }
      });
    };
    return $(function() {
      return $('.semc-wrap').semcSubpages({
        debug: true
      });
    });
  })(jQuery, window, document);

}).call(this);
