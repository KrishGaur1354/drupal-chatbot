<?php


namespace Drupal\chatbot\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ChatbotSettingsForm extends ConfigFormBase
{

  const SETTINGS = 'chatbot.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'chatbot_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [static::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config(static::SETTINGS);

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#default_value' => $config->get('endpoint'),
    ];

    // Attach the configuration settings to drupalSettings
    $form['#attached']['drupalSettings']['chatbot'] = [
      'api_key' => $config->get('api_key'),
      'endpoint' => $config->get('endpoint'),
    ];

    $form['#attached']['library'][] = 'chatbot/chatbot';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
