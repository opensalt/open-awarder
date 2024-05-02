<?php

declare(strict_types=1);

namespace App\MessageHandler\Event;

use App\Enums\AwardState;
use App\Message\Command\SendOfferedEmail;
use App\Message\Event\AwardWasPublishedEvent;
use App\Repository\AwardRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
readonly final class SendOfferedEmailWhenPublished
{
    public function __construct(
        private AwardRepository $awardRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(AwardWasPublishedEvent $message): void
    {
        $award = $this->awardRepository->find($message->awardId);
        if ($award?->getState() !== AwardState::Published) {
            return;
        }

        // Send email to recipient about offer
        $this->bus->dispatch(new SendOfferedEmail($message->awardId), [
            new DispatchAfterCurrentBusStamp(),
        ]);
    }
}
