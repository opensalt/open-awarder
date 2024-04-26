<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enums\AwardState;
use App\Message\OfferAward;
use App\Message\SendOfferedEmail;
use App\Repository\AwardRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
readonly final class OfferAwardHandler
{
    public function __construct(
        private AwardRepository $awardRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(OfferAward $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if ($award?->getState() !== AwardState::OcpProcessed) {
            return;
        }

        // Update workflow status
        $this->awardRepository->updateWorkflowStatus($message->awardId, AwardState::Published);

        // Send email to recipient about offer
        $this->bus->dispatch(
            (new Envelope(new SendOfferedEmail($message->awardId)))
                ->with(new DispatchAfterCurrentBusStamp())
        );
    }
}
