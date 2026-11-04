<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminAccountCenterController extends Controller
{
    private function admin(Request $r): void
    {
        abort_unless($r->user()?->hasVerifiedEmail() && $r->user()->hasRole(['admin', 'super-admin']), 403);
    }

    public function index(Request $r)
    {
        $this->admin($r);

        return view('admin.account-center.index', ['documents' => DB::table('platform_legal_documents')->get()->keyBy('slug'),
            'requests' => DB::table('account_deletion_requests as d')->join('users as u', 'u.id', '=', 'd.user_id')->select('d.*', 'u.name', 'u.email')->orderByDesc('d.id')->paginate(20)]);
    }

    public function save(Request $r, string $slug)
    {
        $this->admin($r);
        abort_unless(in_array($slug, ['privacy', 'terms'], true), 404);
        $d = $r->validate(['version' => ['required', 'integer', 'min:0'], 'published' => ['required', 'boolean'],
            'title_ar' => ['required', 'string', 'max:255'], 'title_en' => ['required', 'string', 'max:255'],
            'body_ar' => ['required', 'string', 'max:40000'], 'body_en' => ['required', 'string', 'max:40000']]);
        DB::transaction(function () use ($r, $slug, $d) {
            DB::table('platform_legal_documents')->insertOrIgnore(['slug' => $slug, 'title_ar' => '', 'title_en' => '', 'body_ar' => '', 'body_en' => '', 'published' => false, 'version' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('platform_legal_documents')->where('slug', $slug)->lockForUpdate()->firstOrFail();
            abort_if((int) $row->version !== (int) $d['version'], 409, 'Document changed. Reload before saving.');
            unset($d['version']);
            $data = $d + ['version' => $row->version + 1, 'updated_at' => now()];
            DB::table('platform_legal_documents')->where('id', $row->id)->update($data);
            DB::table('platform_legal_audits')->insert(['slug' => $slug, 'version' => $data['version'], 'actor_id' => $r->user()->id, 'snapshot' => json_encode($d, JSON_THROW_ON_ERROR), 'created_at' => now()]);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حفظ المستند.' : 'Document saved.');
    }

    public function review(Request $r, int $id)
    {
        $this->admin($r);
        $d = $r->validate(['version' => ['required', 'integer', 'min:1']]);
        $n = DB::table('account_deletion_requests')->where('id', $id)->where('version', $d['version'])->where('status', 'pending')->update(['status' => 'reviewing', 'reviewed_by' => $r->user()->id, 'reviewed_at' => now(), 'version' => $d['version'] + 1, 'updated_at' => now()]);
        abort_unless($n, 409, 'Request changed. Reload.');

        return back();
    }
}
