# omnipulse/plausible

Formerly `omnireach/plausible` (renamed on 2026-11-10 with its family: `Omnireach\` is now `Omnipulse\`;
this package replaces the former name).

[omnisense/omnipulse](https://github.com/glitchr-omnisense/omnipulse)'s Plausible, under any PHP
host: visitors, visits, pageviews, bounce rate and visit duration of a site, by page, entry page,
source, country, device or day, combined as needed, through Plausible's Stats API v2 -
plausible.io or a self-hosted Community Edition.

## Install and a first call

```sh
composer require omnipulse/plausible
```

No account for this first call: Plausible's answer is recorded
(`Omnipulse\Testing\RecordedHttpClient` answers in place of the API), so nothing leaves the process.
A real call needs a Plausible account (or one's own instance) and a Stats API key.

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Omnipulse\Model\{Dimension, Metric, ReachQuery};
use Omnipulse\Plausible\PlausibleFactory;
use Omnipulse\Testing\RecordedHttpClient;

// Plausible's answer, recorded: nothing sent. HttpClient::create() (symfony/http-client) for a real call.
$http = RecordedHttpClient::routes([
    'POST /api/v2/query' => '{"results": [{"metrics": [3120, 3644], "dimensions": ["/"]}, {"metrics": [912, 1005], "dimensions": ["/ateliers"]}]}',
]);
$plausible = (new PlausibleFactory($http))->create(['api_key' => 'recorded', 'site' => 'atelier.example.org']);

$report = $plausible->audience(new ReachQuery(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-31'), [Dimension::EntryPage], [Metric::Visitors, Metric::Visits]));
foreach ($report->rows as $row) {
    echo json_encode($row->dimensions + $row->metrics, JSON_UNESCAPED_SLASHES), "\n";   // {"entry_page":"/","visitors":3120,"visits":3644} ...
}
```

With your credentials, through the family's registry:

```php
use Omnipulse\Plausible\PlausibleFactory;
use Omnipulse\Registry;
use Symfony\Component\HttpClient\HttpClient;

$registry = new Registry([new PlausibleFactory(HttpClient::create())], [
    'stats' => ['factory' => 'plausible', 'options' => ['api_key' => '...', 'site' => 'example.org']],
]);
```

Options: `api_key` (a Stats API key), `site` (the domain as Plausible knows it), `url`
(`https://plausible.io`).

## The API

`POST /api/v2/query` (<https://plausible.io/docs/stats-api>) with a bearer key: `site_id`,
`metrics`, `date_range`, `dimensions` (`event:page`, `visit:entry_page`, `visit:source`,
`visit:country`, `visit:device`, `time:day`), `filters` (`["is", dimension, [value]]`),
`pagination` (10,000 results a page at most). Plausible gives the bounce rate in percent: it is
divided by 100. Plausible itself refuses some mixes (session metrics with event dimensions); its
message comes back in a `ProviderException`.

**Terms** (<https://plausible.io/terms>): 600 requests an hour per API key by default. Reuse: no
PHP client of the Stats API v2 was found to reuse.

## Usage policy

Plausible's usage policy is declared as data in [`policy.json`](policy.json) (omnisense/contracts'
schema, named in `composer.json`'s `extra.omnisense.policy`), its terms read on
2026-11-10 (https://plausible.io/docs/stats-api): 600 requests an hour per API key (plausible.io's default; more on request); rights unknown; an API key. omnipulse's registry enforces it - a call over a
limit is never made, a 429 and its `Retry-After` hold every next call
(`UsageLimitReachedException`) - and a site may tighten it, never loosen it
(omnisense/omnipulse's docs/policies.md).

## Verified, and not

- Verified (the core's harness, PHP 8.4): entry pages with a filter, a daily history by device,
  pages of results up to the limit, what Plausible cannot answer, a refused key - against answers
  composed in the shape the Stats API v2 documents, not recorded; every class loading with the
  package's requirements alone (`BareTest`).
- Not verified live: no Plausible key was used.

## Licence

MIT. An unofficial client, affiliated with Plausible Analytics in no way.
