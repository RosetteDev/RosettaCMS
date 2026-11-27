<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomWidget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Display a listing of custom widgets.
     */
    public function index()
    {
        $widgets = CustomWidget::orderBy('priority')->paginate(20);

        return view('admin.widgets.index', compact('widgets'));
    }

    /**
     * Show the form for creating a new widget.
     */
    public function create()
    {
        return view('admin.widgets.form', [
            'action' => 'Create',
            'widget' => new CustomWidget(),
        ]);
    }

    /**
     * Store a newly created widget.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:custom_widgets,slug',
            'content' => 'nullable|string',
            'type' => 'required|in:html,rss,custom',
            'location' => 'required|in:dashboard,sidebar,before-footer,header',
            'context' => 'required|in:normal,side,side_full',
            'priority' => 'nullable|integer|min:0|max:999',
            'enabled' => 'boolean',
        ]);

        CustomWidget::create($validated);

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget created successfully.');
    }

    /**
     * Display the specified widget.
     */
    public function show(CustomWidget $widget)
    {
        return view('admin.widgets.show', compact('widget'));
    }

    /**
     * Show the form for editing the specified widget.
     */
    public function edit(CustomWidget $widget)
    {
        return view('admin.widgets.form', [
            'action' => 'Edit',
            'widget' => $widget,
        ]);
    }

    /**
     * Update the specified widget.
     */
    public function update(Request $request, CustomWidget $widget)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required', 'string', 'max:255',
                unique('custom_widgets', 'slug')->ignore($widget->id),
            ],
            'content' => 'nullable|string',
            'type' => 'required|in:html,rss,custom',
            'location' => 'required|in:dashboard,sidebar,before-footer,header',
            'context' => 'required|in:normal,side,side_full',
            'priority' => 'nullable|integer|min:0|max:999',
            'enabled' => 'boolean',
        ]);

        $widget->update($validated);

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget updated successfully.');
    }

    /**
     * Remove the specified widget.
     */
    public function destroy(CustomWidget $widget)
    {
        $widget->delete();

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget deleted successfully.');
    }

    /**
     * Toggle widget enabled state.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'widget_id' => 'required|exists:custom_widgets,id',
        ]);

        $widget = CustomWidget::find($validated['widget_id']);
        $widget->enabled = !$widget->enabled;
        $widget->save();

        return redirect()->back()->with('success', 'Widget status updated.');
    }
}
