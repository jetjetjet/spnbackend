<?php

namespace App\Observers;

use App\Http\Repositories\NotificationRepository;
use App\Notifications\NotifCount;
use App\User;

class NotifObserver
{
  public function created()
  {
    $users = User::all();
    foreach ($users as $user) {
      $notif = NotificationRepository::countNotif($user->id);
      $user->notify(new NotifCount($notif));
    }
  }
}