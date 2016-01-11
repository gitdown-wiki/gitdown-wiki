<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

class DeleteUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gitdown-wiki:delete-user')
            ->setDescription('Deletes a user from the wiki.')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'What is the username of the new user?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        
        $adminRepositoryName = $this->getContainer()->getParameter('app.admin_repository');
        $adminRepository = $this->getContainer()->get('app.repository')->getRepository($adminRepositoryName);
        
        $message = sprintf('Delete user %s.', $username);
        
        $adminRepository->run('rm', array('users/'.$username.'.yml'));
        $adminRepository->run('commit', array('-m ' . $message, '--author="Gitdown wiki <wiki@example.com>"'));
        
        $output->writeln(sprintf('Deleted user %s.', $username));
    }
}