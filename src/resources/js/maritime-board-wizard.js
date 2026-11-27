export default function maritimeBoardWizard(config) {
    return {
        kind: config.kind || 'cargo',
        step: config.step || 1,
        stepLabels: config.stepLabels || [],
        cargoType: config.cargoType || '',
        vesselType: config.vesselType || '',
        portSelections: config.portSelections || {},
        portSearch: { loading: '', discharging: '', current: '' },
        portFilters: { loading: 'all', discharging: 'all', current: 'all' },
        summary: {},

        get selectedTypeLabel() {
            const labels = this.kind === 'cargo' ? config.cargoTypes : config.vesselTypes;
            const selected = this.kind === 'cargo' ? this.cargoType : this.vesselType;

            return labels?.[selected] || '—';
        },

        init() {
            this.$nextTick(() => this.updateSummary());
        },

        matchesPort(name, countryCode, searchKey) {
            const code = String(countryCode || '').toUpperCase();
            const filter = this.portFilters[searchKey] || 'all';
            const gulfCountries = ['SA', 'OM', 'QA', 'BH', 'IQ', 'KW'];

            if (filter === 'uae' && code !== 'AE') return false;
            if (filter === 'gulf' && !gulfCountries.includes(code)) return false;

            const query = String(this.portSearch[searchKey] || '').trim().toLocaleLowerCase();
            if (!query) return true;

            const countryName = config.portCountries?.[code] || code;
            return `${name} ${countryName} ${code}`.toLocaleLowerCase().includes(query);
        },

        portResultsCount(searchKey) {
            // Read reactive filter state so Alpine refreshes the empty-state message.
            const currentSearch = this.portSearch[searchKey];
            const currentFilter = this.portFilters[searchKey];
            void currentSearch;
            void currentFilter;
            return [...this.$root.querySelectorAll(`[data-port-card="${searchKey}"]`)]
                .filter(card => card.offsetParent !== null).length;
        },

        selectedPortLabel(name) {
            const selected = this.$root.querySelector(`[name="${name}"]:checked`);
            const card = selected?.closest('.marine-port-card');
            const port = card?.querySelector('.marine-port-name')?.textContent?.trim();
            const country = card?.querySelector('.marine-port-country')?.textContent?.trim();
            return port ? (country ? `${port} · ${country}` : port) : '—';
        },

        validateStageConstraints(step) {
            if (step !== 3) return;

            if (this.kind === 'cargo') {
                const loadingPort = this.$root.querySelector('[name="loading_port_id"]:checked');
                const dischargingOptions = [...this.$root.querySelectorAll('[name="discharging_port_id"]')];
                const selectedDischargingPort = this.$root.querySelector('[name="discharging_port_id"]:checked');
                const laycanFrom = this.$root.querySelector('[name="laycan_from"]');
                const laycanTo = this.$root.querySelector('[name="laycan_to"]');

                // Clear custom errors from previously selected radio cards before rechecking.
                dischargingOptions.forEach(option => option.setCustomValidity(''));
                const samePort = loadingPort?.value && selectedDischargingPort?.value
                    && loadingPort.value === selectedDischargingPort.value;
                const portToFlag = selectedDischargingPort || dischargingOptions[0];
                if (samePort && portToFlag) portToFlag.setCustomValidity(config.differentPortsMessage);
                if (laycanFrom && laycanTo) {
                    const invalidRange = laycanFrom.value && laycanTo.value && laycanTo.value < laycanFrom.value;
                    laycanTo.setCustomValidity(invalidRange ? config.laycanOrderMessage : '');
                }
            } else {
                const currentPort = this.$root.querySelector('[name="current_port_id"]:checked');
                const currentPosition = this.$root.querySelector('[name="current_position"]');

                if (currentPosition) {
                    const missingPosition = !currentPort && !currentPosition.value.trim();
                    currentPosition.setCustomValidity(missingPosition ? config.positionRequiredMessage : '');
                }
            }
        },

        invalidInStep(step) {
            this.validateStageConstraints(step);

            for (const section of this.$root.querySelectorAll('[data-trade-step]')) {
                if (Number(section.dataset.tradeStep) !== step) continue;

                for (const field of section.querySelectorAll('input, select, textarea')) {
                    if (!field.matches(':disabled') && !field.checkValidity()) return field;
                }
            }

            return null;
        },

        showInvalid(step, field) {
            this.step = step;
            this.$nextTick(() => {
                field.focus();
                field.reportValidity();
            });
        },

        go(target) {
            target = Math.max(1, Math.min(5, target));

            if (target > this.step) {
                for (let stage = 1; stage < target; stage++) {
                    const invalid = this.invalidInStep(stage);
                    if (invalid) {
                        this.showInvalid(stage, invalid);
                        return;
                    }
                }
            }

            this.step = target;
            this.updateSummary();
            this.$nextTick(() => this.$root.querySelector('.trade-wizard-top')?.scrollIntoView?.({
                block: 'start',
                behavior: 'smooth',
            }));
        },

        next() {
            this.go(this.step + 1);
        },

        updateSummary() {
            if (!this.$root) return;

            const values = new FormData(this.$root);
            const value = (name) => values.get(name) || '—';

            this.summary = {
                type: this.selectedTypeLabel,
                description: value('description'),
                quantity: value('quantity'),
                loadingPort: this.selectedPortLabel('loading_port_id'),
                dischargingPort: this.selectedPortLabel('discharging_port_id'),
                laycanFrom: value('laycan_from'),
                laycanTo: value('laycan_to'),
                readinessDate: value('readiness_date'),
                dwtCapacity: value('dwt_capacity'),
                currentPort: this.selectedPortLabel('current_port_id'),
                currentPosition: value('current_position'),
                openDate: value('open_date'),
                preferredTradingArea: value('preferred_trading_area'),
                company: value('company_name'),
                contact: value('contact_name'),
                email: value('email'),
                phone: value('phone'),
            };
        },

        submit(event) {
            if (this.step !== 5) {
                event.preventDefault();
                this.next();
                return;
            }

            for (let stage = 1; stage <= 5; stage++) {
                const invalid = this.invalidInStep(stage);
                if (invalid) {
                    event.preventDefault();
                    this.showInvalid(stage, invalid);
                    return;
                }
            }
        },
    };
}
