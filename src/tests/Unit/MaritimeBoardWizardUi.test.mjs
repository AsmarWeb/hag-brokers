import test from 'node:test';
import assert from 'node:assert/strict';
import wizard from '../../resources/js/maritime-board-wizard.js';

function setup(kind = 'cargo') {
    const fields = new Map();
    const root = {
        querySelectorAll: selector => selector?.includes('discharging_port_id')
            ? []
            : [...fields].map(([step, controls]) => ({
                dataset: { tradeStep: String(step) },
                querySelectorAll: () => controls,
            })),
        querySelector: selector => selector === '.trade-wizard-top' ? { scrollIntoView() {} } : null,
    };
    const state = wizard({
        kind,
        cargoType: 'dry_bulk',
        vesselType: 'bulk',
        stepLabels: ['Type', 'Details', 'Availability', 'Contact', 'Review'],
        cargoTypes: { dry_bulk: 'Dry bulk' },
        vesselTypes: { bulk: 'Bulk carrier' },
        differentPortsMessage: 'Ports must differ',
        laycanOrderMessage: 'Laycan end must follow start',
        positionRequiredMessage: 'Choose a port or enter a position',
    });
    state.$root = root;
    state.$nextTick = callback => callback();
    state.updateSummary = () => {};

    return { state, fields };
}

function validField(valid = true, disabled = false) {
    return {
        matches: () => disabled,
        checkValidity: () => valid,
        focus() { this.focused = true; },
        reportValidity() { this.reported = true; },
    };
}

test('cargo and vessel forms expose the selected maritime card label', () => {
    assert.equal(setup('cargo').state.selectedTypeLabel, 'Dry bulk');
    assert.equal(setup('vessel').state.selectedTypeLabel, 'Bulk carrier');
});

test('forward navigation stops at the first invalid stage and retains entered values', () => {
    const { state, fields } = setup();
    const invalid = validField(false);
    fields.set(2, [validField()]);
    fields.set(3, [invalid]);

    state.title = 'Keep entered values';
    state.go(5);

    assert.equal(state.step, 3);
    assert.equal(invalid.focused, true);
    assert.equal(invalid.reported, true);
    assert.equal(state.title, 'Keep entered values');
});

test('back navigation is allowed without revalidating later stages', () => {
    const { state, fields } = setup();
    fields.set(4, [validField(false)]);
    state.step = 4;

    state.go(2);

    assert.equal(state.step, 2);
});

test('disabled conditional controls do not block progress', () => {
    const { state, fields } = setup('vessel');
    fields.set(3, [validField(false, true)]);

    state.go(4);

    assert.equal(state.step, 4);
});

