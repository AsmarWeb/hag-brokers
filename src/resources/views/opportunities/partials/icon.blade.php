@php
$paths = [
'sell' => 'M3 7h18l-2 13H5L3 7Zm4 0V5a5 5 0 0 1 10 0v2',
'buy' => 'M3 3h2l3 13h10l3-9H6M9 21h.01M18 21h.01',
'service_offer' => 'M14 6a4 4 0 0 0-5 5L3 17l4 4 6-6a4 4 0 0 0 5-5l-3 3-4-4 3-3Z',
'service_request' => 'M5 3h14v18H5V3Zm4 5h6M9 12h6M9 16h3',
'partnership' => 'm3 12 4-6 5 2 5-2 4 6-6 7-3-1-3 1-6-7Zm5-1 4 3 4-3',
'distribution' => 'M3 6h12v12H3V6Zm12 5h4l3 4v3h-7M6 21h.01M18 21h.01',
'food' => 'M4 3v7h6V3M7 10v11M18 3v18M18 3c-5 3-5 9 0 9',
'agriculture' => 'M20 3C7 3 3 9 7 17c8 4 14-2 13-14ZM4 21 15 10',
'construction' => 'm3 10 9-7 9 7M5 9v12h14V9M9 21v-8h6v8',
'machinery' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2',
'electronics' => 'M5 5h14v14H5V5ZM9 9h6v6H9V9ZM9 2v3m6-3v3M9 19v3m6-3v3M2 9h3m-3 6h3m14-6h3m-3 6h3',
'consumer_goods' => 'm3 7 9-4 9 4-9 4-9-4Zm0 0v10l9 4 9-4V7M12 11v10',
'media' => 'M3 5h18v14H3V5Zm0 10 5-5 4 4 3-3 6 6M16 8h.01',
'marine' => 'M3 16c3 3 5 3 9 0 4 3 6 3 9 0M5 14l2-5h10l2 5M12 3v6',
'other' => 'M3 3h7v7H3V3Zm11 0h7v7h-7V3ZM3 14h7v7H3v-7Zm11 0h7v7h-7v-7',
];
$aliases = ['technology'=>'electronics','professional_services'=>'service_offer','services'=>'service_offer','manufacturing'=>'machinery','vessel_brokerage'=>'marine','sea_freight'=>'marine','logistics'=>'distribution','import'=>'distribution','export'=>'distribution','procurement'=>'buy'];
$path = $paths[$aliases[$name] ?? $name] ?? $paths['other'];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}" /></svg>
