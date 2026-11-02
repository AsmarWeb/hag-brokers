import test from 'node:test';
import assert from 'node:assert/strict';
import wizard from '../../resources/js/trade-opportunity.js';

function setup() {
    const fields = new Map();
    const input = { files: [], value: '', setCustomValidity() {} };
    const root = {
        querySelectorAll: () => [...fields].map(([step, controls]) => ({ dataset: { tradeStep: String(step) }, querySelectorAll: () => controls })),
        querySelector: () => ({ scrollIntoView() {} }),
    };
    const state = wizard({type: 'sell', category: 'food', stepLabels: ['Goal','Details','Terms','Media','Review'], mediaLabel: 'Invalid media', typeLabels: {sell: 'Sell'}, categoryLabels: {food: 'Food'}});
    state.$root = root; state.$refs = {mediaInput: input}; state.$nextTick = fn => fn();
    state.updateSummary = () => {};
    return {state, fields, input};
}
function validField(valid = true, disabled = false) {
    return {matches: () => disabled, checkValidity: () => valid, focus() {this.focused = true;}, reportValidity() {this.reported = true;}};
}

test('forward jumps expose the first invalid step; back navigation keeps entered values', () => {
    const {state, fields} = setup(); const invalid = validField(false);
    fields.set(2, [invalid]); state.title = 'Saved title';
    state.go(5); assert.equal(state.step, 2); assert.equal(invalid.focused, true);
    state.go(1); assert.equal(state.step, 1); assert.equal(state.title, 'Saved title');
});
test('disabled conditional controls do not prevent advancing', () => {
    const {state, fields} = setup(); fields.set(3, [validField(false, true)]);
    state.go(4); assert.equal(state.step, 4);
});
test('Enter advances one step instead of submitting an unfinished wizard', () => {
    const {state} = setup(); let prevented = false;
    state.submit({preventDefault: () => prevented = true});
    assert.equal(prevented, true); assert.equal(state.step, 2);
});
test('final submit requires confirmation and returns to invalid earlier fields', () => {
    const {state, fields} = setup(); state.step = 5;
    fields.set(5, [validField(false)]); let prevented = false;
    state.submit({preventDefault: () => prevented = true}); assert.equal(prevented, true);
    fields.set(5, [validField()]); prevented = false;
    state.submit({preventDefault: () => prevented = true}); assert.equal(prevented, false);
    fields.set(2, [validField(false)]);
    state.submit({preventDefault: () => prevented = true}); assert.equal(state.step, 2);
});
function mediaEnvironment(t) {
    t.mock.method(URL, 'createObjectURL', () => 'blob:test-preview');
    const revoked = []; t.mock.method(URL, 'revokeObjectURL', url => revoked.push(url));
    const old = globalThis.DataTransfer;
    globalThis.DataTransfer = class {constructor() {this.files = [];this.items = {add: file => this.files.push(file)};}};
    t.after(() => {if (old === undefined) delete globalThis.DataTransfer;else globalThis.DataTransfer = old;});
    return revoked;
}
test('separate image and video selections accumulate, deduplicate and remove the exact submitted files', t => {
    const revoked = mediaEnvironment(t); const {state, input} = setup();
    const image = {name:'photo.jpg', size:1024, lastModified:1}; const video = {name:'clip.mp4', size:2048, lastModified:2};
    state.mergeMedia([image]); state.mergeMedia([video]); state.mergeMedia([image]);
    assert.equal(state.files.length, 2); assert.equal(state.files[1].kind, 'video'); assert.deepEqual(input.files, [image, video]);
    state.removeMedia(state.files[0].id); assert.deepEqual(input.files, [video]); assert.equal(revoked.length, 1);
    state.destroy(); assert.equal(revoked.length, 2);
});
test('unsupported files, file count and total size limits preserve the previously selected files', t => {
    mediaEnvironment(t); const {state, input} = setup(); const original = {name:'keep.png',size:100,lastModified:1}; state.mergeMedia([original]);
    for (const incoming of [[{name:'bad.exe',size:1}], [{name:'large.mp4',size:21*1048576}], Array.from({length:10},(_,i)=>({name:`${i}.jpg`,size:1,lastModified:i}))]) {
        state.mergeMedia(incoming); assert.equal(state.files.length, 1); assert.deepEqual(input.files, [original]); assert.equal(state.mediaError, 'Invalid media');
    }
});
