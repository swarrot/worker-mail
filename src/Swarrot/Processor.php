<?php

namespace Swarrot;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

class Processor implements ProcessorInterface
{
    protected $swiftMailer;
    protected $logger;

    public function __construct(\Swift_Mailer $swiftMailer, LoggerInterface $logger)
    {
        $this->swiftMailer = $swiftMailer;
        $this->logger      = $logger;
    }

    public function process(Message $message, array $options)
    {
        $body = json_decode($message->getBody(), true);

        if (!is_array($body)) {
            $this->logger->error(
                'Unable to send message. Message body MUST be an array containing keys "subject", "to", "body".'
            );

            throw new \InvalidArgumentException(
                'Message body MUST be an array containing keys "subject", "to", "body".'
            );
        }

        foreach (array('subject', 'to', 'body') as $key) {
            if (!array_key_exists($key, $body)) {
                throw new \InvalidArgumentException(sprintf(
                    'No key "%s" defined in message. Existing: [%s]',
                    $key,
                    implode(', ', $body)
                ));
            }
        }

        $swiftMessage = \Swift_Message::newInstance($body['subject'])
            ->setFrom('no-reply@my-company.fr')
            ->setTo($body['to'])
            ->setBody($body['body'])
        ;

        $this->swiftMailer->send($swiftMessage);
    }
}
