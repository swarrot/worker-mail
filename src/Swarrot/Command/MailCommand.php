<?php

namespace Swarrot\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Swarrot\Consumer;
use Symfony\Component\Console\Command\Command;
use Swarrot\Broker\MessageProviderInterface;
use Swarrot\Processor;
use Swarrot\Broker\PeclPackageMessageProvider;
use Swarrot\Processor\Stack;

class MailCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('send-mails')
            ->setDescription('Send mails retrieved from a queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'The queue to consume')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We create a connection to an AMQP broker and retrieve the queue "mail"
        $connection = new \AMQPConnection();
        $connection->connect();
        $channel = new \AMQPChannel($connection);
        $queue = new \AMQPQueue($channel);
        $queue->setName($input->getArgument('queue'));

        $messageProvider = new PeclPackageMessageProvider($queue);

        // We create a basic processor which use \SwiftMailer to send mails
        $processor = new Processor(
            \Swift_Mailer::newInstance(
                \Swift_SmtpTransport::newInstance('127.0.0.1', 1025)
            )
        );
        $stack = (new Stack\Builder())
            ->push('Swarrot\Processor\AckProcessor', $messageProvider)
            ->push('Swarrot\Processor\InstantRetryProcessor')
        ;

        // We can now create a Consumer with a message Provider and a Processor
        $consumer = new Consumer(
            $messageProvider,
            $stack->resolve($processor)
        );
        return $consumer->consume(array());
    }
}
