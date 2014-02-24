<?php

namespace Swarrot;

use Swarrot\AMQP\Message;
use Swarrot\Processor\ProcessorInterface;

class Processor implements ProcessorInterface
{
    protected $swiftMailer;

    public function __construct(\Swift_Mailer $swiftMailer)
    {
        $this->swiftMailer = $swiftMailer;
    }

    public function __invoke(Message $message, array $options)
    {
        $body = json_decode($message->getBody(), true);

        $swiftMessage = \Swift_Message::newInstance($body['subject'])
            ->setFrom('no-reply@my-company.fr')
            ->setTo($body['to'])
            ->setBody($body['body'])
        ;

        $this->swiftMailer->send($swiftMessage);
    }
}
