/*!
 * jQuery Copy Plugin v1.1.1
 * https://github.com/by-syk/jquery-copy
 *
 * Copyright 2017 By_syk
 */

// (function($) {
//   $.extend({
//     copy: function(obj) {
//       return copyText(obj);
//     }
//   });
// } (jQuery));
function urSetClipboard(e,r){void 0===r&&(r=jQuery(document));var t=jQuery('<textarea style="opacity:0">');jQuery("body").append(t),t.val(e).select(),r.trigger("beforecopy");try{document.execCommand("copy"),r.trigger("aftercopy")}catch(o){r.trigger("aftercopyfailure")}t.remove()}function urClearClipboard(){urSetClipboard("")}


// function copyText(obj) {
//   if (!obj) {
//     return false;
//   }
//   var text;
//   if (typeof(obj) == 'object') {
//     if (obj.nodeType) { // DOM node
//       obj = $(obj); // to jQuery object
//     }
//     if (obj instanceof $) {
//       if (!obj.length) { // nonexistent
//         return false;
//       }
//       text = obj.text();
//       if (!text) { // Maybe <textarea />
//         text = obj.val();
//       }
//     } else { // as JSON
//       text = JSON.stringify(obj);
//     }
//   } else { // boolean, number, string
//     text = obj;
//   }
//   //var $temp = $('<input>'); // Line feed is not supported
//   var $temp = $('<textarea>');
//   $('body').append($temp);
//   $temp.val(text).select();
//   var res = document.execCommand('copy');
//   $temp.remove();
//   return res;
// }