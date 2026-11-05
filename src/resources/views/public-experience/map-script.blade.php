@if($mapEnabled)
<script>
(function () {
    const data = {{ Illuminate\Support\Js::from($maritimeMap) }};
    const labels = {{ Illuminate\Support\Js::from(['view' => __('public_experience.view'), 'approximate' => __('public_experience.approximate'), 'empty' => __('public_experience.empty'), 'failed' => __('public_experience.map_unavailable'), 'date' => __('public_experience.location_date')]) }};
    const status = document.getElementById('marine-map-status');
    const cards = Array.from(document.querySelectorAll('[data-marine-kind]'));
    const buttons = Array.from(document.querySelectorAll('[data-marine-filter]'));
    let map, assets, ports, active = 'all';
    function node(tag, text) { const el = document.createElement(tag); el.textContent = text; return el; }
    function applyFilter() {
        cards.forEach(card => card.hidden = active !== 'all' && card.dataset.marineKind !== active);
        buttons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.marineFilter === active)));
        const items = data.listings.filter(item => active === 'all' || item.category === active);
        if (status) status.textContent = items.length ? labels.approximate : labels.empty;
        if (!map) return;
        assets.clearLayers();
        items.forEach(item => {
            const content = document.createElement('div'); content.className = 'hag-map-popup';
            if (item.image) { const img = document.createElement('img'); img.src = item.image; img.alt = ''; content.append(img); }
            content.append(node('strong', item.title), node('p', item.location_label), node('p', labels.approximate));
            if (item.location_updated_at) content.append(node('p', labels.date + ': ' + item.location_updated_at + ' UTC'));
            const a = node('a', labels.view); a.href = item.url; content.append(a);
            const icon = L.divIcon({className:'', html:'<span class="hag-map-marker" data-kind="'+item.category+'">'+(item.category==='yacht'?'🛥':item.category==='boat'?'⛵':'🚢')+'</span>',iconSize:[36,36],iconAnchor:[18,18]});
            L.marker([item.latitude,item.longitude],{icon}).bindPopup(content).addTo(assets);
        });
        if (items.length) map.fitBounds(items.map(i=>[i.latitude,i.longitude]), {padding:[40,40],maxZoom:9});
    }
    buttons.forEach(button=>button.addEventListener('click',()=>{active=button.dataset.marineFilter;applyFilter();}));
    if (typeof L === 'undefined') { if(status)status.textContent=labels.failed; return; }
    map = L.map('gulf-map',{scrollWheelZoom:false,minZoom:2,maxZoom:16}).setView([25.2,55.3],6);
    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:16,attribution:'© OpenStreetMap contributors'}).addTo(map);
    tiles.on('tileerror',()=>{if(status)status.textContent=labels.failed;});
    assets = L.layerGroup().addTo(map); ports = L.layerGroup().addTo(map);
    data.ports.forEach(port=>L.circleMarker([port.latitude,port.longitude],{radius:5,color:'#22d3ee',fillOpacity:.8}).bindPopup(node('strong',port.name)).addTo(ports));
    document.getElementById('marine-ports')?.addEventListener('change', e=>e.target.checked?ports.addTo(map):map.removeLayer(ports));
    applyFilter();
    window.addEventListener('resize',()=>map.invalidateSize({pan:false}));
})();
</script>
@endif
