<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
class NotificationController extends Controller {
 public function index(Request $r){return response()->json(['success'=>true,'data'=>Notification::where('user_id',$r->user()->id)->latest()->paginate(20)]);}
 public function markRead(Request $r,Notification $notification){abort_unless($notification->user_id===$r->user()->id,403);$notification->update(['is_read'=>true]);return response()->json(['success'=>true,'data'=>$notification]);}
}
