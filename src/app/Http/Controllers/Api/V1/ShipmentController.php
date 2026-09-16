<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
class ShipmentController extends Controller {
 public function index(Request $r){$q=Shipment::with(['originPort','destinationPort','vessel'])->where('customer_id',$r->user()->customer_id);if($r->filled('status'))$q->where('status',$r->string('status'));return response()->json(['success'=>true,'data'=>$q->latest()->paginate($r->integer('limit',10))]);}
 public function show(Request $r,Shipment $shipment){abort_unless($shipment->customer_id===$r->user()->customer_id,403);return response()->json(['success'=>true,'data'=>$shipment->load(['originPort','destinationPort','vessel','containers','documents','events'])]);}
 public function tracking(Request $r,Shipment $shipment){abort_unless($shipment->customer_id===$r->user()->customer_id,403);return response()->json(['success'=>true,'data'=>['shipment'=>$shipment->only(['id','shipment_no','status','eta']),'vessel'=>$shipment->vessel,'events'=>$shipment->events()->latest('event_date')->get()]]);}
 public function dashboard(Request $r){$q=Shipment::where('customer_id',$r->user()->customer_id);return response()->json(['success'=>true,'data'=>['total_shipments'=>(clone$q)->count(),'in_transit'=>(clone$q)->where('status','in_transit')->count(),'pending'=>(clone$q)->where('status','pending')->count(),'delivered'=>(clone$q)->where('status','delivered')->count()]]);}
}
