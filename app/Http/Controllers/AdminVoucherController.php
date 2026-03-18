<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminVoucherController extends Controller
{
    public function index()
    {
        return inertia('Admin/Vouchers/Index', [
            'vouchers' => Voucher::latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Vouchers/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['min_order'] = $validated['min_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return inertia('Admin/Vouchers/Edit', ['voucher' => $voucher]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $this->validatedData($request, $voucher);
        $validated['min_order'] = $validated['min_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $normalizedCode = strtoupper(trim((string) $request->input('code')));
        $request->merge(['code' => $normalizedCode]);

        $codeRule = Rule::unique('vouchers', 'code');

        if ($voucher !== null) {
            $codeRule = $codeRule->ignore($voucher);
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $codeRule],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'integer', 'min:1'],
            'min_order' => ['nullable', 'integer', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => $voucher === null
                ? ['nullable', 'date', 'after:today']
                : ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
