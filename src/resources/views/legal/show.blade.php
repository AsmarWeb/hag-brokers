<x-market-layout>
 <section aria-labelledby="legal-title" class="mx-auto max-w-3xl px-6 py-14" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
  <h1 id="legal-title" class="text-3xl font-bold">{{ $document->{'title_'.$language} }}</h1>
  <p class="my-4 text-sm opacity-70">{{ $language === 'ar' ? 'الإصدار: ' : 'Version: ' }}{{ $document->version }}</p>
  <div class="whitespace-pre-wrap leading-8">{{ $document->{'body_'.$language} }}</div>
 </section>
</x-market-layout>
