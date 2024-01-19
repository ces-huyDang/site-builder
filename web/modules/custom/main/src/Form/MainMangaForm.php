<?php

namespace Drupal\main\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\main\Services\MainService;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a main form.
 */
class MainMangaForm extends FormBase {

  /**
   * Main Service.
   *
   * @var \Drupal\main\Services\MainService
   */
  protected $mainService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  protected $minLength = 10;

  /**
   * {@inheritdoc}
   */
  public function __construct(
        MainService $main_service,
        EntityTypeManagerInterface $entityTypeManager
    ) {
    $this->mainService = $main_service;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return $container->get('manga.service');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'main_manga_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $nid = NULL): array {
    $categories = $this->mainService->getTaxonomyKeyValue('categories');
    $statuses = $this->mainService->getTaxonomyKeyValue('status');
    $type = '#type';
    $title = '#title';
    $require = '#required';
    $maxlength = '#maxlength';
    $minlength = '#minlength';

    $form['title'] = [
      $type => 'textfield',
      $title  => $this->t('Title'),
      $require => TRUE,
      $maxlength => 100,
      $minlength => $this->minLength,
    ];

    $form['cover_art'] = [
      $type => 'media_library',
      $require => TRUE,
      $title  => $this->t('Cover Art'),
      '#cardinality' => 1,
      '#allowed_bundles' => ['image'],
    ];

    $form['author'] = [
      $type => 'textfield',
      $title  => $this->t('Author'),
      $require => FALSE,
      $maxlength => 50,
      $minlength => $this->minLength,
    ];

    $form['description'] = [
      $type => 'text_format',
      $title  => $this->t('Description'),
      '#format' => "full_html",
      '#allowed_formats' => ['full_html'],
      $require => FALSE,
      $minlength => $this->minLength,
      $maxlength => 10000,
    ];

    $form['categories'] = [
      $type => 'checkboxes',
      $title => $this->t('Categories'),
      $require => TRUE,
      '#options' => $categories,
    ];

    $form['status'] = [
      $type => 'radios',
      $title => $this->t('Status'),
      $require => TRUE,
      '#options' => $statuses,
    ];

    $form['actions'] = [
      $type => 'actions',
      'submit' => [
        $type => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    if (isset($nid) && is_numeric($nid)) {
      $default_value = '#default_value';
      $node = Node::load($nid);
      if (isset($node)) {
        $form['form_title'] = "Edit Manga";
        $form['nid'] = $nid;
        $form['title'][$default_value] = $node->get('title')->getValue()[0]['value'];
        if (isset($node->get('field_author')->getValue()[0]['value'])) {
          $form['author'][$default_value] = $node->get('field_author')->getValue()[0]['value'];
        }
        if (!empty($node->get('field_description')->getValue()[0]['value'])) {
          $form['description'][$default_value] = $node->get('field_description')->getValue()[0]['value'];
        }
        $form['cover_art'][$default_value] = $node->get('field_cover_art')->getValue()[0]['target_id'];
        $tids = $this->mainService->getTaxonomyIdFromNodeField(
              $node->get('field_manga_categories')->getValue()
          );
        $form['categories'][$default_value] = $tids;
        $form['status'][$default_value] = $node->get('field_status')->getValue()[0]['target_id'];
      }
    }
    $form['#theme'] = 'main_manga_form';
    return $form;
  }

  /**
   * Validate field value duplication.
   */
  public function validateDuplication(
        string $form_field_name,
        array &$form,
        FormStateInterface $form_state,
    ) {
    $field_value = $form_state->getValue($form_field_name);
    $query = \Drupal::entityQuery('node');
    $query->accessCheck(TRUE);
    $query->condition($form_field_name, $field_value);
    $query->count();
    $existing_value = $query->execute();

    if ($existing_value) {
      $form_state->setErrorByName(
            $form_field_name,
            $this->t($form[$form_field_name]['#title']->render() . ' already exists.')
        );
    }
  }

  /**
   * Form textfield minlength validation handler.
   */
  public function validateTextMinLength(
        string $form_field_name,
        array &$form,
        FormStateInterface $form_state,
    ) {
    $field_value = NULL;
    if ($form_field_name === 'description') {
      $field_value = $form_state->getValue($form_field_name)['value'];
    }
    else {
      $field_value = $form_state->getValue($form_field_name);
    }
    if (mb_strlen($field_value) >= $this->minLength) {
      return NULL;
    }
    $form_state->setErrorByName(
          $form_field_name,
          $this->t(
              $form[$form_field_name]['#title']->render() .
              ' must have at least 10 characters.'
          )
      );
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (empty($form['nid'])) {
      $this->validateDuplication('title', $form, $form_state);
    }
    $this->validateTextMinLength('title', $form, $form_state);
    if (!empty($form_state->getValue('author'))) {
      $this->validateTextMinLength('author', $form, $form_state);
    }
    if (!empty($form_state->getValue('description')['value'])) {
      $this->validateTextMinLength('description', $form, $form_state);
    };
    if (!empty($form_state->getValue('cover_art'))) {
      $images_quantity = count(explode(",", $form_state->getValue('cover_art')));
      if ($images_quantity > 1) {
        $form_state->setErrorByName(
              'cover_art',
              $this->t('Only one image is allowed.')
          );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (isset($form['nid']) && is_numeric($form['nid'])) {
      $nid = $form['nid'];
      $node = Node::load($nid);
      if (isset($node)) {
        $node->set('title', trim($form_state->getValue('title')));
        $node->set('field_author', trim($form_state->getValue('author')));
        $node->set('field_description', ($form_state->getValue('description')));
        $node->set('field_cover_art', $form_state->getValue('cover_art'));
        $node->set('field_manga_categories', $form_state->getValue('categories'));
        $node->set('field_status', $form_state->getValue('status'));
        $node->save();
        $this->messenger()->addStatus($this->t('Updated the Manga.'));
      }
    }
    else {
      $new_node = $this->entityTypeManager->getStorage('node')->create(
            [
              'type' => 'manga_overview',
              'title' => trim($form_state->getValue('title')),
              'field_author' => trim($form_state->getValue('author')),
              'field_description' => ($form_state->getValue('description')),
              'field_cover_art' => $form_state->getValue('cover_art'),
              'field_manga_categories' => $form_state->getValue('categories'),
              'field_status' => $form_state->getValue('status'),
              'field_subscribers' => 0,
              'field_views' => 0,
            ]
        );
      $new_node->save();
      $this->messenger()->addStatus($this->t('Added new Manga.'));
    }
  }

}
