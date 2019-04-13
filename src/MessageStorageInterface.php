<?php

namespace Drupal\communications;

use Drupal\communications\Entity\MessageInterface;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for Message entity storage classes.
 */
interface MessageStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of revision IDs for a specific Message.
   *
   * @param \Drupal\communications\Entity\MessageInterface $message
   *   The Message entity.
   *
   * @return int[]
   *   The Message revision IDs (in ascending order).
   */
  public function revisionIds(MessageInterface $message);

  /**
   * Gets a list of revision IDs having a given user as Message author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   The Message revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\communications\Entity\MessageInterface $message
   *   The Message entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(MessageInterface $message);

  /**
   * Updates all Messages of one type to be of another type.
   *
   * @param string $old_type
   *   The current Message Type ID of the Messages.
   * @param string $new_type
   *   The new Message Type ID of the Messages.
   *
   * @return int
   *   The number of Messages whose type field was modified.
   */
  public function updateType($old_type, $new_type);

  /**
   * Unsets the language for all Messages with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
