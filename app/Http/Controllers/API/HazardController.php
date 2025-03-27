<?php
namespace App\Http\Controllers;
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;
use App\Models\Hazard;
use App\Models\MarkerHazard;

class HazardController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $hazards = Hazard::get();
        $language = $request->header('Accept-Language');

 // Initialize an empty array to hold the processed hazards
        $hazardsArray = [];

        // Loop through each hazard and process it
        foreach ($hazards as $hazard) {
            $commit_message = $language === 'es' ? $hazard->name_sp : $hazard->name;
            $hazardsArray[] = [
                'id' => $hazard->id,
                'name' => $commit_message,
                'icon' => $hazard->icon,
                'created_at' => $hazard->created_at,
                'updated_at' => $hazard->updated_at,
            ];
        }

        return $this->sendResponse($hazardsArray, 'Hazard retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input,[
            'name' => 'required|string',
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->hasFile('icon')) {
            $image = $request->file('icon');
            $imageName = time().'.'.$image->extension();
            $image->move(public_path('images'), $imageName);
            $input['icon'] = url('images/' . $imageName); // Save the path to the image
        }

        $hazard = Hazard::create($input);

        return $this->sendResponse($hazard,'Hazard Saved');
    }
    public function saveHazard(Request $request)
    {
        $input = $request->all();

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
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      //  dd($id);
       // Validate the request data
       $request->validate([
        //'name' => 'required|string',
        'name_sp' => 'required|string',
        //'icon' => 'nullable|string',
    ]);
//dd( $request->name_sp);
    // Find the hazard by ID
    $hazard = Hazard::findOrFail($id);

    // Update the hazard with new data
     $hazard->update([
     //   'name' => $request->name,
        'name_sp' => $request->name_sp,
    //    'icon' => $request->icon,
     ]);
     return $this->sendResponse($hazard, 'Hazard Update successfully.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
