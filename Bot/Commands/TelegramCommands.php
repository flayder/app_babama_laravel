<?php

namespace App\Bot\Commands;

use Bot\Commands\BotCommands;

class TelegramCommands extends BotCommands
{
    protected function getRouteConfigPath(): string
    {
        return __DIR__ . '/../router.php';
    }

    public function startCommand($userId, $params)
    {
        $message[] = "Добро пожаловать в техподдержку Babama.ru. \n";
        $message[] = "Напишите свой вопрос или опишите проблему, и мы обязательно во всём разберёмся. Заранее извиняемся, если что-то пошло не так. \n";
        $message[] = "И да, чтобы получить полный ответ:";
        $message[] = "- Опишите максимально подробно свою ситуацию;";
        $message[] = "- Укажите е-мейл (логин) на который делался заказ.";


        return $this->createAnswer(implode("\n", $message));
    }

}
