<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * List bookings with status filter and search.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = Booking::query();

        if ($status !== 'all' && in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('service', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderByDesc('date')->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statistics = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'status', 'search', 'statistics'));
    }

    /**
     * Change a booking's status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $booking->update(['status' => $validated['status']]);

        return back()->with('status', 'Booking marked as ' . $validated['status'] . '.');
    }

    /**
     * Delete a booking.
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return back()->with('status', 'Booking deleted.');
    }
}
