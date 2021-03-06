<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\ThirdParties\Sms\AdsmediaConnection;
use Carbon\Carbon;
use DB;

class Sms
{
    public function send($notifiable, Notification $notification)
    {
        $messageType = isset($notification->type) ? $notification->type : 'reguler';
        $message = $notification->toSms($notifiable);
        $connection = new AdsmediaConnection($messageType);
        $connection->setMessage($message->getTargetPhone(), $message->getMessage());
        $result = strtoupper(env('APP_ENV')) !== 'STANDALONE_TESTING' ? $connection->send() : ['result' => 'hold due to testing environment'];

        $persistedNotification = DB::table('notifications')->where('id', $notification->id);
        if ($persistedNotification->first()) {
            $persistedNotification->update([
                'sms_notification_result' => json_encode($result),
                'sms_notification_sent_at' => Carbon::now(),
            ]);
        }
    }
}