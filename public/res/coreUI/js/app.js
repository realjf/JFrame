/*****
* CONFIGURATION
*/
    //Main navigation
    $.navigation = $('nav > ul.nav');

  $.panelIconOpened = 'icon-arrow-up';
  $.panelIconClosed = 'icon-arrow-down';

  //Default colours
  $.brandPrimary =  '#20a8d8';
  $.brandSuccess =  '#4dbd74';
  $.brandInfo =     '#63c2de';
  $.brandWarning =  '#f8cb00';
  $.brandDanger =   '#f86c6b';

  $.grayDark =      '#2a2c36';
  $.gray =          '#55595c';
  $.grayLight =     '#818a91';
  $.grayLighter =   '#d1d4d7';
  $.grayLightest =  '#f8f9fa';

'use strict';

/****
* MAIN NAVIGATION
*/

var App = function () {
    /*修正右边高*/
    var fixPageContentHeight = function() {
        var $pageContent = $('.page-content-right');
        if ($pageContent.length > 0) {
            if($pageContent.parents('.row-fluid ').parent().hasClass('modal-body')){
                $pageContent.css('max-height', $(window).height() -  130 + 'px');
            }else{
                $pageContent.css('height', $(window).height() -  130 + 'px');
            }
        } else {
            $('body').css('overflow-y', 'auto');
        }
    }


    return {

        //main function to initiate template pages
        init: function () {

            //IMPORTANT!!!: Do not modify the core handlers call order.

            //core handlers
            App.addResponsiveHandler(handleChoosenSelect); // reinitiate chosen dropdown on main content resize. disable this line if you don't really use chosen dropdowns.
        },
        fixPageContentHeight: function() {
            fixPageContentHeight();
        },
        addResponsiveHandler: function (func) {
            responsiveHandlers.push(func);
        },

        // wrapper function to  block element(indicate loading)
        blockUI: function (el, centerY) {
            var el = jQuery(el);
            el.block({
                message: '<image src="./assets/image/ajax-loading.gif" align="">',
                centerY: centerY != undefined ? centerY : true,
                css: {
                    top: '10%',
                    border: 'none',
                    padding: '2px',
                    backgroundColor: 'none'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.05,
                    cursor: 'wait'
                }
            });
        },

        // wrapper function to  un-block element(finish loading)
        unblockUI: function (el) {
            jQuery(el).unblock({
                onUnblock: function () {
                    jQuery(el).removeAttr("style");
                }
            });
        },

        // initializes uniform elements
        initUniform: function (els) {

            if (els) {
                jQuery(els).each(function () {
                    if ($(this).parents(".checker").size() == 0) {
                        $(this).show();
                        $(this).uniform();
                    }
                });
            } else {
                handleUniform();
            }

        },

        // initializes choosen dropdowns
        initChosenSelect: function (els) {
            $(els).chosen({
                allow_single_deselect: true
            });
        },

        initFancybox: function () {
            handleFancybox();
        },

        // 初始化AJAX分页
        initPagination: function(element) {
            jQuery(document).off('click', element + ' .pagination li > a');
            jQuery(document).on('click', element + ' .pagination li > a', function (e) {
                e.preventDefault();

                var url = $(this).attr("href");
                if (!url) {
                    return;
                }
                // 如果是JS则执行跳过HTTP访问
                if (url.substring(0, 11) == 'javascript:') {
                    var str = url.substring(11);
                    return eval(str);
                }
                var pageContent = jQuery(element);
                App.blockUI(pageContent, false);
                Http.load(element, url, {}, function (res) {
                    $(element +' .dataTables_info>select').removeAttr('onchange');//兼容
                    App.unblockUI(pageContent);
                });
            });
            $(element +' .dataTables_info>select').removeAttr('onchange'); //有空过来实现这个功能
            jQuery(document).on('change', element + ' .dataTables_info>select', function (e) {
                var pageContent = jQuery(element);
                var url=$(this).attr('geturl');
                var num=$(this).find('option:selected').attr("value");
                var pattern = 'n=([^&]*)';
                var replaceText = 'n=' + num;
                if(url.match(pattern)) {
                    var tmp = '/(n=)([^&]*)/gi';
                    url = url.replace(eval(tmp), replaceText);
                }else {
                    if(url.match('[\?]')) {
                        url = url + '&' + replaceText;
                    }else {
                        url = url + '?' + replaceText;
                    }
                }
                App.blockUI(pageContent, false);
                Http.load(element, url, {}, function (res) {
                    $(element +' .dataTables_info>select').removeAttr('onchange');
                    App.unblockUI(pageContent);
                });
            });
        },

        getActualVal: function (el) {
            var el = jQuery(el);
            if (el.val() === el.attr("placeholder")) {
                return "";
            }

            return el.val();
        },

        getURLParameter: function (paramName) {
            var searchString = window.location.search.substring(1),
                i, val, params = searchString.split("&");

            for (i = 0; i < params.length; i++) {
                val = params[i].split("=");
                if (val[0] == paramName) {
                    return unescape(val[1]);
                }
            }
            return null;
        },

        // check for device touch support
        isTouchDevice: function () {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        },

        isIE8: function () {
            return isIE8;
        },

        isRTL: function () {
            return isRTL;
        },

        getLayoutColorCode: function (name) {
            if (layoutColorCodes[name]) {
                return layoutColorCodes[name];
            } else {
                return '';
            }
        }

    };

}()

