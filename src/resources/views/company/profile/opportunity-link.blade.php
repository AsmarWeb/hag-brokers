@if($opportunity->company_workspace_id)
@php $publicCompanyProfile=\App\Models\CompanyPublicProfile::where('workspace_id',$opportunity->company_workspace_id)->where('is_published',true)->first(); @endphp
@if($publicCompanyProfile)<div class="market-wrap"><a class="trade-link" href="{{ route('companies.show',['slug'=>$publicCompanyProfile->slug,'lang'=>app()->getLocale()]) }}">{{ __('company_profile.title') }} · {{ $publicCompanyProfile->text('name') }} ↗</a></div>@endif
@endif
