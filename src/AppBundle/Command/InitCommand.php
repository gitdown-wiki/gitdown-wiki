<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gitdown-wiki:init')
            ->setDescription('Initializes the gitdown-wiki')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminRepositoryName = $this->getContainer()->getParameter('app.admin_repository');
        $repositoryRoot = $this->getContainer()->getParameter('app.wiki.root');
        
        $fs = new Filesystem();
        
        if (!$fs->exists($repositoryRoot)) {
            $fs->mkdir($repositoryRoot, 0777);
        }
        
        try {
            $this->getContainer()->get('app.repository')->createRepository($adminRepositoryName);
            $fs->chmod($repositoryRoot.'/'.$adminRepositoryName, 0777, 000, true);
            
            $output->writeln('Initialized gitdown-wiki.');
        } catch(\Exception $exception) {
            $output->writeln('gitdown-wiki is already initialized.');
        }
    }
}