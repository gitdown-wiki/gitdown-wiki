<?php

namespace AppBundle\Command;

use AppBundle\Security\User\GitUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

class AddUserCommand extends ContainerAwareCommand
{
    const MAX_ATTEMPTS = 5;
    
    protected function configure()
    {
        $this
            ->setName('gitdown-wiki:add-user')
            ->setDescription('Adds a new user for the wiki.')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'What is the username of the new user?'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'What is the users password?'
            )
            ->addArgument(
                'email',
                InputArgument::OPTIONAL,
                'What is the users e-mail adress?'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'What is the users name?'
            )
            ->addOption(
                'roles',
                'r',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Set the roles of the new user. Admin by default.',
                array('ROLE_ADMIN')
            )
        ;
    }
    
    public function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('username') && null !== $input->getArgument('password') && null !== $input->getArgument('email') && null !== $input->getArgument('name')) {
            return;
        }
        
        $output->writeln('');
        $output->writeln('Add User Command Interactive Wizard');
        $output->writeln('-----------------------------------');
        
        $output->writeln(array(
            '',
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php app/console gitdown-wiki:add-user username password email@example.com',
            '',
        ));
        
        $output->writeln(array(
            '',
            'Please insert the missing data',
            ''
        ));
        
        $console = $this->getHelper('question');
        
        if (null === $username = $input->getArgument('username')) {
            $question = new Question(' > <info>Username</>: ');
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('The username cannot be empty');
                }

                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $username = $console->ask($input, $output, $question);
            $input->setArgument('username', $username);
        } else {
            $output->writeln(' > <info>Username</>: '.$username);
        }
        
        if (null === $password = $input->getArgument('password')) {
            $question = new Question(' > <info>Password</> (your type will be hidden): ');
            $question->setValidator(array($this, 'passwordValidator'));
            $question->setHidden(true);
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $password = $console->ask($input, $output, $question);
            $input->setArgument('password', $password);
        } else {
            $output->writeln(' > <info>Password</>: '.str_repeat('*', strlen($password)));
        }

        // Ask for the email if it's not defined
        if (null === $email = $input->getArgument('email')) {
            $question = new Question(' > <info>Email</>: ');
            $question->setValidator(array($this, 'emailValidator'));
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $email = $console->ask($input, $output, $question);
            $input->setArgument('email', $email);
        } else {
            $output->writeln(' > <info>Email</>: '.$email);
        }
        
        if (null === $name = $input->getArgument('name')) {
            $question = new Question(' > <info>Name</>: ');

            $name = $console->ask($input, $output, $question);
            $input->setArgument('name', $name);
        } else {
            $output->writeln(' > <info>Name</>: '.$name);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = $input->getOption('roles');
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        
        $adminRepositoryName = $this->getContainer()->getParameter('app.admin_repository');
        $adminRepository = $this->getContainer()->get('app.repository')->getRepository($adminRepositoryName);
        
        $user = new GitUser($username, $password, '', $roles, $email, $name);
        
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user, $password);
        
        $userArray = array(
            'roles' => $user->getRoles(),
            'password' => $encodedPassword,
            'salt' => $user->getSalt(),
            'email' => $user->getEmail(),
            'name' => $user->getName()
        );
        
        $yamlDumper = new Dumper();
        $yaml = $yamlDumper->dump($userArray, 2);
        
        $fs = new Filesystem();
        $fs->dumpFile($adminRepository->getPath().'/users/'.$username.'.yml', $yaml);
        $fs->chmod($adminRepository->getPath().'/users/'.$username.'.yml', 0777);
        
        $message = sprintf('Added user %s.', $username);
        
        $adminRepository->run('add', array('-A'));
        $adminRepository->run('commit', array('-m ' . $message, '--author="Gitdown wiki <wiki@example.com>"'));
        
        $output->writeln(sprintf('Created user %s.', $username));
    }
    
    public function passwordValidator($plainPassword)
    {
        if (empty($plainPassword)) {
            throw new \Exception('The password can not be empty');
        }

        if (strlen(trim($plainPassword)) < 6) {
            throw new \Exception('The password must be at least 6 characters long');
        }

        return $plainPassword;
    }

    public function emailValidator($email)
    {
        if (empty($email)) {
            throw new \Exception('The email can not be empty');
        }

        if (false === strpos($email, '@')) {
            throw new \Exception('The email should look like a real email');
        }

        return $email;
    }
}