$(document).ready(function($){


  // Add class .active to current link
  $.navigation.find('a').each(function(){

    var cUrl = String(window.location).split('?')[0];

    if (cUrl.substr(cUrl.length - 1) == '#') {
      cUrl = cUrl.slice(0,-1);
    }

    if ($($(this))[0].href==cUrl) {
      $(this).addClass('active');

      $(this).parents('ul').add(this).each(function(){
        $(this).parent().addClass('open');
      });
    }
  });

  // Dropdown Menu
  $.navigation.on('click', 'a', function(e){

    if ($.ajaxLoad) {
      e.preventDefault();
    }

    if ($(this).hasClass('nav-dropdown-toggle')) {
      $(this).parent().toggleClass('open');
      resizeBroadcast();
    }

  });

  function resizeBroadcast() {

    var timesRun = 0;
    var interval = setInterval(function(){
      timesRun += 1;
      if(timesRun === 5){
        clearInterval(interval);
      }
      window.dispatchEvent(new Event('resize'));
    }, 62.5);
  }

  /* ---------- Main Menu Open/Close, Min/Full ---------- */
  $('.navbar-toggler').click(function(){

    if ($(this).hasClass('sidebar-toggler')) {
      $('body').toggleClass('sidebar-hidden');
      resizeBroadcast();
    }

    if ($(this).hasClass('aside-menu-toggler')) {
      $('body').toggleClass('aside-menu-hidden');
      resizeBroadcast();
    }

    if ($(this).hasClass('mobile-sidebar-toggler')) {
      $('body').toggleClass('sidebar-mobile-show');
      resizeBroadcast();
    }

  });

  $('.sidebar-close').click(function(){
    $('body').toggleClass('sidebar-opened').parent().toggleClass('sidebar-opened');
  });

  /* ---------- Disable moving to top ---------- */
  $('a[href="#"][data-top!=true]').click(function(e){
    e.preventDefault();
  });

    // wrapper function to  un-block element(finish loading)
    function unblockUI(el) {
        jQuery(el).unblock({
            onUnblock: function () {
                jQuery(el).removeAttr("style");
            }
        });
    }

    // wrapper function to  block element(indicate loading)
    function blockUI(el, centerY) {
        var el = jQuery(el);
        el.block({
            message: '<image src="./assets/image/ajax-loading.gif" align="">',
            centerY: centerY != undefined ? centerY : true,
            css: {
                top: '10%',
                border: 'none',
                padding: '2px',
                backgroundColor: 'none'
            },
            overlayCSS: {
                backgroundColor: '#000',
                opacity: 0.05,
                cursor: 'wait'
            }
        });
    }

    // 初始化AJAX分页
    function initPaginator(element) {
        jQuery(document).off('click', element + ' .pagination li > a');
        jQuery(document).on('click', element + ' .pagination li > a', function (e) {
            e.preventDefault();

            var url = $(this).attr("href");
            if (!url) {
                return;
            }
            // 如果是JS则执行跳过HTTP访问
            if (url.substring(0, 11) == 'javascript:') {
                var str = url.substring(11);
                return eval(str);
            }
            var pageContent = jQuery(element);
            blockUI(pageContent, false);
            Http.load(element, url, {}, function (res) {
                $(element +' .dataTables_info>select').removeAttr('onchange');//兼容
                unblockUI(pageContent);
            });
        });
        $(element +' .dataTables_info>select').removeAttr('onchange'); //有空过来实现这个功能
        jQuery(document).on('change', element + ' .dataTables_info>select', function (e) {
            var pageContent = jQuery(element);
            var url=$(this).attr('geturl');
            var num=$(this).find('option:selected').attr("value");
            var pattern = 'n=([^&]*)';
            var replaceText = 'n=' + num;
            if(url.match(pattern)) {
                var tmp = '/(n=)([^&]*)/gi';
                url = url.replace(eval(tmp), replaceText);
            }else {
                if(url.match('[\?]')) {
                    url = url + '&' + replaceText;
                }else {
                    url = url + '?' + replaceText;
                }
            }
            blockUI(pageContent, false);
            Http.load(element, url, {}, function (res) {
                $(element +' .dataTables_info>select').removeAttr('onchange');
                unblockUI(pageContent);
            });
        });
    }
});

/****
* CARDS ACTIONS
*/

$(document).on('click', '.card-actions a', function(e){
  e.preventDefault();

  if ($(this).hasClass('btn-close')) {
    $(this).parent().parent().parent().fadeOut();
  } else if ($(this).hasClass('btn-minimize')) {
    var $target = $(this).parent().parent().next('.card-block');
    if (!$(this).hasClass('collapsed')) {
      $('i',$(this)).removeClass($.panelIconOpened).addClass($.panelIconClosed);
    } else {
      $('i',$(this)).removeClass($.panelIconClosed).addClass($.panelIconOpened);
    }

  } else if ($(this).hasClass('btn-setting')) {
    $('#myModal').modal('show');
  }

});

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function init(url) {

  /* ---------- Tooltip ---------- */
  $('[rel="tooltip"],[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});

  /* ---------- Popover ---------- */
  $('[rel="popover"],[data-rel="popover"],[data-toggle="popover"]').popover();

}
