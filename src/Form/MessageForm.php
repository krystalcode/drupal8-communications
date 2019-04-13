<?php

namespace Drupal\communications\Form;

use Drupal\communications\Entity\MessageType;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Message add/edit forms.
 *
 * @internal
 */
class MessageForm extends ContentEntityForm {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new MessageForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    EntityRepositoryInterface $entity_repository,
    PrivateTempStoreFactory $temp_store_factory,
    EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL,
    TimeInterface $time = NULL,
    AccountInterface $current_user
  ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->tempStoreFactory = $temp_store_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('tempstore.private'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Try to restore from temp store, this must be done before calling
    // parent::form().
    $store = $this->tempStoreFactory->get('message_preview');

    // Attempt to load from preview when the uuid is present unless we are
    // rebuilding the form.
    $request_uuid = \Drupal::request()->query->get('uuid');
    if (!$form_state->isRebuilding() && $request_uuid && $preview = $store->get($request_uuid)) {
      /** @var $preview \Drupal\Core\Form\FormStateInterface */

      $form_state->setStorage($preview->getStorage());
      $form_state->setUserInput($preview->getUserInput());

      // Rebuild the form.
      $form_state->setRebuild();

      // The combination of having user input and rebuilding the form means
      // that it will attempt to cache the form state which will fail if it is
      // a GET request.
      $form_state->setRequestMethod('POST');

      $this->entity = $preview->getFormObject()->getEntity();
      $this->entity->in_preview = NULL;

      $form_state->set('has_been_previewed', TRUE);
    }

    /** @var \Drupal\communications\Entity\MessageInterface $message */
    $message = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', [
        '@type' => MessageType::load($message->bundle())->label(),
        '@title' => $message->label(),
      ]);
    }

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $message->getChangedTime(),
    ];

    $form = parent::form($form, $form_state);

    $form['advanced']['#attributes']['class'][] = 'entity-meta';

    $form['meta'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -10,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
      '#access' => $this->currentUser->hasPermission('administer message'),
    ];
    $form['meta']['published'] = [
      '#type' => 'item',
      '#markup' => $message->isPublished() ? $this->t('Published') : $this->t('Not published'),
      '#access' => !$message->isNew(),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']],
    ];
    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      '#markup' => !$message->isNew() ? format_date($message->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['entity-meta__last-saved']],
    ];
    $form['meta']['author'] = [
      '#type' => 'item',
      '#title' => $this->t('Author'),
      '#markup' => $message->getOwner()->getUsername(),
      '#wrapper_attributes' => ['class' => ['entity-meta__author']],
    ];

    $form['status']['#group'] = 'footer';

    // Message author information for administrators.
    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['message-form-author'],
      ],
      '#attached' => [
        'library' => ['message/drupal.message'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['#attached']['library'][] = 'message/form';

    return $form;
  }

  /**
   * Entity builder updating the Message status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\communications\Entity\MessageInterface $message
   *   The Message updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\communications\Form\MessageForm::form()
   *
   * @deprecated in Drupal 8.4.x, will be removed before Drupal 9.0.0.
   *   The "Publish" button was removed.
   */
  public function updateStatus(
    $entity_type_id,
    MessageInterface $message,
    array $form,
    FormStateInterface $form_state
  ) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $element['#published_status'] ? $message->setPublished() : $message->setUnpublished();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $message = $this->entity;
    $preview_mode = $message->type->entity->getPreviewMode();

    $element['submit']['#access'] = $preview_mode != DRUPAL_REQUIRED || $form_state->get('has_been_previewed');

    $element['preview'] = [
      '#type' => 'submit',
      '#access' => $preview_mode != DRUPAL_DISABLED && ($message->access('create') || $message->access('update')),
      '#value' => t('Preview'),
      '#weight' => 20,
      '#submit' => ['::submitForm', '::preview'],
    ];

    $element['delete']['#access'] = $message->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * Form submission handler for the 'preview' action.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function preview(array $form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('message_preview');
    $this->entity->in_preview = TRUE;
    $store->set($this->entity->uuid(), $form_state);

    $route_parameters = [
      'message_preview' => $this->entity->uuid(),
      'view_mode_id' => 'full',
    ];

    $options = [];
    $query = $this->getRequest()->query;
    if ($query->has('destination')) {
      $options['query']['destination'] = $query->get('destination');
      $query->remove('destination');
    }
    $form_state->setRedirect('entity.message.preview', $route_parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $message = $this->entity;
    $insert = $message->isNew();
    $message->save();
    $message_link = $message->link($this->t('View'));
    $context = ['@type' => $message->getType(), '%title' => $message->label(), 'link' => $message_link];
    $t_args = ['@type' => node_get_type_label($message), '%title' => $message->link($message->label())];

    if ($insert) {
      $this->logger('content')->notice('@type: added %title.', $context);
      $this->messenger()->addStatus($this->t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      $this->messenger()->addStatus($this->t('@type %title has been updated.', $t_args));
    }

    if ($message->id()) {
      $form_state->setValue('message_id', $message->id());
      $form_state->set('message_id', $message->id());
      if ($message->access('view')) {
        $form_state->setRedirect(
          'entity.message.canonical',
          ['message' => $message->id()]
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }

      // Remove the preview entry from the temp store, if any.
      $store = $this->tempStoreFactory->get('message_preview');
      $store->delete($message->uuid());
    }
    else {
      // In the unlikely case something went wrong on save, the Message will be
      // rebuilt and the form will be redisplayed the same way as in preview.
      $this->messenger()->addError($this->t('The message could not be saved.'));
      $form_state->setRebuild();
    }
  }

}
