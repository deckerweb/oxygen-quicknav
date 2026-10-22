/* Controller regressions in a minimal DOM; browser behavior is checked separately. */
const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const code = fs.readFileSync(require('node:path').join(__dirname,'../assets/dialog.js'),'utf8');
let checks=0;
function fixture({unsupported=false,throws=false}={}) {
 const events={};const listeners={};const form={addEventListener:(name,fn)=>events['form:'+name]=fn};
 const button={focus:()=>{}};const dialog={hidden:true,open:false,querySelector:()=>button,getBoundingClientRect:()=>({left:10,right:100,top:10,bottom:100}),removeAttribute:()=>{dialog.open=false},addEventListener:(name,fn)=>events[name]=fn};
 dialog.querySelector=selector=>selector==='form'?form:button;
 const link={dataset:{oqnOpen:'oqn-history'},isConnected:true,focus:()=>{link.focused=true}};
 function Dialog(){};Dialog.prototype.showModal=function(){if(throws)throw Error('opening rejected');this.open=true};Dialog.prototype.close=function(){this.open=false};
 dialog.showModal=()=>{throw Error('bad instance method')};dialog.close=()=>{throw Error('bad instance method')};
 const window={HTMLDialogElement:unsupported?null:Dialog};
 const document={addEventListener:(name,fn)=>{(listeners[name]??=[]).push(fn)},querySelectorAll:()=>[dialog],getElementById:()=>dialog};
 const context=vm.createContext({window,document});vm.runInContext(code,context);
 const click=()=>{let prevented=false;listeners.click[0]({target:{closest:()=>link},preventDefault:()=>{prevented=true}});return prevented};
 return {context,dialog,events,link,listeners,click};
}
let f=fixture();assert.equal(f.click(),true);assert.equal(f.dialog.open,true);checks++;
f.events.cancel({preventDefault(){}});assert.equal(f.dialog.hidden,true);assert.equal(f.link.focused,true);checks++;
f.click();f.events['form:submit']({preventDefault(){}});assert.equal(f.dialog.open,false);checks++;
f.click();f.events.click({target:f.dialog,clientX:0,clientY:0});assert.equal(f.dialog.hidden,true);checks++;
vm.runInContext(code,f.context);assert.equal(f.listeners.click.length,1);checks++;
f=fixture({unsupported:true});assert.equal(f.click(),false);assert.equal(f.dialog.hidden,true);checks++;
f=fixture({throws:true});assert.equal(f.click(),false);assert.equal(f.dialog.hidden,true);checks++;
console.log(`RESULT: ${checks} dialog controller regressions; DOM simulation, no screenshot`);
