(()=>{"use strict";!function(a){const t=a("#wp-welcome-wrap");a(".wpw-box-plugin a.button").on("click",(function(t){t.preventDefault();const e=a(this),n=e.data("slug");n&&(e.hasClass("disabled")||(e.hasClass("install")&&((t,e)=>{a.ajax({url:WPW_OBJECT.ajax_url,type:"POST",dataType:"json",data:{action:"wpw_plugin_installer",plugin:t,nonce:WPW_OBJECT.admin_nonce},beforeSend(){e.addClass("installing")},complete(a){!0===JSON.parse(a.responseText).success&&(e.html(WPW_OBJECT.i18n.activate),e.attr("class","button activate")),e.removeClass("installing")}})})(n,e),e.hasClass("activate")&&((t,e)=>{a.ajax({url:WPW_OBJECT.ajax_url,type:"POST",dataType:"json",data:{action:"wpw_plugin_activation",plugin:t,nonce:WPW_OBJECT.admin_nonce},beforeSend(){e.addClass("installing")},complete(a){!0===JSON.parse(a.responseText).success&&(e.html(WPW_OBJECT.i18n.activated),e.attr("class","button disabled")),e.removeClass("installing")}})})(n,e)))})),t.find(".wpw-tab-content").hide();let e="";"undefined"!=typeof localStorage&&(e=localStorage.getItem(WPW_OBJECT.storage_key)),null!==e&&a(`#${e}`).length?(a(`#${e}`).hide().fadeIn("fast"),a(`.wpw-tabs-nav a[href="#${e}"]`).addClass("active")):(t.find(".wpw-tab-content").first().hide().fadeIn("fast"),t.find(".wpw-tabs-nav a").first().addClass("active")),t.find(".wpw-tabs-nav a").on("click",(function(e){if(e.preventDefault(),a(this).hasClass("active"))return;t.find(".wpw-tabs-nav a").removeClass("active"),a(e.target).addClass("active");const n=a(e.target).attr("href");"undefined"!=typeof localStorage&&localStorage.setItem(WPW_OBJECT.storage_key,n.replace("#","")),t.find(".wpw-tab-content").hide(),a(n).fadeIn("fast")}))}(jQuery)})();