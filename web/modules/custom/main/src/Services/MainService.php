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

}
