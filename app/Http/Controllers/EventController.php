<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Image;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
  public function index()
  {
    $upcomingEvents = Event::with(['image'])
      ->published()
      ->upcoming()
      ->orderBy('date', 'asc')
      ->get();

    $pastEvents = Event::with(['image'])
      ->published()
      ->past()
      ->orderBy('date', 'desc')
      ->get();

    return Inertia::render('events/Index', [
      'upcomingEvents' => $upcomingEvents,
      'pastEvents' => $pastEvents
    ]);
  }

  public function create()
  {
    return Inertia::render('events/Create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'date' => 'required|date',
      'end_date' => 'nullable|date|after_or_equal:date',
      'location' => 'nullable|string|max:255',
      'image' => 'nullable|integer',
    ]);

    $event = Event::create([
      'title' => $request->title,
      'description' => $request->description,
      'date' => $request->date,
      'end_date' => $request->end_date,
      'location' => $request->location,
      'status' => 'published',
    ]);

    if ($request->has('image') && $request->image) {
      Image::where('id', $request->image)
        ->update([
          'imageable_type' => Event::class,
          'imageable_id' => $event->id,
        ]);
    }

    return redirect()
      ->route('events')
      ->with('success', 'Event created successfully!');
  }

  public function show(Event $event)
  {
    $event->load(['image']);

    return Inertia::render('events/Show', [
      'event' => $event
    ]);
  }

  public function edit(Event $event)
  {

    $event->load(['image']);

    return Inertia::render('events/Edit', [
      'event' => $event
    ]);
  }

  public function update(Request $request, Event $event)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'date' => 'required|date',
      'end_date' => 'nullable|date|after_or_equal:date',
      'location' => 'nullable|string|max:255',
      'image' => 'nullable|integer',
    ]);

    $event->update([
      'title' => $request->title,
      'description' => $request->description,
      'date' => $request->date,
      'end_date' => $request->end_date,
      'location' => $request->location,
    ]);

    // Update image
    if ($request->has('image')) {
      // Remove old image
      Image::where('imageable_type', Event::class)
        ->where('imageable_id', $event->id)
        ->update([
          'imageable_type' => null,
          'imageable_id' => null,
        ]);

      // Add new image
      if ($request->image) {
        Image::where('id', $request->image)
          ->update([
            'imageable_type' => Event::class,
            'imageable_id' => $event->id,
          ]);
      }
    }

    return redirect()
      ->route('events')
      ->with('success', 'Event updated successfully!');
  }

  public function destroy(Event $event)
  {
    $event->delete();

    return redirect()
      ->route('events')
      ->with('success', 'Event deleted successfully!');
  }
}
