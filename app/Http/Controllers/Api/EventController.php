<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\Gate;

class EventController extends RoutingController
{
    use CanLoadRelationships;
    use AuthorizesRequests;

    public function __construct(){
        $this->middleware('auth:sanctum')->except(['index','show']);

        $this->middleware('throttle:api')->only(['store','destroy','update']);
        $this->authorizeResource(Event::class, 'event');
    }

    private array $relations = ['user','attendees','attendees.user'];






    public function index()
    {
        $query = $this->loadRelationships(Event::query());


        return EventResource::collection(
            $query->latest()->paginate()
        );
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Another apporach
        /*$data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $data['user_id'] = 1;  // Manually adding 'user_id' to the validated data
        $event = Event::create($data);
        */

        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id,
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user','attendees');
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Currently using policies so not needed ....

        // if(Gate::denies('update-event', $event)){
        //     abort(403, 'You are not authorized to update this event');
        // }

        // the below code does the same thing as above
        // Gate::authorize('update-post', $event);


        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ])
        );

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        // return response()->json([
        //     'message' => 'Event Deleted Successfully'
        // ]);

        // if you want no message/content to be displayed then do:
        return response(status: 204);
    }
}
