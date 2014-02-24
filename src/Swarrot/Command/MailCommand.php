<?php

namespace Swarrot\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Swarrot\Consumer;
use Swarrot\Processor\Stack;
use Swarrot\AMQP\PeclPackageMessageProvider;
use Cilex\Command\Command;

class MailCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('send-mails')
            ->setDescription('Send mails retrieved from a queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue to consume')
            //->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Timeout (seconds) before exit', 300)
            //->addOption('max-messages', null, InputOption::VALUE_REQUIRED, 'Max messages to process before exit', 300)
            //->addOption('no-sighandler', null, InputOption::VALUE_NONE, 'Disable signal handlers')
            //->addOption('requeue-on-error', null, InputOption::VALUE_NONE, 'Requeue in the same queue on error')
            //->addOption('poll-interval', null, InputOption::VALUE_REQUIRED, 'Poll interval (in micro-seconds)', 500000)
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messageProvider = $this->getMessageProvider($input);
        $consumer = new Consumer($messageProvider);

        $processor = $this->getService('processor');

        return $consumer->consume($processor, array());
    }

    /**
     * getConsumer
     *
     * @param InputInterface $input
     *
     * @return Consumer
     */
    protected function getMessageProvider(InputInterface $input)
    {
        $connection = $this->getService('amqp.connection');
        $connection->connect();
        $channel = new \AMQPChannel($connection);
        $queue = new \AMQPQueue($channel);
        $queue->setName($input->getArgument('queue'));

        return new PeclPackageMessageProvider($queue);
    }
}
