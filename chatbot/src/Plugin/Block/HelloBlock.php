<?php

namespace Drupal\chatbot\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'Chatbot' Block.
 *
 * @Block(
 *   id = "chatbot",
 *   admin_label = @Translation("Chatbot"),
 *   category = @Translation("Custom"),
 * )
 */
class HelloBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $build = [];

    $build['#attached']['library'][] = 'chatbot/chatbot';

    $build['chatbot'] = [
      '#theme' => 'chatbot',
      '#title' => 'chatbot',
      '#attached' => [
        'drupalSettings' => [
          'chatbot' => [
            'color' => $config['chatbot_color'] ?? '#0000FF',
            'enable_speech_to_text' => $config['enable_speech_to_text'] ?? FALSE,
            'api_key' => $config['api_key'] ?? '',
            'endpoint' => $config['endpoint'] ?? '',
          ],
        ],
      ],
    ];

    $logoFid = $config['chatbot_logo'] ?? NULL;
    if (!empty($logoFid)) {
      if ($file = File::load($logoFid)) {
        $build['#attached']['drupalSettings']['chatbot']['logo_url'] = $file->createFileUrl();
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['chatbot_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Chatbot Color'),
      '#default_value' => $config['chatbot_color'] ?? '#0000FF',
      '#description' => $this->t('Choose the primary color for the chatbot.'),
    ];

    $form['chatbot_logo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Chatbot Logo'),
      '#upload_location' => 'public://chatbot_logo/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
      '#default_value' => $config['chatbot_logo'] ?? NULL,
      '#description' => $this->t('Upload a logo for the chatbot (max 2MB). Allowed extensions: png, gif, jpg, jpeg.'),
    ];

    $form['enable_speech_to_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Speech-to-Text'),
      '#default_value' => $config['enable_speech_to_text'] ?? FALSE,
      '#description' => $this->t('Allow users to input text via speech.'),
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config['api_key'] ?? '',
      '#description' => $this->t('Enter the API key for the chatbot.'),
      '#required' => TRUE,
    ];

    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#default_value' => $config['endpoint'] ?? '',
      '#description' => $this->t('Enter the API endpoint for the chatbot.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();

    $this->configuration['chatbot_color'] = $values['chatbot_color'];
    $this->configuration['enable_speech_to_text'] = $values['enable_speech_to_text'];
    $this->configuration['api_key'] = $values['api_key'];
    $this->configuration['endpoint'] = $values['endpoint'];

    $logo = $values['chatbot_logo'];
    if (!empty($logo[0])) {
      $file = File::load($logo[0]);
      $file->setPermanent();
      $file->save();
      $this->configuration['chatbot_logo'] = $logo[0];
    }
  }
}
