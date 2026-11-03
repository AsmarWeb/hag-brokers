import "./bootstrap";

import Alpine from "alpinejs";

import tradeOpportunity from "./trade-opportunity";

Alpine.data("tradeOpportunity", tradeOpportunity);
import bringDeal from "./bring-deal";
Alpine.data("bringDeal", bringDeal);
window.Alpine = Alpine;

Alpine.start();

import "./trade-room-live";
