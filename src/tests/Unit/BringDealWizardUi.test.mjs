import test from 'node:test';
import assert from 'node:assert/strict';
import wizard from '../../resources/js/bring-deal.js';
function setup(config = {}) {
 const sections = new Map(); const data = {title:'Saved deal',description:'Description stays',country:'UAE',currency:'AED',estimated_value:'0'};
 const state = wizard({primaryTypes:['sell','buy'],typeLabels:{buy:'Buy'},categoryLabels:{marine:'Marine'},empty:'Not specified',...config});
 state.$root={querySelector(selector){if(selector.includes('data-deal-step')){const number=Number(selector.match(/"(\d+)"/)[1]);return {querySelectorAll:()=>sections.get(number)||[]};}return {scrollIntoView(){}};}};
 state.$nextTick=fn=>fn(); const original=state.updateSummary;state.updateSummary=()=>{};
 return {state,sections,data,summary:()=>original.call(state)};
}
function field(valid=true,disabled=false,name='title'){return {name,matches:()=>disabled,checkValidity:()=>valid,focus(){this.focused=true;},reportValidity(){this.reported=true;}};}
test('forward navigation stops at earliest invalid step and reveals legacy card choices',()=>{
 const {state,sections}=setup({type:'investment'});const bad=field(false,false,'deal_type');sections.set(1,[bad]);state.go(5);assert.equal(state.step,1);assert.equal(state.showMoreTypes,true);assert.equal(bad.focused,true);assert.equal(bad.reported,true);
});
test('back navigation preserves card choices and later fields are not cleared',()=>{
 const {state}=setup({type:'buy',category:'marine'});state.step=4;state.go(1);assert.equal(state.type,'buy');assert.equal(state.category,'marine');state.go(3);assert.equal(state.step,3);
});
test('Enter advances a stage while textarea and button keyboard behavior remain native',()=>{
 const {state}=setup();const event={target:{tagName:'INPUT',type:'text'},preventDefault(){this.prevented=true;}};state.enter(event);assert.equal(state.step,2);assert.equal(event.prevented,true);
 for(const target of [{tagName:'TEXTAREA'},{tagName:'BUTTON'},{tagName:'INPUT',type:'radio'}]){let prevented=false;state.enter({target,preventDefault(){prevented=true;}});assert.equal(prevented,false);}
});
test('early submit advances without posting and final submit requires valid review and contact',()=>{
 const {state,sections}=setup();let prevented=0;state.submit({preventDefault(){prevented++;}});assert.equal(state.step,2);assert.equal(prevented,1);
 state.step=5;const email=field(false,false,'email');sections.set(4,[email]);state.submit({preventDefault(){prevented++;}});assert.equal(state.step,4);assert.equal(state.busy,false);
 sections.set(4,[]);const review=field(false);sections.set(5,[review]);state.step=5;state.submit({preventDefault(){prevented++;}});assert.equal(state.busy,false);
 sections.set(5,[]);state.submit({preventDefault(){prevented++;}});assert.equal(state.busy,true);const before=prevented;state.submit({preventDefault(){prevented++;}});assert.equal(prevented,before+1);
});
test('summary keeps literal text and zero values, while disabled controls do not block navigation',()=>{
 const {state,sections,data,summary}=setup({type:'buy'});data.description='<img src=x onerror=alert(1)>';
 const original=globalThis.FormData;try{globalThis.FormData=class{get(name){return data[name] ?? null;}};summary();assert.equal(state.summary.description,data.description);assert.equal(state.summary.estimated_value,'0');assert.equal(state.summary.company_name,'Not specified');}finally{globalThis.FormData=original;}
 sections.set(2,[field(false,true)]);state.go(4);assert.equal(state.step,4);
});
