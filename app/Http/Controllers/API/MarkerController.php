<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Marker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Hazard;
use App\Models\MarkerHazard;
use App\Models\User;
use App\Models\MarkerComment;
use App\Models\GroupMember;
use App\Models\Group;
use App\Models\MarkerTodo;
use App\Models\MarkerNote;
use App\Models\MarkerLink;
use App\Models\MarkerImg;
use App\Models\TempData;
use App\Helpers\Helper;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Imports\MarkersImport;

class MarkerController extends BaseController
{
    // Create a new marker
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user_id'   => 'required|exists:users,id', // Ensure user_id exists in the users table
            'lat'       => 'required',
            'long'      => 'required',
            'icon' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color'     => 'required|string',
            'group_id'     => 'nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if ($request->hasFile('icon')) {
            $image = $request->file('icon');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imageUrl = url('images/' . $imageName);
            $input['icon'] = $imageUrl;
        }
        $marker = Marker::create($input);
        if (isset($request->group_id) && $request->group_id != 0) {
            $userIds = GroupMember::where('group_id', $request->group_id)->where('user_id',"!=",$request->user_id)->pluck('user_id')->toArray();
            $user =User::with('organization')->where('id', $request->user_id)->first();
            $group =Group::where('id', $request->group_id)->first();
            $user->group =  $group;
            $userIds[] =$user->organization->admin_id;
            $title = $user->name." has added a new marker ".$request->title." in ".$group->name. ' group';
            Helper::sendFcmNotification($userIds,  $title,  $request->description, "",$user);
        }
        return $this->sendResponse($marker,'Marker Saved');
    }
    // Retrieve all markers
    public function index(Request $request)
    {    $latitude=isset($request->lat) ? $request->lat:'';

        $longitude=isset($request->long) ? $request->long:'';;
        if( $latitude!="" && $longitude!="" ){
            $radius = 1; // Radius in miles

            $markers = Marker::select('*')
            ->selectRaw("(
                3959 * acos(
                    cos(radians(?)) * cos(radians(lat)) * cos(radians(long) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )
            ) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->with(['user', 'hazards' => function($query) {
                $query->with('hazards'); // Eager load related hazard details
            }])
            ->get();
    }else{
        $groups = Group::where('organization_id', $request->organization_id)->pluck('id')->toArray();

        $markers = Marker::with(['user', 'hazards' => function($query) {
            $query->with('hazards'); // Eager load related hazard details
        }])->whereIn('group_id',$groups)->get();
    }
        return $this->sendResponse($markers,'Marker retrieved successfully');

    }

    /**
     * Fetch all members
     */
    public function deleted_index(Request $request)
    {
        $groups = Group::where('organization_id', $request->organization_id)->pluck('id')->toArray();
        $markers = Marker::onlyTrashed()->with('group','user')->whereIn('group_id',$groups)->get();
        return $this->sendResponse($markers, 'Markers retrieved successfully.');
    }
    public function restore($id)
    {
        $marker = Marker::onlyTrashed()->find($id);
        if (!$marker) {
            return $this->sendError('Marker not found', [], 404);
        }
        $marker->restore();
        return $this->sendResponse($marker, 'Marker restored successfully.');
    }
    /**
     * Delete a member
     */
    public function hard_destroy($id)
    {
        $marker = Marker::onlyTrashed()->find($id);
        if (!$marker) {
            return $this->sendError('Marker not found', [], 404);
        }
        $marker->forceDelete();
        return $this->sendResponse($marker, 'Marker deleted successfully.');
    }
    // Retrieve group all markers
    public function group_markers(Request $request)
    {
        $markers = Marker::with(['user','note','links','todo','comments' => function($query) {
            $query->with('user'); // Eager load related hazard details
        },'images' => function($query) {
            $query->with('user'); // Eager load related hazard details
        }, 'hazards' => function($query) {
            $query->with('hazards'); // Eager load related hazard details
        }])->where("group_id",$request->group_id)->get();
        return $this->sendResponse($markers,'Group Marker  retrieved successfully');
    }
    // Retrieve user all markers
    public function user_markers(Request $request)
    {
        $markers = Marker::with(['user','note','links','todo','comments' => function($query) {
            $query->with('user'); // Eager load related hazard details
        }, 'images' => function($query) {
            $query->with('user'); // Eager load related hazard details
        }, 'hazards' => function($query) {
            $query->with('hazards'); // Eager load related hazard details
        }])->where("user_id",$request->user_id)->get();
        return $this->sendResponse($markers,'User Marker retrieved successfully');
    }

    // Retrieve a specific marker
    public function show($id)
    {
        $marker = Marker::with(['user','note','links','todo','comments' => function($query) {
            $query->with('user'); // Eager load related hazard details
        }, 'images' => function($query) {
            $query->with('user'); // Eager load related hazard details
        },'hazards' => function($query) {
            $query->with('hazards'); // Eager load related hazard details
        }])->where('id',$id)->first();
        // $marker = Marker::create($request->all());


        if (is_null($marker)) {
            return $this->sendError('Marker not found.');
        }

        return $this->sendResponse($marker, 'Marker retrieved successfully.');

    }

    // Update a marker
    public function update(Request $request, Marker $marker)
    {
        $marker->update($request->all());
        return response()->json($marker, 200);
    }

    // Delete a marker
    public function destroy(Marker $marker)
    {
        $marker->delete();
        return $this->sendResponse("", 'Marker delete successfully.');
    }

    public function saveoption(Request $request)
    {
        $input = $request->all();
        if($input['option']==1)//hazards
        {
        $validator = Validator::make($input,[
            'marker_id' => 'required|integer',
            'user_id' => 'required|integer',
            'hazards_id' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);
      // Explode hazards_id string into an array
      $hazardIds = explode(',', $input['hazards_id']);

    // Create a new Hazard for each hazard_id
    foreach ($hazardIds as $hazardId) {
        $markerTodo = MarkerHazard::create([
            'user_id' =>$input['user_id'],
            'hazards_id' =>$hazardId,
            'marker_id'=> $input['marker_id']
        ]);
    }

        return $this->sendResponse($markerTodo,'Marker  hazards Saved');
    }
    if($input['option']==2)//comments
    {

        $validator = Validator::make($input,[
            'user_id' => 'required|exists:users,id',
            'marker_id' => 'required|integer',
            'comment' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $comment = MarkerComment::create([
            'user_id' =>$input['user_id'],
            'comment' => $input['comment'],
            'marker_id'=>$input['marker_id']
        ]);

        return $this->sendResponse($comment,'Marker  Comment Saved');

    }

    if($input['option']==3)//Todo
    {
        $validator = Validator::make($input,[
            'marker_id' => 'required|integer',
            'user_id' => 'required|integer',
            'name' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);
       $hazardIds = explode(',', $input['name']);
    //    $status = explode(',', $input['status']);

       // Create a new Hazard for each hazard_id
       foreach ($hazardIds as $key => $hazardId) {
        $markerTodo = MarkerTodo::create([
            'user_id' =>$input['user_id'],
            'name' => $hazardId,
            // 'status' => $status[$key],
            'marker_id'=> $input['marker_id']
        ]);
    }

        return $this->sendResponse($markerTodo,'Marker  Todo Saved');

    }

    if($input['option']==4)//Notes
    {
        $validator = Validator::make($input,[
            'marker_id' => 'required|integer',
            'user_id' => 'required|integer',
            'note' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);

        $MarkerNote = MarkerNote::create([
            'user_id' =>$input['user_id'],
            'note' => $input['note'],
            'marker_id'=> $input['marker_id']
        ]);

        return $this->sendResponse($MarkerNote,'Marker  Note Saved');
    }

    if($input['option']==5)//Links
    {

        $validator = Validator::make($input,[
            'marker_id' => 'required|integer',
            'user_id' => 'required|integer',
            'link' => 'required|string',
            'description' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);

        $markerTodo = MarkerLink::create([
            'user_id' =>$input['user_id'],
            'link' => $input['link'],
            'description' => $input['description'],
            'marker_id'=> $input['marker_id']
        ]);

        return $this->sendResponse($markerTodo,'Marker  Link Saved');
    }

    }
    public function updateoption(Request $request)
    {
        $input = $request->all();
        if($input['option']==1)//hazards
        {
        $validator = Validator::make($input,[
            'marker_id' => 'required|integer',
            'user_id' => 'required|integer',
            'hazards_id' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);
      // Explode hazards_id string into an array
      $hazardIds = explode(',', $input['hazards_id']);

    // Create a new Hazard for each hazard_id
    foreach ($hazardIds as $hazardId) {
        $markerTodo = MarkerHazard::create([
            'user_id' =>$input['user_id'],
            'hazards_id' =>$hazardId,
            'marker_id'=> $input['marker_id']
        ]);
    }

        return $this->sendResponse($markerTodo,'Marker  hazards Saved');
    }
    if($input['option']==2)//comments
    {

        $validator = Validator::make($input,[
            //'user_id' => 'required|exists:users,id',
            'id' => 'required|integer',
            'comment' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $markerId= $input['marker_id'];
        $marker = Marker::find($markerId);
        $comment = MarkerComment::where('id', $input['id'])
                   ->where('user_id',$input['user_id'])
                   ->where('marker_id',$markerId)
                  ->update([
                'comment' => $input['comment']
            ]);

        // $comment = $marker->comments()->create([
        //     'user_id' =>$input['user_id'],
        //     'comment' => $input['comment'],
        //     'marker_id'=>$markerId
        // ]);

        return $this->sendResponse($comment,'Marker  Comment Saved');

    }

    if($input['option']==3)//Todo
    {
        $validator = Validator::make($input,[
           // 'marker_id' => 'required|integer',
            'todo_id' => 'required|string',
            'status' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);
     //  $hazardIds = explode(',', $input['name']);

     $todo_ids = explode(',', $input['todo_id']);
     $status = explode(',', $input['status']);
       // Create a new Hazard for each hazard_id
       foreach ($todo_ids as  $key => $todo_id) {
        $markerTodo = MarkerTodo::where('id',$todo_id)
           ->update([
            'status'=> $status[$key]
        ]);
   }

        return $this->sendResponse($markerTodo,'Marker  Todo updated');

    }

    if($input['option']==4)//Notes
    {
        $validator = Validator::make($input,[
           // 'marker_id' => 'required|integer',
            'id' => 'required|integer',
            'note' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);

         $MarkerNote = MarkerNote::where('id',$input['id'])
         ->where('user_id',$input['user_id'])
         ->where('marker_id',$input['marker_id'])
            ->update([

            'note'=> $input['note']
        ]);

        return $this->sendResponse($MarkerNote,'Marker  Note Updated');
    }

    if($input['option']==5)//Links
    {

        $validator = Validator::make($input,[
            'updated_id' => 'required|integer',
           // 'user_id' => 'required|integer',
           // 'link' => 'required|string',
           // 'description' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
       // $marker = Marker::findOrFail($markerId);

        $markerTodo = MarkerLink::where('id',$input['updated_id'])->update([
            // 'user_id' =>$input['user_id'],
             'link' => $input['link'],
            'description' => $input['description'],
           // 'marker_id'=> $input['marker_id']
        ]);

        return $this->sendResponse($markerTodo,'Marker  Link Saved');
    }

    }
    public function getalloption(Request $request)
    {
        $input = $request->all();
       // $limit=$input['limit'];
       // $offset=$input['offset'];
        if($input['option']==1)//hazards
        {
            //add where with user_id if in request with isset parameter


            $markers = MarkerHazard::with('hazards','marker');
            if (isset($input['user_id'])) {
                $markers = $markers->where('user_id', $input['user_id']);
            }
            if (isset($input['marker_id'])) {
                $markers = $markers->where('marker_id', $input['marker_id']);
            }

            // $markers =$markers->limit($limit)
            // ->offset($offset)
            $markers =$markers->get();
            return $this->sendResponse($markers,'MarkerHazard retrieved successfully');

        }
        if($input['option']==2)//comments
        {
            $markers = MarkerComment::with('user','marker');
            if (isset($input['user_id'])) {
                $markers = $markers->where('user_id', $input['user_id']);
            }
            if (isset($input['marker_id'])) {
                $markers = $markers->where('marker_id', $input['marker_id']);
            }
            $markers =$markers->get();
            return $this->sendResponse($markers,'MarkerComment retrieved successfully');
        }
        if($input['option']==3)//Todo
        {
            $markers = MarkerTodo::with('user');
            if (isset($input['user_id'])) {
                $markers = $markers->where('user_id', $input['user_id']);
            }
            if (isset($input['marker_id'])) {
                $markers = $markers->where('marker_id', $input['marker_id']);
            }
            $markers =$markers->get();
            return $this->sendResponse($markers,'MarkerTodo retrieved successfully');
        }
        if($input['option']==4)//Notes
        {
            $markers = MarkerNote::with('user');
            if (isset($input['user_id'])) {
                $markers = $markers->where('user_id', $input['user_id']);
            }
            if (isset($input['marker_id'])) {
                $markers = $markers->where('marker_id', $input['marker_id']);
            }
            $markers =$markers->get();
            return $this->sendResponse($markers,'MarkerNote retrieved successfully');
        }
        if($input['option']==5)//Links
        {
            $markers = MarkerLink::with('user');
            if (isset($input['user_id'])) {
                $markers = $markers->where('user_id', $input['user_id']);
            }
            if (isset($input['marker_id'])) {
                $markers = $markers->where('marker_id', $input['marker_id']);
            }
            $markers =$markers->get();
            return $this->sendResponse($markers,'MarkerLink retrieved successfully');
        }
    }
    public function importMarker(Request $request)
	{
        $validator = Validator::make(request()->only('file', 'organization_id'), [
            'file' => 'required|mimes:csv,txt,xls,xlsx',
            'organization_id' => 'required|exists:organizations,id',
        ]);
		if ($validator->fails())
		{
			return $this->sendError('Validation Error.', $validator->errors());
		}
        $fileMimeType = $request->file('file')->getMimeType();
		try
		{
            $organizationId = request()->input('organization_id');
            // Excel::queueImport(new MarkersImport($organizationId), request()->file('file'));
            Excel::import(new MarkersImport($organizationId), request()->file('file'));
            $tempmarkers = TempData::where('organization_id',$organizationId)->get();
		} catch (ValidationException $e)
		{
			$failures = $e->failures();
            return $this->sendError('Validation Error.', $failures);
		}
        return $this->sendResponse($tempmarkers,'Temp Marker retrieved successfully');
	}
    public function importMarkerShow(Request $request)
	{
        $validator = Validator::make(request()->only('organization_id'), [
            'organization_id' => 'required|exists:organizations,id',
        ]);
		if ($validator->fails())
		{
			return $this->sendError('Validation Error.', $validator->errors());
		}
        $organizationId = request()->input('organization_id');
        $tempmarkers = TempData::where('organization_id',$organizationId)->get();
        return $this->sendResponse($tempmarkers,'Temp Marker retrieved successfully');
	}
    public function tempDataUpdate(Request $request)
	{
        $validator = Validator::make(request()->only('status', 'user_id', 'organization_id'), [
            'status' => 'required',
            'organization_id' => 'required|exists:organizations,id',
            'user_id' => 'required|exists:users,id',
        ]);
		if ($validator->fails())
		{
			return $this->sendError('Validation Error.', $validator->errors());
		}
        $organizationId = request()->input('organization_id');
        $userId = request()->input('user_id');
        $status = request()->input('status');
        $tempmarkers = TempData::where('organization_id',$organizationId);
        if ($status == 1) {
            $tempmarkers = $tempmarkers->get();
            foreach ($tempmarkers as $tempmarker) {
                if ($tempmarker->status ==0) {
                    $tempmarker->delete();
                    continue;
                }
                if ($tempmarker->group_id_1 ==0) {
                    $groups = Group::where('organization_id', $this->organizationId)
                    ->select('id')->get();
                    foreach ($groups as $group) {
                        $markerData = [
                            'user_id' => $userId,
                            'group_id' => $group->id,
                            'internal_id' => $tempmarker->internal_id,
                            'proximity' => $tempmarker->proximity,
                            'description' => $tempmarker->description,
                            'proximity' => $tempmarker->proximity,
                            'color' => $tempmarker->color,
                            'long' => $tempmarker->longitude,
                            'lat' => $tempmarker->latitude,
                            'marker_title' => $tempmarker->marker_title,
                            'status' => $tempmarker->status,
                        ];
                        $marker = Marker::create($markerData);

                        // Create MarkerHazard (assuming it's related to markers)
                        MarkerHazard::create([
                            'marker_id' => $marker->id,
                            'hazards_id' => $tempmarker->hazard_id,
                            'user_id' => $userId,
                        ]);
                    }
                }else {
                    $markerData = [
                        'user_id' => $userId,
                        'group_id' => $tempmarker->group_id_1,
                        'internal_id' => $tempmarker->internal_id,
                        'proximity' => $tempmarker->proximity,
                        'description' => $tempmarker->description,
                        'color' => $tempmarker->color,
                        'long' => $tempmarker->longitude,
                        'lat' => $tempmarker->latitude,
                        'marker_title' => $tempmarker->marker_title,
                        'status' => $tempmarker->status,
                    ];
                    $marker = Marker::create($markerData);
                    // Create MarkerHazard (assuming it's related to markers)
                    MarkerHazard::create([
                            'marker_id' => $marker->id,
                            'hazards_id' => $tempmarker->hazard_id,
                            'user_id' => $userId,
                    ]);
                    if ($tempmarker->group_id_2 !=null) {
                        $markerData = [
                            'user_id' => $userId,
                            'group_id' => $tempmarker->group_id_2,
                            'internal_id' => $tempmarker->internal_id,
                            'proximity' => $tempmarker->proximity,
                            'description' => $tempmarker->description,
                            'color' => $tempmarker->color,
                            'long' => $tempmarker->longitude,
                            'lat' => $tempmarker->latitude,
                            'marker_title' => $tempmarker->marker_title,
                            'status' => $tempmarker->status,
                        ];
                        $marker = Marker::create($markerData);
                        // Create MarkerHazard (assuming it's related to markers)
                    MarkerHazard::create([
                        'marker_id' => $marker->id,
                        'hazards_id' => $tempmarker->hazard_id,
                        'user_id' => $userId,
                    ]);
                    }
                    if ($tempmarker->group_id_3 !=null) {
                        $markerData = [
                            'user_id' => $userId,
                            'group_id' => $tempmarker->group_id_3,
                            'internal_id' => $tempmarker->internal_id,
                            'proximity' => $tempmarker->proximity,
                            'description' => $tempmarker->description,
                            'color' => $tempmarker->color,
                            'long' => $tempmarker->longitude,
                            'lat' => $tempmarker->latitude,
                            'marker_title' => $tempmarker->marker_title,
                            'status' => $tempmarker->status,
                        ];
                        $marker = Marker::create($markerData);
                        // Create MarkerHazard (assuming it's related to markers)
                    MarkerHazard::create([
                        'marker_id' => $marker->id,
                        'hazards_id' => $tempmarker->hazard_id,
                        'user_id' => $userId,
                    ]);
                    }
                }
                    // Delete the temp marker
                    $tempmarker->delete();
            }
            return $this->sendResponse($tempmarkers,'Markers have been imported successfully');
        }
        $tempmarkers = $tempmarkers->delete();
        return $this->sendResponse($tempmarkers,'Temp Marker deleted successfully');
	}


    public function upload_marker_imgs(Request $request, Helper $helper)
	{

        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '60000');
        ini_set('max_input_time', '600');


        $validator = Validator::make(request()->only('img'), [
            // 'img.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for image upload
            'img.*' => 'required', // Validation for image upload
        ]);
		if ($validator->fails())
		{
			return $this->sendError('Validation Error.', $validator->errors());
		}
// dd($request->all());
        // if ($request->hasFile('img')) {
            foreach ($request->file('img') as $key => $image) {
                $uploadedImages = '';
                $img_name = $image->getClientOriginalName();
                // dd($image);
                $uploadedImages = $helper->uploader($request, "img", 'uploaded/markers/img', $key);
                MarkerImg::create([
                    'marker_id' => $request->marker_id,
                    'img' => $uploadedImages,
                    'status' => $img_name,
                    'user_id' => $request->user_id,
                ]);
            }
        // }
        return $this->sendResponse([],'Marker images Uploaded successfully');
	}
    public function marker_imgs($id)
	{
        $marker_img = MarkerImg::where('marker_id',$id)->get();
        return $this->sendResponse($marker_img,'Marker Images retrieved successfully');
	}
    public function delete_marker_img($id)
{
    $markerImg = MarkerImg::find($id);

    if (!$markerImg) {
        return $this->sendError('Image not found.');
    }

    // Delete the image file from storage
    if ($markerImg->img) {
        $imagePath = public_path($markerImg->img); // Assuming `img` field stores file path
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    // Delete the database record
    $markerImg->delete();
    return $this->sendResponse([], 'Deleted successfully');
}
}
