export default function tradeOpportunity(config) {
    return {
        title: config.title || '', description: config.description || '', type: config.type || 'sell',
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
