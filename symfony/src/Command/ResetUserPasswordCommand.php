<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetUserPasswordCommand extends Command
{
    protected static $defaultName = 'app:user:reset-password';

    const DEFAULT_PASSWORD = 'angleplanner';

    /** @var ManagerRegistry $doctrine */
    private $doctrine;
    /** @var EntityManagerInterface $em */
    private $em;
    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email to reset its password.')
            ->setDescription('Reset a user\'s password, set it to: angleplanner')
            ->setHelp('This command allows you to force-reset a user\'s password in case they have forgotten it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->doctrine->getRepository(User::class);

        $email = $input->getArgument('email');

        // Look up the user
        $user = $userRepository->findOneByEmail($email);

        if (!$user) {
            $output->writeln(sprintf('<error>User email not found.</error>'));
            return 1;
        }

        // User was found, reset its password
        $newEncodedPassword = $this->passwordEncoder->encodePassword(
            $user,
            self::DEFAULT_PASSWORD
        );

        $user->setPassword($newEncodedPassword);


        try {
            $this->em->flush();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error updating the user record.</error>'));
            return 1;
        }


        $output->writeln('<info>OK</info> Password was successfully reset!');
    }

}