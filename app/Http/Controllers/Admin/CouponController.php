<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index()
    {
        $siteId = Site::first()?->id;

        $coupons = Coupon::where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['action' => 'Create', 'coupon' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);
        $validated['site_id'] = Site::first()?->id;

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        $this->authorizeSite($coupon);

        return view('admin.coupons.form', ['action' => 'Edit', 'coupon' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeSite($coupon);

        $validated = $this->validateCoupon($request, $coupon);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeSite($coupon);

        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted.');
    }

    protected function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $siteId = Site::first()?->id;

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('coupons', 'code')->where('site_id', $siteId)->ignore($coupon?->id),
            ],
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01|max:99999.99',
            'min_subtotal' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'active' => 'nullable|boolean',
        ]);

        if ($validated['type'] === Coupon::TYPE_PERCENT && (float) $validated['value'] > 100) {
            throw ValidationException::withMessages([
                'value' => 'Percentage coupons cannot exceed 100%.',
            ]);
        }

        $validated['code'] = strtoupper($validated['code']);
        $validated['active'] = $request->boolean('active');

        if (empty($validated['min_subtotal'])) {
            $validated['min_subtotal'] = null;
        }

        return $validated;
    }

    protected function authorizeSite(Coupon $coupon): void
    {
        abort_unless($coupon->site_id === Site::first()?->id, 404);
    }
}
