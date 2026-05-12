<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProposalCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Proposal $proposal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'proposal_created',
            'title'       => "Nova proposta: {$this->proposal->number}",
            'message'     => "A proposta {$this->proposal->number} para o cliente {$this->proposal->entity?->name} foi criada.",
            'proposal_id' => $this->proposal->id,
            'proposal_number' => $this->proposal->number,
            'total_value' => $this->proposal->total_value,
        ];
    }
}
