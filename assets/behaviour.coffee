# Note that when compiling with coffeescript, the plugin is wrapped in another
# anonymous function. We do not need to pass in undefined as well, since
# coffeescript uses (void 0) instead.
do ($ = jQuery, window, document) ->

  # window and document are passed through as local variable rather than global
  # as this (slightly) quickens the resolution process and can be more efficiently
  # minified (especially when both are regularly referenced in your plugin).

  # Create the defaults once
  pluginName = "semcSubpages"
  defaults =
    debug: false
    orderSel: '[data-trigger="order"]'
    expandSel: '[data-trigger="expand"]'

  # The actual plugin constructor
  class Plugin
    constructor: (@element, options) ->
      # jQuery has an extend method which merges the contents of two or
      # more objects, storing the result in the first object. The first object
      # is generally empty as we don't want to alter the default options for
      # future instances of the plugin
      @settings = $.extend {}, defaults, options
      @_defaults = defaults
      @_name = pluginName
      @init()

    # Simple logger.
    log: (msg) ->
      console?.log msg if @settings.debug

    init: ->
      # Access the DOM element and the options via the instance,
      # e.g., @element and @settings
      $(@element).on 'click', @settings.orderSel, (ev) =>
        @handleOrder(ev)

      $(@element).on 'click', @settings.expandSel, (ev) =>
        @handleExpandCollapse(ev)

    # Sort subitems by title
    handleOrder: (ev) ->
      ev.preventDefault()
      link = $(ev.target)
      switch link.attr('data-order')
        when 'asc' then comp = (a, b) ->
          (a > b) ? -1 : 1
        when 'desc' then comp = (a, b) ->
          (a < b) ? -1 : 1
        else
          @log "Invalid sort order"
          return

      $list = link.closest('nav').next('ul');
      $sorted = $('>li', link.closest('nav').next('ul')).get().sort (l, r) ->
        left = $('.media__body', $(l)).text()
        right = $('.media__body', $(r)).text()
        comp left, right

      $list.empty().append $sorted

    handleExpandCollapse: (ev) ->
      ev.preventDefault()
      link = $(ev.target)
      action = link.attr('data-action')
      if action is 'expand'
        link.parent().next('nav').show().next('ul').show()
        link.attr('data-action', 'collapse').text('Collapse')

      if action is 'collapse'
        link.parent().next('nav').hide().next('ul').hide()
        link.attr('data-action', 'expand').text('Expand')


  # A really lightweight plugin wrapper around the constructor,
  # preventing against multiple instantiations
  $.fn[pluginName] = (options) ->
    @each ->
      unless $.data @, "plugin_#{pluginName}"
        $.data @, "plugin_#{pluginName}", new Plugin @, options

  $ ->
    $('.semc-wrap').semcSubpages { debug: true }