<?php

namespace Swarrot\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Swarrot\Consumer;
use Symfony\Component\Console\Command\Command;
use Swarrot\AMQP\MessageProviderInterface;

class MailCommand extends Command
{
    protected $consumer;

    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('send-mails')
            ->setDescription('Send mails retrieved from queue mail')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->consumer->consume(array());
    }
}
