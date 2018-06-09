<?php

namespace Drupal\communications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Communications settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'communications_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['communications.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('communications.settings');

    $form['domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Domain'),
      '#description' => $this->t('The domain for which to fetch email messages.'),
      '#default_value' => $config->get('domain'),
      '#required' => TRUE,
    ];
    $form['mailgun_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mailgun API key'),
      '#description' => $this->t('The API key that will be used to authenticate with Mailgun.'),
      '#default_value' => $config->get('mailgun_api_key'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('communications.settings')
      ->set('domain', $form_state->getValue('domain'))
      ->set('mailgun_api_key', $form_state->getValue('mailgun_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
