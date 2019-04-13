<?php

namespace Drupal\communications;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the Message Type entity type.
 *
 * @see \Drupal\node\Entity\MessageType
 */
class MessageTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(
    EntityInterface $message,
    $operation,
    AccountInterface $account
  ) {
    if ($operation === 'view') {
      // @I Create permissions for accessing messages.
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }

    if ($operation !== 'delete') {
      return parent::checkAccess($message, $operation, $account);
    }


    if ($message->isLocked()) {
      return AccessResult::forbidden()->addCacheableDependency($message);
    }

    return parent::checkAccess($message, $operation, $account)
      ->addCacheableDependency($message);
  }

}
