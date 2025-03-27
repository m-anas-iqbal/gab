<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMail;
use App\Mail\SaleContactMail;
use App\Models\ContactSales;

class TicketController extends BaseController
{
    /**
     * Fetch all groups
     */

    public function storedata(Request $request, Helper $helper)
    {
        // Validate the request data
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|required|exists:users,id',
            'subject' => 'required|string',
            'reason' => 'required|string',
            'priority' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,txt,mp4,avi,mkv',
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        // Initialize an empty data array
        $data = $validatedData->validated();
        if ($request->hasFile('file')) {
            $data['file'] = $helper->uploader($request, 'file', 'uploaded/ticket/attachment');
        }

        // Create a new group using the validated data and file paths
        $ticket = Ticket::create($data);
        Mail::to('couierin1@gmail.com')->send(new TicketMail($ticket->subject,$ticket->priority,$ticket->reason,$ticket->file,$ticket->user));

        return $this->sendResponse(null, 'Your request has been submited successfully.', 200); // HTTP 201 for Created
    }
    public function storesalecontact(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'nullable|string',
        ]);

        if ($validatedData->fails()) {
            return $this->sendError('Validation Error', $validatedData->errors(), 422);
        }

        ContactSales::create($validatedData->validated());
        $subject = $request->name." want to buy custom plan";
        Mail::to('couierin1@gmail.com')->send(new SaleContactMail($subject,$request->name,$request->email,$request->message));

        return $this->sendResponse(null, 'User data has been saved successfully.', 200);
    }
}
