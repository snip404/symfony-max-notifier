<?php

namespace Symfony\Component\Notifier\Bridge\Max;

use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 */
final class MaxTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): MaxTransport
    {
        $scheme = $dsn->getScheme();

        if ('max' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'max', $this->getSupportedSchemes());
        }

        $token = $this->getToken($dsn);
        $channel = $dsn->getOption('channel');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new MaxTransport($token, $channel, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['max'];
    }

    private function getToken(Dsn $dsn): string
    {
        if (null === $dsn->getUser() && null === $dsn->getPassword()) {
            throw new IncompleteDsnException('Missing token.', 'max://'.$dsn->getHost());
        }

        if (null === $dsn->getPassword()) {
            throw new IncompleteDsnException('Malformed token.', 'max://'.$dsn->getHost());
        }

        return \sprintf('%s:%s', $dsn->getUser(), $dsn->getPassword());
    }
}
