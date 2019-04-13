<?php

namespace Drupal\communications\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for Message Type forms.
 *
 * @internal
 */
class MessageTypeForm extends BundleEntityFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   *
   * @I Use the entity type manager and the entity field manager instead
   */
  protected $entityManager;

  /**
   * Constructs a new MessageTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add message type');
      $fields = $this->entityManager->getBaseFieldDefinitions('message');
      // Create a Message with a fake bundle using the type's UUID so that we can
      // get the default values for workflow settings.
      // @todo Make it possible to get default values without an entity.
      //   https://www.drupal.org/node/2318187
      $message = $this->entityManager
        ->getStorage('message')
        ->create(['type' => $type->uuid()]);
    }
    else {
      $form['#title'] = $this->t(
        'Edit %label message type',
        ['%label' => $type->label()]
      );
      $fields = $this->entityManager->getFieldDefinitions(
        'message',
        $type->id()
      );
      // Create a message to get the current values for workflow settings
      // fields.
      $message = $this->entityManager
        ->getStorage('message')
        ->create(['type' => $type->id()]);
    }

    $form['name'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => $this->t(
        'The human-readable name of this message type. This text will be
         displayed as part of the list on the <em>Add message</em> page. This
         name must be unique.'
      ),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['type'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $type->isLocked(),
      '#machine_name' => [
        'exists' => ['Drupal\communications\Entity\MessageType', 'load'],
        'source' => ['name'],
      ],
      '#description' => $this->t(
        'A unique machine-readable name for this message type. It must only
         contain lowercase letters, numbers, and underscores. This name will be
         used for constructing the URL of the %message-add page, in which
         underscores will be converted into hyphens.',
        ['%message-add' => $this->t('Add message')]
      ),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => $this->t(
        'This text will be displayed on the <em>Add new message</em> page.'
      ),
    ];

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['message/drupal.message_types'],
      ],
    ];

    $form['submission'] = [
      '#type' => 'details',
      '#title' => $this->t('Submission form settings'),
      '#group' => 'additional_settings',
      '#open' => TRUE,
    ];
    $form['submission']['preview_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Preview before submitting'),
      '#default_value' => $type->getPreviewMode(),
      '#options' => [
        DRUPAL_DISABLED => $this->t('Disabled'),
        DRUPAL_OPTIONAL => $this->t('Optional'),
        DRUPAL_REQUIRED => $this->t('Required'),
      ],
    ];
    $form['submission']['help']  = [
      '#type' => 'textarea',
      '#title' => $this->t('Explanation or submission guidelines'),
      '#default_value' => $type->getHelp(),
      '#description' => $this->t(
        'This text will be displayed at the top of the page when creating or
         editing messages of this type.'
      ),
    ];
    $form['workflow'] = [
      '#type' => 'details',
      '#title' => $this->t('Publishing options'),
      '#group' => 'additional_settings',
    ];
    $workflow_options = [
      'status' => $message->status->value,
      'revision' => $type->isNewRevision(),
    ];
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => [
        'status' => $this->t('Published'),
        'revision' => $this->t('Create new revision'),
      ],
      '#description' => $this->t(
        'Users with the <em>Administer messages</em> permission will be able to
         override these options.'
      ),
    ];
    // @I review whether we need language support and to what extend
    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle(
        'message',
        $type->id()
      );
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'message',
          'bundle' => $type->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }
    $form['display'] = [
      '#type' => 'details',
      '#title' => $this->t('Display settings'),
      '#group' => 'additional_settings',
    ];
    $form['display']['display_submitted'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display author and date information'),
      '#default_value' => $type->displaySubmitted(),
      '#description' => $this->t('Author username and publish date will be displayed.'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save message type');
    $actions['delete']['#value'] = $this->t('Delete message type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    // @I Review the 0-check for message type names is needed
    if ($id == '0') {
      $form_state->setErrorByName(
        'type',
        $this->t(
          "Invalid machine-readable name. Enter a name other than %invalid.",
          ['%invalid' => $id]
        )
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->setNewRevision($form_state->getValue(['options', 'revision']));
    $type->set('type', trim($type->id()));
    $type->set('name', trim($type->label()));

    $status = $type->save();

    $t_args = ['%name' => $type->label()];

    if ($status == SAVED_UPDATED) {
      drupal_set_message(
        $this->t('The message type %name has been updated.', $t_args)
      );
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(
        $this->t('The message type %name has been added.', $t_args)
      );
      $context = array_merge(
        $t_args,
        ['link' => $type->link($this->t('View'), 'collection')]
      );
      $this->logger('communications')->notice('Added message type %name.', $context);
    }

    $fields = $this->entityManager->getFieldDefinitions('message', $type->id());
    // Update workflow options.
    // @todo Make it possible to get default values without an entity.
    //   https://www.drupal.org/node/2318187
    $message = $this->entityManager
      ->getStorage('message')
      ->create(['type' => $type->id()]);
    $value = (bool) $form_state->getValue(['options', 'status']);
    if ($message->get('status')->value != $value) {
      $fields['status']->getConfig($type->id())
        ->setDefaultValue($value)
        ->save();
    }

    $this->entityManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($type->urlInfo('collection'));
  }

}
