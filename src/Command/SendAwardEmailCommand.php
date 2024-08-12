<?php

declare(strict_types=1);

namespace App\Command;

use App\Enums\AwardState;
use App\Message\Command\SendOfferedEmail;
use App\Repository\AwardRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:send-award-email',
    description: 'Re-send an award email',
)]
class SendAwardEmailCommand extends Command
{
    public function __construct(
        private readonly AwardRepository $awardRepository,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('awardId', InputArgument::REQUIRED, 'Award Id')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $awardId = Uuid::fromString($input->getArgument('awardId'));

        $award = $this->awardRepository->find($awardId);
        if (AwardState::Offered !== $award->getState()) {
            $io->error('Award is not published');

            return Command::FAILURE;
        }

        $this->awardRepository->updateWorkflowStatus($awardId, AwardState::Published);

        // Send email to recipient about offer
        $this->bus->dispatch(new SendOfferedEmail($awardId), [
            new DispatchAfterCurrentBusStamp(),
        ]);

        $io->success('Email sent');

        return Command::SUCCESS;
    }
}
