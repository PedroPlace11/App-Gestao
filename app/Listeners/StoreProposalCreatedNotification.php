<?php

namespace App\Listeners;

use App\Events\ProposalCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreProposalCreatedNotification implements ShouldQueue
{
    public function handle(ProposalCreated $event): void
    {
        $proposal = $event->proposal->load('entity', 'user');

        if ($proposal->user) {
            $proposal->user->notify(
                new \App\Notifications\ProposalCreatedNotification($proposal)
            );
        }
    }
}
