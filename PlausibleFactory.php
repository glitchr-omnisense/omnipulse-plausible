<?php

namespace Omnipulse\Plausible;

use Omnipulse\Config;
use Omnipulse\Exception\InvalidConfigException;
use Omnipulse\ProviderFactory;
use Omnipulse\ProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Plausible, from its options:
 *
 *   api_key  a Stats API key (account settings, "API Keys")
 *   site     the site's domain as Plausible knows it ("example.org")
 *   url      https://plausible.io, or a self-hosted Community Edition
 */
final class PlausibleFactory extends ProviderFactory
{
    public function __construct(private readonly ?HttpClientInterface $http = null)
    {
    }

    protected function populate(Config $c): void
    {
        $c->defaults([
            'omnipulse.factory_name' => 'plausible',
            'omnipulse.factory_title' => 'Plausible',
            'omnipulse.required_options' => ['api_key', 'site'],
            'url' => 'https://plausible.io',
        ]);
    }

    protected function build(Config $c): ProviderInterface
    {
        return new PlausibleProvider(
            $this->http ?? throw new InvalidConfigException('The "plausible" provider needs an HTTP client: give one to PlausibleFactory.'),
            (string) $c->string('api_key'),
            (string) $c->string('site'),
            rtrim($c->string('url') ?? 'https://plausible.io', '/'),
        );
    }
}
