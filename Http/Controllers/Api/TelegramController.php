<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Bot\Commands\TelegramCommands;
use App\Dto\TicketSubjectDto;
use App\Http\Controllers\Controller;
use App\Http\Controllers\User\SupportController;
use App\Models\Promocode;
use App\Models\Ticket;
use App\Repositories\UserRepository;
use App\Services\PromocodeService;
use Bot\Commands\BotRouter;
use Bot\Service\TelegramBot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends SupportController
{
    public TelegramBot $telegramBot;

    public function __construct(TelegramBot $telegramBot)
    {
        $this->telegramBot = $telegramBot;
    }

    public function test()
    {
        $tgBot = $this->telegramBot;
        $tgBot->setUserId(389860001);
        $tgBot->setMessage('Привет');
        $tgBot->send();
    }

    public function webhook(Request $request)
    {
        Log::info('[BotRequest]', [
            $request->all()
        ]);
        Log::info('[BotRequest]', [
            'file' => $request->files
        ]);

        $bot = $this->telegramBot;
//        if (!$bot->isAnswer()){
//            $bot->setUserId(TG_TEST_USER_ID);
//            $bot->setMessage('регистрация');
//        }
        $command = new BotRouter(new TelegramCommands());
        $result = $command->call($bot->getMessage(), $bot->getUserId(), $bot);

        if (!empty($result['answer']) && !empty($result['answer']['message'])) {
            $bot->setMessage($result['answer']['message']);

            return response()->json($bot->send());
        }

        $noMessageText = 'Вы можете отправить только текстовое сообщение!';
        $message = "Ваш запрос взят в работу. Срок рассмотрения обычно занимает до часа. Но, из-за нагрузки, может растянуться до двух дней (надеемся, что это не Ваш случай).";

        if (empty($bot->getMessage())) {
            $bot->setMessage($noMessageText);
            return response()->json($bot->send());
        } elseif (!empty($bot->getFiles())) {
            $bot2 = clone $bot;
            $bot2->setMessage($noMessageText);
            $bot2->send();
        }

        $request = Request::create('/', 'POST', [
            'name' => $bot->getUserName(),
            'email' => $bot->getUserId(),
            'subject' => TicketSubjectDto::$TELEGRAM,
            'message' => $bot->getMessage()
        ]);

        $this->store($request);


//        $files = $bot->getFiles();
//        Log::info('[TgFiles]', [
//            $files
//        ]);
        if (empty($result['answer'])) {
            $bot->setMessage($message);
        }


        return response()->json($bot->send());
    }

    public function store(Request $request)
    {
        $random = random_int(100000, 999999);
        $ticket = Ticket::where('email', $request->email)->first();
        if (!empty($ticket)) {
            $random = $ticket->ticket;
            $ticket->last_reply = Carbon::now();
            $ticket->status = 0;
            $ticket->save();
        } else {
            $ticket = $this->saveTicket($request, $random);
        }


        $message = $this->saveMsgTicket($request, $ticket);

        $path = config('location.ticket.path');
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                try {
                    $this->saveAttachment($message, $image, $path);
                } catch (\Exception $exp) {
                    return back()->withInput()->with('error', 'Could not upload your '.$image);
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

        return redirect()->route('user.ticket.list')->with('success', 'Your Ticket has been pending');
    }

    /** @param $random */
    public function saveTicket(Request $request, $random): Ticket
    {

        $ticket = new Ticket();
        $ticket->user_id = null;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->status = 0;
        $ticket->last_reply = Carbon::now();
        $ticket->save();

        return $ticket;
    }


}
