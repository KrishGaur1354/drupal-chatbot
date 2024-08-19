<?php

namespace Drupal\chatbot\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get chatbot settings.
 *
 * @RestResource(
 *   id = "chatbot_settings",
 *   label = @Translation("Chatbot settings"),
 *   uri_paths = {
 *     "canonical" = "/api/chatbot/settings"
 *   }
 * )
 */
class ChatbotSettingsResource extends ResourceBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a new ChatbotSettingsResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory,
    Request $current_request,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('chatbot'),
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Retrieves the chatbot settings.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object containing the chatbot settings.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the configuration cannot be retrieved.
   */
  public function get(): ResourceResponse {
    $this->logRequestDetails();

    $config = $this->configFactory->get('chatbot.settings');
    if (!$config) {
      $this->logger->error('Unable to load chatbot.settings configuration.');
      return new ResourceResponse(['error' => 'Configuration not found'], 500);
    }

    $this->logRawConfig($config);

    $data = [
      'api_key' => $config->get('api_key'),
      'endpoint' => $config->get('endpoint'),
    ];

    $this->logReturnedData($data);
    $this->checkEmptyData($data);

    $response = new ResourceResponse($data);
    $this->setCacheHeaders($response, $config);
    $this->logResponseDetails($response);

    return $response;
  }

  /**
   * Logs the current request details.
   */
  private function logRequestDetails(): void {
    $this->logger->debug(sprintf('Current request details: %s', json_encode([
      'method' => $this->currentRequest->getMethod(),
      'uri' => $this->currentRequest->getUri(),
      'query' => $this->currentRequest->query->all(),
    ])));
  }

  /**
   * Logs the raw configuration object.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The configuration object.
   */
  private function logRawConfig($config): void {
    $this->logger->debug(sprintf('Raw config object: %s', json_encode($config->get())));
  }

  /**
   * Logs the data being returned.
   *
   * @param array $data
   *   The data being returned.
   */
  private function logReturnedData(array $data): void {
    $this->logger->debug(sprintf('Data being returned: %s', json_encode($data)));
  }

  /**
   * Checks if the data is empty and logs a warning if so.
   *
   * @param array $data
   *   The data to check.
   */
  private function checkEmptyData(array $data): void {
    if (empty($data['api_key']) && empty($data['endpoint'])) {
      $this->logger->warning('No configuration data found for chatbot.settings');
    }
  }

  /**
   * Sets cache headers for the response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   The response object.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The configuration object.
   */
  private function setCacheHeaders(ResourceResponse $response, $config): void {
    $response->addCacheableDependency($config);
    $response->headers->set('Cache-Control', 'no-cache, must-revalidate, max-age=0');
  }

  /**
   * Logs the response details.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   The response object.
   */
  private function logResponseDetails(ResourceResponse $response): void {
    $this->logger->debug(sprintf('Response details: %s', json_encode([
      'status_code' => $response->getStatusCode(),
      'headers' => $response->headers->all(),
    ])));
  }

}
