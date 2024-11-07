<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller
{
    use CanLoadRelationships;
    use AuthorizesRequests;

    public function __construct(){
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('throttle:api')->only(['store','destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

    private array $relations = ['user'];

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        // $attendee = $event->attendees()->create([
        //     'user_id' => 1
        // ]);

        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => $request->user()->id
            ])
        );
        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        // Currently using policies so not needed below code ....

        // if(Gate::denies('delete-attendee', [$event, $attendee])){
        //     abort(403, "Sorry !! You're not authenticated to delete this attendee");
        // }

        // the above code is same as below
        // Gate::authorize('delete-attendee', [$event, $attendee]);


        $attendee->delete();
        return response(status: 204);
    }
}
