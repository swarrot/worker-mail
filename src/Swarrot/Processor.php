<?php

namespace Swarrot;

use Swarrot\Broker\Message;
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
        echo "Send message... ";

        $body = json_decode($message->getBody(), true);

        if (!is_array($body)) {
            echo "NOK\n";

            throw new \InvalidArgumentException(
                'Message body MUST be an array containing keys "subject", "to", "body".'
            );
        }

        foreach (array('subject', 'to', 'body') as $key) {
            if (!array_key_exists($key, $body)) {
                echo "NOK\n";

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

        echo "OK\n";
    }
}
