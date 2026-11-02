{{-- Shared, first-party source runs before styles paint on public pages. --}}
<script>{!! file_get_contents(resource_path('js/public-theme.js')) !!}</script>
