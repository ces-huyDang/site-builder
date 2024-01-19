<?php

namespace Drupal\main\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\main\Services\MainService;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a main chapter form.
 */
class MainChapterForm extends FormBase
{

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
    public static function create(ContainerInterface $container)
    {
        return $container->get('chapter.service');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId(): string
    {
        return 'main_chapter_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, int $nid = null): array
    {
        $type = '#type';
        $require = '#required';
        $title = '#title';
        $manga_list = $this->mainService->getMangaListKeyValue();

        $form['title'] = [
        $type => 'textfield',
        $title  => $this->t('Chapter'),
        $require => true,
        '#maxlength' => 100,
        '#minlength' => 9,
        ];

        $form['field_manga'] = [
        '#type' => 'select',
        '#title' => t('Manga Name'),
        $require => true,
        '#options' => $manga_list,
        ];

        $form['field_content'] = [
        $type => 'media_library',
        $require => true,
        $title  => $this->t('Content'),
        '#cardinality' => 999,
        '#allowed_bundles' => ['image'],
        ];

        $form['actions'] = [
        $type => 'actions',
        'submit' => [
        $type => 'submit',
        '#value' => $this->t('Save'),
        ],
        ];

        if (!isset($nid) || !is_numeric($nid)) {
            $form['title']['#default_value'] = 'Chapter ';
            return $form;
        }
        $default_value = '#default_value';
        $node = Node::load($nid);
        if (empty($node)) {
            return $form;
        }
        $form['form_title'] = "Edit Chapter";
        $form['nid'] = $nid;
        $form['title'][$default_value] = $node->get('title')->getValue()[0]['value'];
        $form['field_manga'][$default_value] = $node->get('field_manga')->getValue()[0]['target_id'];
        $field_content = $this->mainService->getFieldContentStringtValue(
            $node->get('field_content')->getValue()
        );
        $form['field_content'][$default_value] = $field_content;
        $form['#theme'] = 'main_chapter_form';
        return $form;
    }

    /**
     * Form textfield minlength validation handler.
     */
    public function validateTextMinLength(
        string $form_field_name,
        array &$form,
        FormStateInterface $form_state,
    ) {
        $field_value = null;
        if ($form_field_name === 'description') {
            $field_value = $form_state->getValue($form_field_name)['value'];
        }
        else {
            $field_value = $form_state->getValue($form_field_name);
        }
        if (mb_strlen($field_value) >= 9) {
            return null;
        }
        $form_state->setErrorByName(
            $form_field_name,
            $this->t(
                $form[$form_field_name]['#title']->render() .
                ' must have at least 9 characters.'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        $this->validateTextMinLength('title', $form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $field_content = $this->mainService->getFieldContentArrayValue(
            $form_state->getValue('field_content')
        );
        if (isset($form['nid']) && is_numeric($form['nid'])) {
            $nid = $form['nid'];
            $node = Node::load($nid);
            if (empty($node)) {
                $this->messenger()->addStatus($this->t('Something went wrong.'));
                ;
            }
            $node->set('title', $form_state->getValue('title'));
            $node->set('field_manga', $form_state->getValue('field_manga'));
            $node->set('field_content', $field_content);
            $node->save();
            $this->messenger()->addStatus($this->t('Updated Chapter.'));
        }
        else {
            $title = $form_state->getValue('title');
            $field_manga = $form_state->getValue('field_manga');
            $new_node = $this->entityTypeManager->getStorage('node')->create(
                [
                'type' => 'chapter',
                'title' => $title,
                'field_manga' => $field_manga,
                'field_content' => $field_content,
                ]
            );
            $new_node->save();
            if ($title !== 'Chapter 1') {
                return;
            }
            $manga = Node::load($field_manga);
            $manga->set(
                'field_read_chap_1', [
                'uri' => $new_node->toUrl()->toString(),
                'title' => $new_node->toUrl()->toString(),
                ]
            );
            $manga->save();
            $this->messenger()->addStatus($this->t('Added new Chapter.'));
        }
    }

}
