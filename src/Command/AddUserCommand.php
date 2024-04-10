<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-admin-user',
    description: 'Add a new user',
)]
class AddUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username for user')
            ->addOption('password', null, InputOption::VALUE_NONE, 'Password for user')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $username = $input->getArgument('username');

        if (!$username) {
            $username = $helper->ask($input, $output, new Question('Username: '));
        }

        $password = $input->getOption('password');
        if (!$password) {
            $password = $helper->ask($input, $output, (new Question('Password: '))->setHidden(true));
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('The user has been added!');

        return Command::SUCCESS;
    }
}
