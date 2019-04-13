<?php

namespace Drupal\communications\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting Message Types.
 *
 * @internal
 */
class MessageTypeDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $num_messages = $this->entityTypeManager
      ->getStorage('message')
      ->getQuery()
      ->condition('type', $this->entity->id())
      ->count()
      ->execute();

    if ($num_messages) {
      $caption = '<p>' .
        $this->formatPlural(
          $num_messages,
          '%type is used by 1 message on your site. You can not remove this
           content type until you have removed all of the %type messages.',
          '%type is used by @count messages on your site. You may not remove
           %type until you have removed all of the %type messages.',
          ['%type' => $this->entity->label()]) .
        '</p>';

      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];

      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
