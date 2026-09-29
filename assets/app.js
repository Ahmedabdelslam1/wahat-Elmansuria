/* واحة المنصورية — تحسينات واجهة مشتركة */
(function(){
  'use strict';
  function ready(fn){if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',fn);else fn();}
  window.uiToast=function(message,type){
    var t=document.querySelector('.ui-toast');
    if(!t){t=document.createElement('div');t.className='ui-toast';document.body.appendChild(t);}
    t.textContent=String(message||'');t.className='ui-toast show '+(type||'info');
    clearTimeout(t._timer);t._timer=setTimeout(function(){t.classList.remove('show')},3200);
  };
  window.uiSetBusy=function(btn,busy,label){
    if(!btn)return;
    if(busy){btn.dataset.uiLabel=btn.innerHTML;btn.disabled=true;btn.innerHTML='<span class="ui-inline-spinner" aria-hidden="true"></span>'+(label||'جارٍ التنفيذ...');}
    else{btn.disabled=false;if(btn.dataset.uiLabel!=null){btn.innerHTML=btn.dataset.uiLabel;delete btn.dataset.uiLabel;}}
  };
  ready(function(){
    var s=document.createElement('style');s.textContent='.ui-inline-spinner{display:inline-block;width:13px;height:13px;border:2px solid currentColor;border-left-color:transparent;border-radius:50%;vertical-align:-2px;margin-left:6px;animation:uiSpin .65s linear infinite}';document.head.appendChild(s);
    document.querySelectorAll('form').forEach(function(form){form.addEventListener('submit',function(){var b=form.querySelector('button[type="submit"]');if(b&&!b.disabled)uiSetBusy(b,true);});});
  });
})();