<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Message\Command\CheckAwardsArePublished;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\JitterTrigger;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
readonly class CheckAwardsArePublishedProvider implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private LockFactory $lockFactory,
    ) {
    }

    #[\Override]
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::trigger(
                new JitterTrigger(CronExpressionTrigger::fromSpec('7 * * * *')), new CheckAwardsArePublished()
            ))
            ->stateful($this->cache)
            ->lock($this->lockFactory->createLock('scheduler-default'))
        ;
    }
}
