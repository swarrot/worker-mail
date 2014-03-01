# Worker mail

This mail worker is an example of what you can do with
[swarrot](https://github.com/swarrot/swarrot) and some processors.
Its goal is obviously to send mails.

As it's just an demo project, it MUST not be used in production environment.

## Expected message format

The message retrieved from your broker MUST be in json with `to`, `subject`,
`body` fields.

    {
        "to": "foobar@example.org",
        "subject": "It works !",
        "body": "This mail have been sent using the mail-worker, which use swarrot."
    }

## Sending mails

See:

    ./console send-mails -h

## License

This project is released under the MIT License. See the bundled LICENSE file for details.
