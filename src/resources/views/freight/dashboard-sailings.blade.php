<x-app-layout>
    <div class="text-slate-900 dark:text-white">
        @include('freight.sailing-search', [
            'searchRoute' => 'dashboard.freight.sailings',
            'panelClass' => 'border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900',
        ])
    </div>
</x-app-layout>
