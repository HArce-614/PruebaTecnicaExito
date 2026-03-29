<?php
// web/modules/custom/custom_events/src/Service/CountryService.php

declare(strict_types=1);

namespace Drupal\custom_events\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Fetches and caches country data from the REST Countries API.
 */
class CountryService {

  private const API_URL  = 'https://restcountries.com/v3.1/all?fields=name';
  private const CACHE_ID = 'custom_events:countries';
  private const CACHE_TTL = 86400; // 24 hours in seconds.

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly CacheBackendInterface $cache,
    private readonly LoggerInterface $logger,
  ) {}

  /**
   * Returns an alphabetically sorted associative array of countries.
   *
   * Keys and values are both the common country name, e.g.:
   *   ['Colombia' => 'Colombia', 'France' => 'France', ...]
   *
   * On success the result is cached for 24 hours.
   * On failure stale cache is returned when available; otherwise an exception
   * is re-thrown so callers can degrade gracefully.
   *
   * @return array<string, string>
   *
   * @throws \RuntimeException
   *   When the API call fails and no cached data is available.
   */
  public function getCountries(): array {
    // Return fresh cached data when available.
    $cached = $this->cache->get(self::CACHE_ID);
    if ($cached !== FALSE && isset($cached->data)) {
      return $cached->data;
    }

    try {
      $response = $this->httpClient->request('GET', self::API_URL, [
        'timeout' => 10,
        'verify'  => false,
        'headers' => ['Accept' => 'application/json'],
      ]);

      $body = (string) $response->getBody();
      $raw  = json_decode($body, TRUE, 512, JSON_THROW_ON_ERROR);

      $countries = [];
      foreach ($raw as $entry) {
        $name = $entry['name']['common'] ?? NULL;
        if ($name !== NULL && $name !== '') {
          $countries[$name] = $name;
        }
      }

      ksort($countries, SORT_STRING | SORT_FLAG_CASE);

      $this->cache->set(
        self::CACHE_ID,
        $countries,
        \Drupal::time()->getRequestTime() + self::CACHE_TTL,
        ['custom_events_countries'],
      );

      return $countries;
    }
    catch (GuzzleException|\JsonException $e) {
      $this->logger->error(
        'CountryService: failed to fetch countries from REST API — @message',
        ['@message' => $e->getMessage()],
      );

      // Attempt to return stale (expired) cache as a fallback.
      $stale = $this->cache->get(self::CACHE_ID, TRUE); // allow invalid.
      if ($stale !== FALSE && isset($stale->data)) {
        $this->logger->warning('CountryService: serving stale country cache as fallback.');
        return $stale->data;
      }

      throw new \RuntimeException(
        'Unable to retrieve country list and no cached data is available.',
        0,
        $e,
      );
    }
  }

}
