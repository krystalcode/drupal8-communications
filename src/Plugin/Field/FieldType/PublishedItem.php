<?php

namespace Drupal\communications\Plugin\Field\FieldType;

/**
 * Defines an field type for holding the publication time for entities.
 *
 * @FieldType(
 *   id = "published",
 *   label = @Translation("Published"),
 *   description = @Translation("An entity field containing the UNIX timestamp of when the entity was published."),
 *   no_ui = TRUE,
 *   default_widget = "datetime_timestamp",
 *   default_formatter = "timestamp"
 * )
 */
class PublishedItem extends TimestampItem {

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();

    $entity->this->getEntity();

    // Set the timestamp to request time if the entity is new and it is
    // published, or if it getting published for the first time.
    if (!$entity->isPublished()) {
      return;
    }

    if (!$entity->isNew() && $entity->original->isPublished()) {
      return;
    }

    $this->value = REQUEST_TIME;
  }

}
