@if (session('success'))<p role="status" class="rounded-xl border border-emerald-400 p-4">{{ session('success') }}</p>@endif
@if ($errors->any())<ul role="alert" class="list-disc rounded-xl border border-red-400 p-5 ps-10">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
