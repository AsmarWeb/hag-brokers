import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';
const source=readFileSync(new URL('../../resources/js/public-theme.js',import.meta.url),'utf8');
function boot({saved=null,systemLight=false,storageBlocked=false,admin='light'}={}) {
    const values=new Map([['hag-admin-theme',admin]]);if(saved!==null)values.set('hag-theme',saved);
    const classes=new Set(),listeners=new Map();let observer, systemChange;
    const root={style:{},classList:{contains:x=>classes.has(x),toggle:(x,on)=>{if(on===undefined)on=!classes.has(x);if(on)classes.add(x);else classes.delete(x);return on;}}};
    const dispatchEvent=event=>{for(const fn of listeners.get(event.type)||[])fn(event);};
    const media={matches:systemLight,addEventListener:(key,fn)=>systemChange=fn};
    const window={localStorage:{getItem:key=>{if(storageBlocked)throw Error('blocked');return values.get(key)||null;}},matchMedia:()=>media,
        addEventListener:(key,fn)=>listeners.set(key,[...(listeners.get(key)||[]),fn]),dispatchEvent,
        Event:class{constructor(type){this.type=type;}},MutationObserver:class{constructor(fn){observer=fn;}observe(){}}};
    vm.runInNewContext(source,{window,document:{documentElement:root}});
    return {values,classes,root,dispatchEvent,observe:()=>observer(),changeSystem:value=>{media.matches=value;systemChange();}};
}
test('homepage light choice controls both public CSS classes, ignoring admin preference',()=>{
    const env=boot({saved:'light',admin:'dark'});assert.equal(env.classes.has('theme-light'),true);assert.equal(env.classes.has('dark'),false);assert.equal(env.root.style.colorScheme,'light');
});
test('homepage dark choice is respected even on a light operating system',()=>{
    const env=boot({saved:'dark',systemLight:true});assert.equal(env.classes.has('theme-light'),false);assert.equal(env.classes.has('dark'),true);
});
test('system preference is used when no homepage choice exists or storage is blocked',()=>{
    assert.equal(boot({systemLight:true}).classes.has('theme-light'),true);
    assert.equal(boot({systemLight:true,storageBlocked:true}).classes.has('theme-light'),true);
    const env=boot();env.changeSystem(true);assert.equal(env.classes.has('dark'),false);
});
test('storage updates and returning from browser history use the latest homepage selection',()=>{
    const env=boot({saved:'dark'});env.values.set('hag-theme','light');env.dispatchEvent({type:'storage',key:'hag-theme'});assert.equal(env.classes.has('dark'),false);
    env.values.set('hag-theme','dark');env.dispatchEvent({type:'pageshow'});assert.equal(env.classes.has('dark'),true);
});
test('homepage theme-light changes bridge to Tailwind without writing another preference',()=>{
    const env=boot({saved:'dark'});env.root.classList.toggle('theme-light',true);env.observe();assert.equal(env.classes.has('dark'),false);assert.equal(env.values.get('hag-admin-theme'),'light');assert.equal(env.values.get('hag-theme'),'dark');
});
test('admin preference updates do not override the homepage selection',()=>{
    const env=boot({saved:'dark'});env.values.set('hag-admin-theme','light');env.dispatchEvent({type:'storage',key:'hag-admin-theme'});assert.equal(env.classes.has('dark'),true);
});
