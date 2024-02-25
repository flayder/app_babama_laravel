<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Notify;
use App\Http\Traits\Upload;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TopQuestion;
use App\Services\BasicService;
use Bot\Service\TelegramBot;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Purify\Facades\Purify;
use App\Dto\TicketSubjectDto;
use function count;
use function in_array;
use const PATHINFO_EXTENSION;

class TicketController extends Controller
{
    use Notify;
    use Upload;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            return $next($request);
        });
    }

    public function topTickets()
    {
        $topQuestions = TopQuestion::all();
        return view('admin.pages.ticket.top-tickets', compact('topQuestions'));
    }

    public function storeTopTicket(Request $request): Redirector|Application|RedirectResponse
    {
        TopQuestion::create($request->all());

        return redirect(route('admin.top-ticket'))->with('success', 'stored successfully');
    }

    public function editTopTickets($id)
    {
        $question = TopQuestion::find($id);

        return view('admin.pages.top-question.edit', compact('question'));
    }

    public function updateTopTickets(int $id, Request $request)
    {
        TopQuestion::find($id)->update($request->all());

        return redirect(route('admin.top-ticket'))->with('success', 'stored successfully');
    }

    public function createTopTicket(): Factory|View|Application
    {
        return view('admin.pages.top-question.create');
    }

    public function tickets(string $type = null)
    {
        $title = 'Tickets List';

        if (isset($type) && 1 == BasicService::validateKeyword($type, 'open')) {
            $title = 'Open Tickets';
            $type = 0;
        } elseif (isset($type) && 1 == BasicService::validateKeyword($type, 'answered')) {
            $title = 'Answered Tickets';
            $type = 1;
        } elseif (isset($type) && 1 == BasicService::validateKeyword($type, 'replied')) {
            $title = 'Replied Tickets';
            $type = 2;
        } elseif (isset($type) && 1 == BasicService::validateKeyword($type, 'closed')) {
            $title = 'Closed Tickets';
            $type = 3;
        }

        $tickets = Ticket::with('user')
            ->when(isset($type), fn($query) => $query->where('status', $type))
            ->latest()
            ->paginate(config('basic.paginate'));
        $empty_message = 'No Data found.';

        return view('admin.pages.ticket.index', compact('tickets', 'title', 'empty_message'));
    }

    public function ticketSearch(Request $request)
    {
        $search = $request->all();
        $dateSearch = $request->date_time;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);
        $tickets = Ticket::when(isset($search['ticket']), fn($query) => $query->where('ticket', 'LIKE', "%{$search['ticket']}%"))
            ->when(isset($search['email']), fn($query) => $query->where('email', 'LIKE', "%{$search['email']}%"))
            ->when(isset($search['status']), function ($query) use ($search) {
                if ('-1' == $search['status']) {
                    return $query->where('status', '!=', $search['status']);
                }

                return $query->where('status', $search['status']);
            })
            ->when(1 == $date, fn($query) => $query->whereDate('created_at', $dateSearch))
            ->with('user')->paginate(config('basic.paginate'));
        $tickets->appends($search);

        $title = 'Search Tickets';

        $empty_message = 'No Data found.';

        return view('admin.pages.ticket.index', compact('tickets', 'title', 'empty_message'));
    }

    public function ticketReply($id)
    {
        $ticket = Ticket::where('id', $id)->with('user', 'messages')->firstOrFail();
        $title = 'Ticket #' . $ticket->ticket;

        $subject = match ($ticket->subject) {
            TicketSubjectDto::$EMAIL => 'email',
            TicketSubjectDto::$TELEGRAM => 'telegram'
        };

        return view('admin.pages.ticket.show', compact('ticket', 'title', 'subject'));
    }

    public function ticketReplySend(Request $request, $id)
    {
        $ticket = Ticket::where('id', $id)->with('user')->firstOrFail();
        $message = new TicketMessage();

        if (1 == $request->replyTicket) {
            $req = $request->except('_token', '_method');

            $imgs = $request->file('attachments');
            $allowedExts = ['jpg', 'png', 'jpeg', 'pdf'];

            $request->validate([
                'attachments' => [
                    'max:4096',
                    function ($attribute, $value, $fail) use ($imgs, $allowedExts) {
                        foreach ($imgs as $img) {
                            $ext = strtolower($img->getClientOriginalExtension());
                            if (($img->getSize() / 1000000) > 2) {
                                return $fail('Images MAX  2MB ALLOW!');
                            }

                            if (!in_array($ext, $allowedExts)) {
                                return $fail('Only png, jpg, jpeg, pdf images are allowed');
                            }
                        }
                        if (count($imgs) > 5) {
                            return $fail('Maximum 5 images can be uploaded');
                        }
                    },
                ],
                'message' => 'required',
            ]);

            $ticket->status = 1;
            $ticket->last_reply = Carbon::now();
            $ticket->save();

            $message->ticket_id = $ticket->id;
            $message->admin_id = $this->user->id;
            $message->message = $req['message'];
            $message->save();

            $path = config('location.ticket.path');
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $image) {
                    try {
                        TicketAttachment::create([
                            'ticket_message_id' => $message->id,
                            'image' => $this->uploadImage($image, $path),
                        ]);
                    } catch (Exception $exp) {
                        return back()->with('error', 'Could not upload your ' . $image)->withInput();
                    }
                }
            }

            $msg = [
                'ticket_id' => $ticket->ticket,
            ];
            $action = [
                'link' => route('user.ticket.view', $ticket->ticket),
                'icon' => 'fas fa-ticket-alt text-white',
            ];

            if (!empty($ticket->user)) {
                $this->userPushNotification($ticket->user, 'ADMIN_REPLIED_TICKET', $msg, $action);
                $this->sendMailSms($ticket->user, 'ADMIN_SUPPORT_REPLY', [
                    'ticket_id' => $ticket->ticket,
                    'ticket_subject' => $ticket->subject,
                    'reply' => $request->message,
                ]);
            }
            if ($ticket->subject == TicketSubjectDto::$TELEGRAM) {
                /**
                 * @var TelegramBot $bot
                 */
                $bot = app(TelegramBot::class);
                $bot->setMessage("Ответ от службы поддержки:\n \n" . $req['message']);
                $bot->setUserId($ticket->email);
                $bot->send();
            }


            return back()->with('success', 'Ticket has been replied');
        } elseif (2 == $request->replyTicket) {
            $ticket->status = 3;
            $ticket->save();

            return back()->with('success', 'Ticket has been closed');
        }
    }

    public function ticketDownload($ticket_id)
    {
        $attachment = TicketAttachment::with('supportMessage', 'supportMessage.ticket')->findOrFail(decrypt($ticket_id));
        $file = $attachment->image;
        $path = config('location.ticket.path');
        $full_path = $path . '/' . $file;

        if (!file_exists($full_path)) {
            abort(404);
        }

        $title = slug($attachment->supportMessage->ticket->subject) . '-' . $file;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimetype = mime_content_type($full_path);
        header('Content-Disposition: attachment; filename="' . $title);
        header('Content-Type: ' . $mimetype);

        return readfile($full_path);
    }

    public function ticketDelete(Request $request)
    {
        $message = TicketMessage::findOrFail($request->message_id);
        $path = config('location.ticket.path');
        if (count($message->attachments) > 0) {
            foreach ($message->attachments as $img) {
                @unlink($path . '/' . $img->image);
                $img->delete();
            }
        }
        $message->delete();

        return back()->with('success', 'Message has been deleted');
    }
}
