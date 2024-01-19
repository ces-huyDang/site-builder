<?php

namespace Drupal\main\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Contains all the method to handle request of the site.
 *
 * @category Service
 *
 * @package Custom
 */
class MainService {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor for EntityTypeManagerInterface.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Create a new array contains all needed Terms information.
   *
   * @param array $terms
   *   Terms from db.
   *
   * @return array
   *   Information of Terms.
   */
  public function getTermsInfo($terms) {
    $terms_list = [];
    foreach ($terms as $term) {
      $term_info = [];
      $tid = $term->id();
      $term_name = $term->getName();
      $term_info['tid'] = $tid;
      $term_info['term_name'] = $term_name;
      array_push($terms_list, $term_info);
    }
    return $terms_list;
  }

  /**
   * Get all terms of a taxonomy by vocabulary machine name.
   *
   * @param string $vocabulary_machine_name
   *   The vocabulary machine name.
   *
   * @return array
   *   An array of term info.
   */
  public function getTaxonomyTerms($vocabulary_machine_name) {
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->accessCheck(TRUE);
    $query->condition('vid', $vocabulary_machine_name);
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    return $this->getTermsInfo($terms);
  }

  /**
   * Get key-value array of a taxonomy by vocabulary machine name.
   *
   * @param string $vocabulary_machine_name
   *   The vocabulary machine name.
   *
   * @return array
   *   A key value array of term info.
   */
  public function getTaxonomyKeyValue($vocabulary_machine_name) {
    $categories = $this->getTaxonomyTerms($vocabulary_machine_name);
    $names_list = [];
    foreach ($categories as $category) {
      $names_list[$category['tid']] = $category['term_name'];
    }
    return $names_list;
  }

  /**
   * Retrieves the list of manga.
   *
   * @return array List of manga info.
   */
  public function getMangaList() {
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => 'manga_overview']);
  }

  /**
   * Get key-value id-anme array of a manga list.
   *
   * @return array
   *   A key value array of manga info.
   */
  public function getMangaListKeyValue() {
    $manga_list = $this->getMangaList();
    $key_value_list = [];
    foreach ($manga_list as $manga) {
      $key_value_list[$manga->id()] = $manga->title->value;
    }
    return $key_value_list;
  }

  /**
   * Get the taxonomy id from the given node field.
   *
   * @param mixed $node_field
   *   The node field to extract the taxonomy id from.
   *
   * @return mixed The taxonomy id extracted from the node field.
   */
  public function getTaxonomyIdFromNodeField($node_field) {
    $tids = [];
    if (!empty($node_field)) {
      foreach ($node_field as $field) {
        array_push($tids, $field['target_id']);
      }
      return $tids;
    }
  }

  /**
   * Convert array to string to get form field content default value.
   *
   * @param array $field_content
   *   The field content of content type Chapter.
   *
   * @return string Value for field content.
   */
  public function getFieldContentStringtValue($field_content) {
    $images = [];
    foreach ($field_content as $image) {
      array_push($images, $image['target_id']);
    }
    return implode(',', $images);
  }

  /**
   * Convert string to node's field content array type.
   *
   * @param string $tring_value
   *   The input string value to be conveted.
   *
   * @return array The array of Node field type.
   */
  public function getFieldContentArrayValue($tring_value) {
    $node_field = [];
    $image_ids = explode(',', $tring_value);
    foreach ($image_ids as $image_id) {
      $node_field[] = ['target_id' => $image_id];
    }
    return $node_field;
  }

}
