<?php

namespace Drupal\chatbot\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides controller for the chatbot.
 */
class HelloController extends ControllerBase {

  /**
   * Renders the chatbot page.
   *
   * @return array
   *   A render array for the chatbot page.
   */
  public function hello() {
    $config = $this->config('chatbot.settings');

    return [
      '#theme' => 'chatbot',
      '#title' => $this->t('Chatbot'),
      '#attached' => [
        'library' => [
          'chatbot/chatbot',
        ],
        'drupalSettings' => [
          'chatbot' => [
            'apiKey' => $config->get('api_key'),
            'endpoint' => $config->get('endpoint'),
          ],
        ],
      ],
    ];
  }

  /**
   * Returns chatbot settings as JSON.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing chatbot settings.
   */
  public function getSettings() {
    $config = $this->config('chatbot.settings');

    $settings = [
      'apiKey' => $config->get('api_key'),
      'endpoint' => $config->get('endpoint'),
    ];

    return new JsonResponse($settings);
  }

}
