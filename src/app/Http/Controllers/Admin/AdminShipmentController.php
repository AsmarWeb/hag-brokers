<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Vessel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shipment::with(['customer', 'originPort', 'destinationPort', 'vessel', 'carrier']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($query) use ($search) {
                $query->where('booking_no', 'like', "%{$search}%")
                    ->orWhere('shipment_no', 'like', "%{$search}%");
            });
        }
        return view('admin.shipments.index', [
            'shipments' => $query->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Shipment $shipment): View
    {
        $shipment->load([
            'customer', 'quotation', 'carrier', 'originPort', 'destinationPort',
            'vessel', 'containers', 'documents', 'events',
        ]);
        return view('admin.shipments.show', ['shipment' => $shipment]);
    }

    public function create(): View
    {
        return view('admin.shipments.create', $this->formData());
    }

    public function edit(Shipment $shipment): View
    {
        return view('admin.shipments.edit', $this->formData() + ['shipment' => $shipment]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['shipment_no'] = 'HAG-SHP-'.Str::upper((string) Str::ulid());
        $shipment = Shipment::create($data);
        return redirect()->route('admin.shipments.show', $shipment)->with('success',
            app()->getLocale() === 'ar' ? 'تم إنشاء الشحنة بنجاح.' : 'Shipment created successfully.');
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $shipment->update($this->validatedData($request, $shipment));
        return redirect()->route('admin.shipments.show', $shipment)->with('success',
            app()->getLocale() === 'ar' ? 'تم تحديث الشحنة بنجاح.' : 'Shipment updated successfully.');
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $blocked = DB::transaction(function () use ($shipment) {
            $target = Shipment::whereKey($shipment->getKey())->lockForUpdate()->firstOrFail();
            $documents = DB::table('documents')->where(function ($query) use ($target) {
                $query->where('shipment_id', $target->id)
                    ->orWhere(function ($query) use ($target) {
                        $query->where('documentable_id', $target->id)
                            ->whereIn('documentable_type', [$target->getMorphClass(), 'shipment']);
                    });
            })->exists();
            if ($target->containers()->exists() || $target->events()->exists() || $documents) {
                return true;
            }
            $target->delete();
            return false;
        });
        $isAr = app()->getLocale() === 'ar';
        return redirect()->route('admin.shipments.index')->with(
            $blocked ? 'error' : 'success',
            $blocked
                ? ($isAr ? 'لا يمكن حذف شحنة مرتبطة بحاويات أو مستندات أو أحداث تتبع.' : 'Shipments with containers, documents or tracking events cannot be deleted.')
                : ($isAr ? 'تم حذف الشحنة بنجاح.' : 'Shipment deleted successfully.')
        );
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('company_name')->get(),
            'quotations' => Quotation::with(['customer', 'originPort', 'destinationPort'])->latest()->get(),
            'carriers' => Carrier::orderBy('name')->get(),
            'vessels' => Vessel::with('carrier')->orderBy('name')->get(),
            'ports' => Port::orderBy('name')->get(),
        ];
    }

    private function validatedData(Request $request, ?Shipment $shipment = null): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'quotation_id' => ['nullable', 'integer', 'exists:quotations,id'],
            'booking_no' => ['nullable', 'string', 'max:255', Rule::unique('shipments', 'booking_no')->ignore($shipment)],
            'origin_port_id' => ['required', 'integer', 'exists:ports,id'],
            'destination_port_id' => ['required', 'integer', 'exists:ports,id', 'different:origin_port_id'],
            'carrier_id' => ['nullable', 'integer', 'exists:carriers,id'],
            'vessel_id' => ['nullable', 'integer', 'exists:vessels,id'],
            'cargo_type' => ['nullable', 'string', 'max:255'],
            'container_type' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in(['pending', 'booked', 'confirmed', 'in_transit', 'arrived', 'delivered', 'cancelled'])],
            'preferred_date' => ['nullable', 'date'],
            'etd' => ['nullable', 'date'],
            'eta' => ['nullable', 'date'],
            'actual_departure' => ['nullable', 'date'],
            'actual_arrival' => ['nullable', 'date'],
        ]);
        $errors = [];
        $isAr = app()->getLocale() === 'ar';
        if (!empty($data['quotation_id'])) {
            $quotation = Quotation::findOrFail($data['quotation_id']);
            if ((int) $quotation->customer_id !== (int) $data['customer_id']) {
                $errors['quotation_id'] = $isAr ? 'عرض السعر لا يتبع العميل المختار.' : 'The quotation belongs to another customer.';
            }
        }
        if (!empty($data['vessel_id']) && !empty($data['carrier_id'])) {
            $vessel = Vessel::findOrFail($data['vessel_id']);
            if ($vessel->carrier_id !== null && (int) $vessel->carrier_id !== (int) $data['carrier_id']) {
                $errors['vessel_id'] = $isAr ? 'السفينة لا تتبع الناقل المختار.' : 'The vessel belongs to another carrier.';
            }
        }
        foreach ([['etd', 'eta'], ['actual_departure', 'actual_arrival']] as [$start, $end]) {
            if (!empty($data[$start]) && !empty($data[$end]) && strtotime($data[$end]) < strtotime($data[$start])) {
                $errors[$end] = $isAr ? 'تاريخ الوصول يجب ألا يسبق تاريخ المغادرة.' : 'Arrival cannot precede departure.';
            }
        }
        if ($errors) throw ValidationException::withMessages($errors);
        return $data + array_fill_keys([
            'quotation_id', 'booking_no', 'carrier_id', 'vessel_id', 'cargo_type',
            'container_type', 'weight', 'preferred_date', 'etd', 'eta',
            'actual_departure', 'actual_arrival',
        ], null);
    }
}
