<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyBillingAccount;

class AdminCompanyBillingController extends Controller
{
    public function index()
    {
        return view('admin.platform.billing', ['accounts' => CompanyBillingAccount::latest('id')->paginate(20)]);
    }
}
