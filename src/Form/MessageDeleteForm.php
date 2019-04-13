<?php

namespace Drupal\communications\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting a Message.
 *
 * @internal
 */
class MessageDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    /** @var \Drupal\communications\Entity\MessageInterface $message */
    $message = $this->getEntity();

    $message_type_storage = $this->entityManager->getStorage('message_type');
    $message_type = $message_type_storage->load($message->bundle())->label();

    if (!$message->isDefaultTranslation()) {
      return $this->t('@language translation of the @type %label has been deleted.', [
        '@language' => $message->language()->getName(),
        '@type' => $message_type,
        '%label' => $message->label(),
      ]);
    }

    return $this->t('The @type %title has been deleted.', [
      '@type' => $message_type,
      '%title' => $this->getEntity()->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    /** @var \Drupal\communications\Entity\MessageInterface $message */
    $message = $this->getEntity();
    $this->logger('content')->notice(
      '@type: deleted %title.',
      ['@type' => $message->getType(), '%title' => $message->label()]
    );
  }

}
