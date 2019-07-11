<?php

namespace Drupal\an_related_articles;

use Drupal\Core\Database\Connection;

/**
 * Provides a service that provides realted artilces.
 */
class RelatedArticleService {

  /**
   * The Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new RelatedArticleService object.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
  }

  /**
   * To get related articles.
   */
  public function getArticlesData($node) {

    $category_id = 0;

    // Get node id.
    $nid = $node->id();

    // Get node author.
    $node_uid = $node->getOwnerId();

    // Get node category.
    if (!$node->get('field_category')->isEmpty()) {
      $category_id = $node->field_category->target_id;
    }

    // Query to get related articles.
    $query = $this->database->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->leftJoin('node__field_category', 'nc', 'n.nid = nc.entity_id');
    $query->condition('n.status', 0, '<>');
    $query->condition('n.nid', $nid, '<>');
    $query->condition('n.type', 'article');
    $query->condition('nc.bundle', 'article');
    $query->condition('nc.deleted', 0);

    // Sorting criteria.
    // Same category by same author first.
    // Ssame category by different author next.
    // Different category by same author next.
    // Different category by different author next.
    $query->addExpression('CASE 
      WHEN n.uid = ' . $node_uid . ' AND nc.field_category_target_id = ' . $category_id . ' THEN 1 
      WHEN n.uid <> ' . $node_uid . ' AND nc.field_category_target_id = ' . $category_id . ' THEN 2 
      WHEN n.uid = ' . $node_uid . ' AND nc.field_category_target_id <> ' . $category_id . ' THEN 3 
      WHEN n.uid <> ' . $node_uid . ' AND nc.field_category_target_id <> ' . $category_id . ' THEN 4 
      ELSE 6 END', 'art_order');
    $query->orderBy('art_order', 'ASC');
    $query->range(0, 5);
    $results = $query->execute()->fetchAll();

    $articles = [];
    foreach ($results as $key => $result) {
      $articles[$key]['nid'] = $result->nid;
      $articles[$key]['title'] = $result->title;
    }

    return $articles;
  }

}
