<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\MobileAppSettings;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminMobileAppController extends Controller
{
    public function edit(Request $request, MobileAppSettings $settings)
    {
        abort_unless($request->user()?->hasVerifiedEmail(), 403);

        return view('admin.mobile-app.edit', [
            'settings' => $settings->stored(),
            'categories' => OpportunityCatalog::CATEGORIES,
        ]);
    }

    public function update(Request $request, MobileAppSettings $settings)
    {
        abort_unless($request->user()?->hasVerifiedEmail(), 403);

        $rules = [
            'version' => ['required', 'integer', 'min:0'],
            'app_name' => ['required', 'string', 'max:40'],
            'categories' => ['required', 'array', 'min:1', 'max:30'],
            'categories.*' => ['required', 'distinct', Rule::in(OpportunityCatalog::CATEGORIES)],
            'welcome_enabled' => ['required', 'boolean'],
            'show_marine' => ['required', 'boolean'],
            'category_cards' => ['required', 'array'],
        ];

        foreach (OpportunityCatalog::CATEGORIES as $code) {
            $rules["category_cards.$code"] = ['required', 'array'];
            $rules["category_cards.$code.title_ar"] = ['required', 'string', 'max:100'];
            $rules["category_cards.$code.title_en"] = ['required', 'string', 'max:100'];
            $rules["category_cards.$code.description_ar"] = ['nullable', 'string', 'max:240'];
            $rules["category_cards.$code.description_en"] = ['nullable', 'string', 'max:240'];
            $rules["category_cards.$code.enabled"] = ['required', 'boolean'];
            $rules["category_cards.$code.featured"] = ['required', 'boolean'];
            $rules["category_cards.$code.sort_order"] = ['required', 'integer', 'between:0,999'];
            $rules["category_cards.$code.image"] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072', 'dimensions:max_width=4096,max_height=4096'];
            $rules["category_cards.$code.remove_image"] = ['nullable', 'boolean'];
        }

        foreach (['welcome_title', 'home_title'] as $key) {
            foreach (['ar', 'en'] as $lang) {
                $rules[$key.'_'.$lang] = ['required', 'string', 'max:100'];
            }
        }

        foreach (['welcome_body', 'home_body', 'announcement'] as $key) {
            foreach (['ar', 'en'] as $lang) {
                $rules[$key.'_'.$lang] = ['nullable', 'string', 'max:240'];
            }
        }

        foreach (['welcome', 'home'] as $key) {
            $rules[$key.'_image'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072', 'dimensions:max_width=4096,max_height=4096'];
            $rules['remove_'.$key.'_image'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);
        $uploads = [];

        try {
            foreach (['welcome_image', 'home_image'] as $key) {
                if ($request->hasFile($key)) {
                    $uploads[$key] = $request->file($key)->store('mobile-app', 'public');
                }
            }

            foreach (OpportunityCatalog::CATEGORIES as $code) {
                $key = "category_cards.$code.image";
                if ($request->hasFile($key)) {
                    $uploads[$code] = $request->file($key)->store('mobile-app/categories', 'public');
                }
            }

            DB::transaction(function () use ($settings, $request, $data, $uploads) {
                DB::table('mobile_app_settings')->insertOrIgnore([
                    'id' => 1,
                    'content' => json_encode($settings->defaults(), JSON_THROW_ON_ERROR),
                    'version' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('mobile_app_settings')->where('id', 1)->lockForUpdate()->first();
                abort_if((int) $row->version !== (int) $data['version'], 409, 'Settings changed. Reload before saving.');

                $content = array_replace(
                    $settings->defaults(),
                    json_decode($row->content, true, 512, JSON_THROW_ON_ERROR)
                );

                foreach (array_keys($settings->defaults()) as $key) {
                    if (! in_array($key, ['welcome_image', 'home_image', 'category_cards'], true)) {
                        $content[$key] = $data[$key] ?? '';
                    }
                }

                foreach (['welcome_image', 'home_image'] as $key) {
                    if ($request->boolean('remove_'.$key)) {
                        $content[$key] = null;
                    }
                    if (isset($uploads[$key])) {
                        $content[$key] = $uploads[$key];
                    }
                }

                $content['welcome_enabled'] = $request->boolean('welcome_enabled');
                $content['show_marine'] = $request->boolean('show_marine');

                $existingCards = $content['category_cards'] ?? [];
                foreach (OpportunityCatalog::CATEGORIES as $code) {
                    $card = $data['category_cards'][$code];
                    $saved = $existingCards[$code] ?? [];

                    $card['code'] = $code;
                    $card['image'] = $saved['image'] ?? null;

                    if ($request->boolean("category_cards.$code.remove_image")) {
                        $card['image'] = null;
                    }
                    if (isset($uploads[$code])) {
                        $card['image'] = $uploads[$code];
                    }

                    $card['enabled'] = $request->boolean("category_cards.$code.enabled");
                    $card['featured'] = $request->boolean("category_cards.$code.featured");
                    $content['category_cards'][$code] = $card;
                }

                $json = json_encode($content, JSON_THROW_ON_ERROR);
                DB::table('mobile_app_settings')->where('id', 1)->update([
                    'content' => $json,
                    'version' => $row->version + 1,
                    'updated_at' => now(),
                ]);
                DB::table('mobile_app_setting_audits')->insert([
                    'actor_id' => $request->user()->id,
                    'version' => $row->version + 1,
                    'content' => $json,
                    'created_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($uploads as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return back()->with('success', app()->isLocale('ar') ? 'تم تحديث محتوى التطبيق والمجالات.' : 'App content and marketplace categories updated.');
    }
}
