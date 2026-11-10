import 'intl-tel-input/styles';
import intlTelInput from 'intl-tel-input';
import "./bootstrap";

import Alpine from "alpinejs";

import tradeOpportunity from "./trade-opportunity";

Alpine.data("tradeOpportunity", tradeOpportunity);
import bringDeal from "./bring-deal";
Alpine.data("bringDeal", bringDeal);
window.Alpine = Alpine;

Alpine.start();

import "./trade-room-live";


// HAG_PHONE_SELECTOR
function initializeHagPhoneSelector() {
    const phoneInput = document.querySelector('#phone');
    const countryInput = document.querySelector('#phone_country_code');

    if (!phoneInput || !countryInput || phoneInput.dataset.itiInitialized) {
        return;
    }

    const iti = intlTelInput(phoneInput, {
        initialCountry: (countryInput.value || 'AE').toLowerCase(),
        separateDialCode: true,
        nationalMode: true,
        countrySearch: true,
        autoPlaceholder: 'polite',
        loadUtils: () => import('intl-tel-input/utils'),
    });

    phoneInput.dataset.itiInitialized = 'true';

    const syncCountry = () => {
        const country = iti.getSelectedCountryData();
        if (country && country.iso2) {
            countryInput.value = country.iso2.toUpperCase();
        }
    };

    phoneInput.addEventListener('countrychange', syncCountry);
    phoneInput.closest('form')?.addEventListener('submit', syncCountry);
    syncCountry();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHagPhoneSelector);
} else {
    initializeHagPhoneSelector();
}
