<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class DocumentController extends Controller {
 private function own(Request $r, Shipment $s){abort_unless($s->customer_id===$r->user()->customer_id,403);}
 public function index(Request $r,Shipment $shipment){$this->own($r,$shipment);return response()->json(['success'=>true,'data'=>$shipment->documents()->latest()->get()]);}
 public function store(Request $r,Shipment $shipment){$this->own($r,$shipment);$d=$r->validate(['type'=>'required|string|max:100','file'=>'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);$file=$r->file('file');$path=$file->store('shipments/'.$shipment->id,'local');$doc=Document::create(['shipment_id'=>$shipment->id,'type'=>$d['type'],'name'=>$file->getClientOriginalName(),'original_name'=>$file->getClientOriginalName(),'file_path'=>$path,'uploaded_by'=>$r->user()->id]);return response()->json(['success'=>true,'data'=>$doc],201);}
}
