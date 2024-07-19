<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Date;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $request->validate([
                'filter' => 'nullable|in:after,before',
                'per_page' => 'nullable|integer|min:1'
            ]);

            //select all columns of Announcement table
            $query = Announcement::select("*");

            if ($request->has('filter')) {

                $currentDateTime = Carbon::now();

                $filter = $request->input('filter');
                if ($filter === 'after') {
                    $query->where("date", '>', $currentDateTime);
                } else if ($filter === 'before') {
                    $query->where("date", '<', $currentDateTime);
                }
            }

            $parPage = $request->input('per_page');

            $announcements = $query->paginate($parPage, $columns = ['*'], $pageName = 'announcements');

            return ok("success", $announcements);

        } catch (\Exception $e) {
            return error('Failed to fetch announcements', $e->getMessage(), 'internal_server_error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'date' => 'required|date_format:Y-m-d\TH:i:s',
        ]);

        try {

            $Announcement = Announcement::create($request->only("message", 'date'));
            return ok('Announcement created', $Announcement);

        } catch (\Exception $e) {

            return error('Failed to create announcements', $e->getMessage(), 'internal_server_error');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        return ok('Announcements', $announcement);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'message' => 'string',
            'date' => 'date_format:Y-m-d\TH:i:s',
        ]);
        try {
            $currentDate = Carbon::today();

            // Get the announcement
            $announcement = Announcement::findOrFail($id);

            if ($announcement) {
                $announcementDate = Carbon::parse($announcement->date);
                // time not getting compare
                if ($announcementDate->gte($currentDate)) {

                    $announcement->fill($request->only('message', 'date', 'time'));
                    $announcement->save();
                    return ok('Announcement updated', $announcement);
                } else {
                    return error('Announcement date is older than current date', null, 'validation');
                }
            }
        } catch (\Exception $e) {
            return error('Announcement not found', $e->getMessage(), 'notfound');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $request->validate([
            'forceDelete' => 'nullable|boolean',
        ]);

        try {
            $currentDate = Carbon::today();

            //get the announcement
            $announcement = Announcement::findOrFail($id);

            //convert date in $announcement to carbon date for compression
            $announcementDate = Carbon::parse($announcement->date);

            //force delete or soft delete
            // $forceDelete = $request->input('forceDelete', false);

            if ($announcementDate->gte($currentDate)) {

                if ($request->is_force_delete) {
                    $announcement->forceDelete();

                } else {
                    $announcement->delete();
                }
            } else {
                return error('Announcement date is older than current date', null, '');
            }

            return ok('Announcement deleted');
        } catch (\Exception $e) {
            return error('Failed to delete announcement', $e->getMessage());
        }
    }
}
