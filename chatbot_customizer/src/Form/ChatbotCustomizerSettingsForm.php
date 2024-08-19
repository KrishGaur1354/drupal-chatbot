<?php

//
namespace Drupal\chatbot_customizer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatbotCustomizerSettingsForm extends ConfigFormBase {
  protected $languageManager;

  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager')
    );
  }

  protected function getEditableConfigNames() {
    return ['chatbot_customizer.settings', 'chatbot.settings'];
  }

  public function getFormId() {
    return 'chatbot_customizer_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('chatbot_customizer.settings');
    $chatbot_config = $this->config('chatbot.settings');

    $form['chatbot_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Chatbot Color'),
      '#default_value' => $config->get('chatbot_color') ?: '#0000FF',
      '#description' => $this->t('Choose the primary color for the chatbot.'),
    ];

    $form['enable_speech_to_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Speech-to-Text'),
      '#default_value' => $config->get('enable_speech_to_text') ?: FALSE,
      '#description' => $this->t('Allow users to input text via speech.'),
    ];

    $languages = $this->languageManager->getLanguages();
    $language_options = [];
    foreach ($languages as $langcode => $language) {
      $language_options[$langcode] = $language->getName();
    }

    $form['supported_languages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Supported Languages'),
      '#options' => $language_options,
      '#default_value' => $config->get('supported_languages') ?: [],
      '#description' => $this->t('Select the languages supported by the chatbot.'),
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $chatbot_config->get('api_key'),
      '#description' => $this->t('Enter the API key for the chatbot.'),
      '#required' => TRUE,
    ];

    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#default_value' => $chatbot_config->get('endpoint'),
      '#description' => $this->t('Enter the API endpoint for the chatbot.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatbot_customizer.settings')
      ->set('chatbot_color', $form_state->getValue('chatbot_color'))
      ->set('enable_speech_to_text', $form_state->getValue('enable_speech_to_text'))
      ->set('supported_languages', array_filter($form_state->getValue('supported_languages')))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
