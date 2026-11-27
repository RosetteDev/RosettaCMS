<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Month-grid calendar for the event custom post type.
 *
 * Events use their published_at as the event date, so an event is shown on
 * the day its published_at falls. Scheduled events (future published_at)
 * appear in the grid and are flagged as scheduled.
 */
class CalendarController extends Controller
{
    /**
     * Abort 403 when the current user lacks the given permission for events.
     */
    protected function enforce(string $action): void
    {
        if (!auth()->user()->canPerform('event', $action)) {
            abort(403, 'You do not have permission to ' . $action . ' events.');
        }
    }

    /**
     * Month grid of events.
     */
    public function index(Request $request)
    {
        $this->enforce('view');

        $month = Carbon::createFromFormat('Y-m', (string) $request->input('month', now()->format('Y-m')))
            ->startOfMonth();

        $events = Post::where('post_type', 'event')
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [$month->copy()->startOfMonth()->startOfDay(), $month->copy()->endOfMonth()->endOfDay()])
            ->orderBy('published_at')
            ->get();

        $byDay = $events->groupBy(fn ($event) => $event->published_at->format('Y-m-d'));

        // Grid starts on the first day of the month aligned to the week start.
        $gridStart = $month->copy()->startOfMonth()->startOfWeek();
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek();

        $weeks = [];
        $day = $gridStart->copy();
        while ($day->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $day->format('Y-m-d');
                $week[] = [
                    'date' => $day->copy(),
                    'inMonth' => $day->month === $month->month && $day->year === $month->year,
                    'isToday' => $day->isToday(),
                    'events' => $byDay->get($date, collect()),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

        $upcoming = Post::where('post_type', 'event')
            ->whereNotNull('published_at')
            ->where('published_at', '>=', now()->startOfDay())
            ->where('status', '!=', 'trash')
            ->orderBy('published_at')
            ->limit(5)
            ->get();

        return view('admin.events.calendar', [
            'month' => $month,
            'weeks' => $weeks,
            'upcoming' => $upcoming,
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'eventCount' => $events->count(),
        ]);
    }
}
