import "./bootstrap";

import Alpine from "alpinejs";

import tradeOpportunity from "./trade-opportunity";

Alpine.data("tradeOpportunity", tradeOpportunity);
window.Alpine = Alpine;

Alpine.start();
