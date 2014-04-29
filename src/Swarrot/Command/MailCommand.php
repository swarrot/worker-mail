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
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Processor\Stack;
use Psr\Log\LoggerInterface;

class MailCommand extends Command
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        parent::__construct();
    }

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
            ),
            $this->logger
        );
        $stack = (new Stack\Builder())
            ->push('Swarrot\Processor\SignalHandler\SignalHandlerProcessor', $this->logger)
            ->push('Swarrot\Processor\ExceptionCatcher\ExceptionCatcherProcessor', $this->logger)
            ->push('Swarrot\Processor\MaxMessages\MaxMessagesProcessor', $this->logger)
            ->push('Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor', $this->logger)
            ->push('Swarrot\Processor\Ack\AckProcessor', $messageProvider, $this->logger)
            ->push('Swarrot\Processor\InstantRetry\InstantRetryProcessor', $this->logger)
        ;

        // We can now create a Consumer with a message Provider and a Processor
        $consumer = new Consumer(
            $messageProvider,
            $stack->resolve($processor),
            null,
            $this->logger
        );

        return $consumer->consume(array());
    }
}
