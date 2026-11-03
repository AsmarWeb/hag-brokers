export default function bringDeal(config = {}) {
    return {
        step: config.step || 1, type: config.type || '', category: config.category || '',
        preferredContact: config.preferredContact || 'whatsapp', showMoreTypes: false, showMoreSectors: false,
        busy: false, summary: {},
        get progress() { return Math.round(this.step / 5 * 100); },
        get typeLabel() { return config.typeLabels?.[this.type] || this.type || config.empty || '—'; },
        get categoryLabel() { return config.categoryLabels?.[this.category] || config.empty || '—'; },
        invalidInStep(step) {
            const section = this.$root.querySelector(`[data-deal-step="${step}"]`);
            for (const field of section?.querySelectorAll('input,select,textarea') || []) {
                if (!field.matches(':disabled') && !field.checkValidity()) return field;
            }
            return null;
        },
        revealInvalid(step, field) {
            this.step = step;
            if (field.name === 'deal_type' && !config.primaryTypes?.includes(this.type)) this.showMoreTypes = true;
            if (field.name === 'category') this.showMoreSectors = true;
            this.$nextTick(() => { field.focus(); field.reportValidity(); });
        },
        updateSummary() {
            const values = new FormData(this.$root);
            const fields = ['title','description','estimated_value','currency','country','city','name','company_name','phone','email'];
            this.summary = Object.fromEntries(fields.map(name => [name, values.get(name) || config.empty || '—']));
            this.summary.contact = config.contactLabels?.[this.preferredContact] || this.preferredContact;
        },
        go(target) {
            target = Math.max(1, Math.min(5, Number(target)));
            if (target > this.step) {
                for (let stage = 1; stage < target; stage++) {
                    const invalid = this.invalidInStep(stage);
                    if (invalid) { this.revealInvalid(stage, invalid); return; }
                }
            }
            this.step = target;
            this.updateSummary();
            this.$nextTick(() => this.$root.querySelector('.deal-wizard-top')?.scrollIntoView?.({block:'start',behavior:'smooth'}));
        },
        enter(event) {
            if (event.target?.tagName === 'TEXTAREA' || event.target?.tagName === 'BUTTON' || ['radio','checkbox'].includes(event.target?.type)) return;
            event.preventDefault();
            if (this.step < 5) this.go(this.step + 1);
        },
        submit(event) {
            if (this.busy) { event.preventDefault(); return; }
            if (this.step !== 5) { event.preventDefault(); this.go(this.step + 1); return; }
            for (let stage = 1; stage <= 5; stage++) {
                const invalid = this.invalidInStep(stage);
                if (invalid) { event.preventDefault(); this.revealInvalid(stage, invalid); return; }
            }
            this.busy = true;
        },
    };
}
