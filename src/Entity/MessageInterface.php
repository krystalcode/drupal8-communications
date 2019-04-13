<?php

namespace Drupal\communications\Entity;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a Message entity.
 */
interface MessageInterface extends
  ContentEntityInterface,
  EntityChangedInterface,
  EntityOwnerInterface,
  RevisionLogInterface,
  EntityPublishedInterface {

  /**
   * Denotes that the Message is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the Message is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the Message Type.
   *
   * @return string
   *   The Message Type.
   */
  public function getType();

  /**
   * Gets the Message title.
   *
   * @return string
   *   Title of the Message.
   */
  public function getTitle();

  /**
   * Sets the Message title.
   *
   * @param string $title
   *   The Message title.
   *
   * @return \Drupal\communications\MessageInterface
   *   The called Message entity.
   */
  public function setTitle($title);

  /**
   * Gets the Message creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Message.
   */
  public function getCreatedTime();

  /**
   * Sets the Message creation timestamp.
   *
   * @param int $timestamp
   *   The Message creation timestamp.
   *
   * @return \Drupal\communications\MessageInterface
   *   The called Message entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Message publication timestamp.
   *
   * @return int
   *   Publication timestamp of the Message.
   */
  public function getPublishedTime();

  /**
   * Sets the Message publication timestamp.
   *
   * @param int $timestamp
   *   The Message publication timestamp.
   *
   * @return \Drupal\communications\MessageInterface
   *   The called Message entity.
   */
  public function setPublishedTime($timestamp);

  /**
   * Gets the Message revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Message revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\communications\MessageInterface
   *   The called Message entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Message revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::getRevisionUser() instead.
   */
  public function getRevisionAuthor();

  /**
   * Sets the Message revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\communications\MessageInterface
   *   The called Message entity.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::setRevisionUserId() instead.
   */
  public function setRevisionAuthorId($uid);

}
