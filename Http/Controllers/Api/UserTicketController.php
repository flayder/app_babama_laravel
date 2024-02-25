<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\SupportController;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTicketController extends SupportController
{
    public function index(): TicketResource
    {
        $tickets = Ticket::with('messages')->where('user_id', auth()->id())->get();

        return TicketResource::make($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $this->newTicketValidation($request);
        $random = random_int(100000, 999999);
        $ticket = $this->saveTicket($request, $random);

        $message = $this->saveMsgTicket($request, $ticket);

        $path = config('location.ticket.path');
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                try {
                    $this->saveAttachment($message, $image, $path);
                } catch (Exception) {
                    return response()->json(['error' => 'Could not upload your ' . $image]);
                }
            }
        }

        $msg = [
            'username' => optional($ticket->user)->username,
            'ticket_id' => $ticket->ticket,
        ];
        $action = [
            'link' => route('admin.ticket.view', $ticket->id),
            'icon' => 'fas fa-ticket-alt text-white',
        ];

        $this->adminPushNotification('SUPPORT_TICKET_CREATE', $msg, $action);
        return response()->json([
            'message' => 'Мы получили заявку!'
        ]);
    }
}
