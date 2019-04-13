<?php

namespace Drupal\communications\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;

/**
 * Defines a controller to render a single Message in preview.
 */
class MessagePreviewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(
    EntityInterface $message_preview,
    $view_mode_id = 'full',
    $langcode = NULL
  ) {
    $message_preview->preview_view_mode = $view_mode_id;
    $build = parent::view($message_preview, $view_mode_id);

    $build['#attached']['library'][] = 'message/drupal.message.preview';

    // Don't render cache previews.
    unset($build['#cache']);

    return $build;
  }

  /**
   * The _title_callback for the page that renders a single Message in preview.
   *
   * @param \Drupal\Core\Entity\EntityInterface $message_preview
   *   The current Message.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $message_preview) {
    return $this->entityManager->getTranslationFromContext($message_preview)->label();
  }

}
