<?php

namespace Drupal\communications;

use Drupal\communications\Entity\MessageInterface;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the storage handler class for Messages.
 *
 * This extends the base storage class, adding required special handling for
 * Messages entities.
 */
class MessageStorage
  extends SqlContentEntityStorage
  implements MessageStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(MessageInterface $message) {
    return $this->database->query(
      'SELECT revision_id FROM {message_revision} WHERE message_id=:message_id ORDER BY revision_id',
      [':message_id' => $message->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT revision_id FROM {message_field_revision} WHERE uid = :uid ORDER BY revision_id',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(MessageInterface $message) {
    return $this->database->query(
      'SELECT COUNT(*) FROM {message_field_revision} WHERE message_id = :message_id AND default_langcode = 1',
      [':message_id' => $message->id()]
    )
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update('message')
      ->fields(['type' => $new_type])
      ->condition('type', $old_type)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('message_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
