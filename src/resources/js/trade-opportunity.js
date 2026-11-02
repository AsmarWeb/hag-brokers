export default function tradeOpportunity(config) {
    return {
        title: config.title || '', description: config.description || '', type: config.type || 'sell',
        showAllSectors: false, category: config.category || '', step: config.step || 1,
        stepLabels: config.stepLabels || [], summary: {}, files: [], mediaError: '', dragging: false, mediaSequence: 0,
        get selectedTypeLabel() { return config.typeLabels?.[this.type] || this.type; },
        get selectedCategoryLabel() { return config.categoryLabels?.[this.category] || this.category; },
        get mediaMegabytes() { return (this.files.reduce((sum, item) => sum + item.file.size, 0) / 1048576).toFixed(1); },
        invalidInStep(step) {
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
            this.$nextTick(() => { field.focus(); field.reportValidity(); });
        },
        go(target) {
            target = Math.max(1, Math.min(5, target));
            if (target > this.step) {
                for (let stage = 1; stage < target; stage++) {
                    const invalid = this.invalidInStep(stage);
                    if (invalid) { this.showInvalid(stage, invalid); return; }
                }
            }
            this.step = target;
            this.updateSummary();
            this.$nextTick(() => this.$root.querySelector('.trade-wizard-top')?.scrollIntoView?.({ block: 'start', behavior: 'smooth' }));
        },
        next() { this.go(this.step + 1); },
        updateSummary() {
            const values = new FormData(this.$root);
            const unit = values.get('commercial_details[unit]');
            this.summary = {
                country: values.get('country') || '—', city: values.get('city') || '—',
                product_name: values.get('commercial_details[product_name]') || '—',
                quantity: (values.get('commercial_details[quantity]') || '—') + ' ' + (config.unitLabels?.[unit] || ''),
                price: (values.get('commercial_details[unit_price]') || '—') + ' ' + (values.get('currency') || config.currency || ''),
            };
        },
        submit(event) {
            if (this.step !== 5) { event.preventDefault(); this.next(); return; }
            for (let stage = 1; stage <= 5; stage++) {
                const invalid = this.invalidInStep(stage);
                if (invalid) { event.preventDefault(); this.showInvalid(stage, invalid); return; }
            }
            if (this.step !== 5) { event.preventDefault(); this.go(5); }
        },
        chooseMedia(kind) {
            const input = this.$refs.mediaInput;
            input.accept = kind === 'video' ? 'video/mp4,video/quicktime,video/webm' : 'image/jpeg,image/png,image/webp';
            input.click();
        },
        addMedia(event) { this.mergeMedia(Array.from(event.target.files || [])); },
        dropMedia(event) { this.dragging = false; this.mergeMedia(Array.from(event.dataTransfer?.files || [])); },
        mergeMedia(incoming) {
            const added = [];
            for (const file of incoming) {
                if (!this.files.some(item => item.file.name === file.name && item.file.size === file.size && item.file.lastModified === file.lastModified)
                    && !added.some(other => other.name === file.name && other.size === file.size && other.lastModified === file.lastModified)) added.push(file);
            }
            const combined = [...this.files.map(item => item.file), ...added];
            const allowed = file => /\.(jpe?g|png|webp|mp4|mov|webm)$/i.test(file.name);
            if (combined.length > 10 || combined.some(file => !allowed(file)) || combined.reduce((sum, file) => sum + file.size, 0) > 20 * 1048576) {
                this.mediaError = config.mediaLabel || config.mediaError;
                this.syncMediaInput();
                return;
            }
            const next = added.map(file => ({ id: ++this.mediaSequence, file, kind: /\.(mp4|mov|webm)$/i.test(file.name) ? 'video' : 'image', url: URL.createObjectURL(file) }));
            this.files = [...this.files, ...next]; this.mediaError = '';
            this.syncMediaInput();
        },
        syncMediaInput() {
            const input = this.$refs.mediaInput;
            if (!input) return;
            try {
                const transfer = new DataTransfer();
                this.files.forEach(item => transfer.items.add(item.file));
                input.files = transfer.files;
                input.setCustomValidity('');
            } catch {
                // Fail visibly rather than submit files different from the previews.
                input.value = ''; this.files.forEach(item => URL.revokeObjectURL(item.url));
                this.files = []; this.mediaError = config.mediaLabel || config.mediaError;
            }
        },
        removeMedia(id) {
            const item = this.files.find(item => item.id === id);
            if (item) URL.revokeObjectURL(item.url);
            this.files = this.files.filter(item => item.id !== id); this.mediaError = ''; this.syncMediaInput();
        },
        destroy() { this.files.forEach(item => URL.revokeObjectURL(item.url)); },
        consent: false, busy: false, result: null, error: '', original: null,
        checkMedia(event) {
            const files = Array.from(event.target.files || []);
            const invalid = files.length > 10 || files.reduce((sum, file) => sum + file.size, 0) > 20 * 1024 * 1024;
            event.target.setCustomValidity(invalid ? config.mediaError : '');
            if (invalid) event.target.reportValidity();
        },
        async review(mode) {
            if (this.busy) return;
            if (mode === 'ai' && !this.consent) { this.error = config.consentError; return; }
            this.busy = true; this.error = ''; this.result = null;
            const form = this.$root;
            const values = new FormData(form);
            const payload = { mode, consent: this.consent, language: config.language, commercial_details: {} };
            for (const name of ['title', 'description', 'opportunity_type', 'category', 'country', 'city']) {
                payload[name] = values.get(name) || '';
            }
            for (const name of ['product_name', 'specifications', 'quantity', 'unit', 'condition', 'delivery_terms', 'payment_terms', 'lead_time_days', 'target_market', 'certifications']) {
                const value = values.get(`commercial_details[${name}]`);
                if (value !== null && value !== '') payload.commercial_details[name] = value;
            }
            try {
                const response = await fetch(config.endpoint, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': values.get('_token') },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) {
                    this.error = data.message || config.error;
                    return;
                }
                this.result = data;
            } catch { this.error = config.error; }
            finally { this.busy = false; }
        },
        apply() {
            if (!this.result?.suggestion) return;
            this.original = { title: this.title, description: this.description };
            this.title = this.result.suggestion.title;
            this.description = this.result.suggestion.description;
            this.result = null;
        },
        undo() {
            if (!this.original) return;
            this.title = this.original.title; this.description = this.original.description;
            this.original = null;
        },
    };
}