test('cargo route constraints prevent identical ports and reversed laycan dates', () => {
    const { state } = setup('cargo');
    const constraints = new Map();
    const fields = {
        loading_port_id: { value: '10' },
        discharging_port_id: { value: '10', setCustomValidity: message => constraints.set('port', message) },
        laycan_from: { value: '2026-12-10' },
        laycan_to: { value: '2026-12-09', setCustomValidity: message => constraints.set('laycan', message) },
    };
    fields.loading_port_id.checked = true;
    fields.discharging_port_id.checked = true;
    state.$root.querySelector = selector => {
        const name = selector.match(/name=\"([^\"]+)/)?.[1];
        const field = fields[name];
        return selector.includes(':checked') && !field?.checked ? null : (field || null);
    };
    state.$root.querySelectorAll = selector => selector.includes('discharging_port_id') ? [fields.discharging_port_id] : [];
    state.validateStageConstraints(3);

    assert.ok(constraints.get('port'));
    assert.ok(constraints.get('laycan'));
});

test('vessel route stage requires either a selected current port or a free-text position', () => {
    const { state } = setup('vessel');
    let validityMessage = '';
    const fields = {
        current_port_id: { value: '', checked: false },
        current_position: { value: '', setCustomValidity: message => { validityMessage = message; } },
    };
    state.$root.querySelector = selector => {
        const name = selector.match(/name=\"([^\"]+)/)?.[1];
        const field = fields[name];
        return selector.includes(':checked') && !field?.checked ? null : (field || null);
    };
    state.validateStageConstraints(3);

    assert.ok(validityMessage);
    fields.current_position.value = 'Fujairah anchorage';
    state.validateStageConstraints(3);
    assert.equal(validityMessage, '');
});

test('submitting before review advances one stage instead of posting early', () => {
    const { state } = setup();
    let prevented = false;

    state.submit({ preventDefault: () => { prevented = true; } });

    assert.equal(prevented, true);
    assert.equal(state.step, 2);
});

test('final submission returns to the first invalid stage', () => {
    const { state, fields } = setup();
    const invalid = validField(false);
    state.step = 5;
    fields.set(5, [validField()]);
    fields.set(3, [invalid]);
    let prevented = false;

    state.submit({ preventDefault: () => { prevented = true; } });

    assert.equal(prevented, true);
    assert.equal(state.step, 3);
    assert.equal(invalid.focused, true);
});

test('final submission is allowed when every stage is valid', () => {
    const { state, fields } = setup();
    state.step = 5;
    for (let step = 1; step <= 5; step++) fields.set(step, [validField()]);
    let prevented = false;

    state.submit({ preventDefault: () => { prevented = true; } });

    assert.equal(prevented, false);
});


test('port filtering supports UAE and Gulf port groups and localized search', () => {
    const { state } = setup();
    state.portSearch.loading = '';
    assert.equal(state.matchesPort('Jebel Ali Port', 'AE', 'loading'), true);
    state.portFilters.loading = 'uae';
    assert.equal(state.matchesPort('Jebel Ali Port', 'AE', 'loading'), true);
    assert.equal(state.matchesPort('Umm Qasr Port', 'IQ', 'loading'), false);
    state.portFilters.loading = 'gulf';
    assert.equal(state.matchesPort('Umm Qasr Port', 'IQ', 'loading'), true);
    assert.equal(state.matchesPort('Jebel Ali Port', 'AE', 'loading'), false);
    state.portFilters.loading = 'all';
    state.portSearch.loading = 'iraq';
    state.$root = { ...state.$root, querySelector: () => null };
    const originalConfig = { portCountries: { IQ: 'Iraq' } };
    const withCountries = wizard({ ...originalConfig, kind: 'cargo' });
    withCountries.portSearch.loading = 'iraq';
    assert.equal(withCountries.matchesPort('Umm Qasr Port', 'IQ', 'loading'), true);
});

test('selected port summary uses the chosen card name and country', () => {
    const { state } = setup();
    const card = {
        querySelector: selector => ({
            '.marine-port-name': { textContent: 'Jebel Ali Port' },
            '.marine-port-country': { textContent: 'United Arab Emirates' },
        })[selector],
    };
    state.$root.querySelector = () => ({ closest: () => card });
    assert.equal(state.selectedPortLabel('loading_port_id'), 'Jebel Ali Port · United Arab Emirates');
});


test('changing a selected discharging port clears a stale same-port validation error', () => {
    const { state } = setup('cargo');
    const validity = new Map();
    const options = [
        { value: '10', checked: true, setCustomValidity: message => validity.set('10', message) },
        { value: '11', checked: false, setCustomValidity: message => validity.set('11', message) },
    ];
    let selectedLoading = { value: '10', checked: true };
    let selectedDischarging = options[0];
    const dates = {
        laycan_from: { value: '2026-12-10' },
        laycan_to: { value: '2026-12-11', setCustomValidity() {} },
    };
    state.$root.querySelector = selector => {
        if (selector.includes('loading_port_id')) return selectedLoading;
        if (selector.includes('discharging_port_id')) return selectedDischarging;
        const name = selector.match(/name="([^"]+)/)?.[1];
        return dates[name] || null;
    };
    state.$root.querySelectorAll = () => options;
    state.validateStageConstraints(3);
    assert.ok(validity.get('10'));

    selectedDischarging = options[1];
    selectedLoading = { value: '10', checked: true };
    state.validateStageConstraints(3);
    assert.equal(validity.get('10'), '');
    assert.equal(validity.get('11'), '');
});
