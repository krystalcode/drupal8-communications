<?php

namespace Drupal\communications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Message Type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "message_type",
 *   label = @Translation("Message type"),
 *   handlers = {
 *     "access" = "Drupal\communications\MessageTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\communications\Form\MessageTypeForm",
 *       "edit" = "Drupal\communications\Form\MessageTypeForm",
 *       "delete" = "Drupal\communications\Form\MessageTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\communications\MessageTypeListBuilder",
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer message_type",
 *   config_prefix = "message_type",
 *   bundle_of = "message",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "add-form" = "/admin/communications/config/message-types/add",
 *     "delete-form" = "/admin/communications/config/message-types/{message_type}/delete",
 *     "edit-form" = "/admin/communications/config/message-types/{message_type}/edit",
 *     "collection" = "/admin/communications/config/message-types"
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *     "description",
 *     "help",
 *     "new_revision",
 *     "preview_mode",
 *     "display_submitted",
 *   },
 * )
 *
 * @I Create permissions for administering Message Types
 * @I Create custom admin menu section
 */
class MessageType
  extends ConfigEntityBundleBase
  implements MessageTypeInterface {

  /**
   * The machine name of this Message Type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  protected $type;

  /**
   * The human-readable name of the Message Type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  protected $name;

  /**
   * A brief description of this Message Type.
   *
   * @var string
   */
  protected $description;

  /**
   * Help information shown to the user when creating a Message of this type.
   *
   * @var string
   */
  protected $help;

  /**
   * Default value of the 'Create new revision' checkbox of this Message Type.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

  /**
   * The preview mode.
   *
   * @var int
   */
  protected $preview_mode = DRUPAL_OPTIONAL;

  /**
   * Display setting for author and date 'Submitted by' Message information.
   *
   * @var bool
   */
  protected $display_submitted = TRUE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('node.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function displaySubmitted() {
    return $this->display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplaySubmitted($display_submitted) {
    $this->display_submitted = $display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewMode() {
    return $this->preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreviewMode($preview_mode) {
    $this->preview_mode = $preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelp() {
    return $this->help;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update) {
      return;
    }

    // If the ID of this Message Type has changed, update the type for all
    // Messages of this type.
    $this->updateMessageTypes();

    // Clear the cached field definitions as some settings affect the field
    // definitions.
    $this->entityManager()->clearCachedFieldDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(
    EntityStorageInterface $storage,
    array $entities
  ) {
    parent::postDelete($storage, $entities);

    // Clear the Message Type cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->isNewRevision();
  }

  /**
   * Update the type for all Messages if the ID of this Message Type has changed.
   */
  protected function updateMessageTypes() {
    if ($this->getOriginalId() == $this->id()) {
      return;
    }

    $update_count = message_type_update_messages(
      $this->getOriginalId(),
      $this->id()
    );
    if ($update_count) {
      drupal_set_message(
        \Drupal::translation()->formatPlural(
          $update_count,
          'Changed the message type of 1 message from %old-type to %type.',
          'Changed the message type of @count messages from %old-type to %type.',
          [
            '%old-type' => $this->getOriginalId(),
            '%type' => $this->id(),
          ]
        )
      );
    }
  }

}
