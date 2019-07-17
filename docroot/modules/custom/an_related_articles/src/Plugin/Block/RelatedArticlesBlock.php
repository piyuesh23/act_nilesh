<?php

namespace Drupal\an_related_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\an_related_articles\RelatedArticleService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Related Articles' Block.
 *
 * @Block(
 *   id = "an_related_articles_block",
 *   admin_label = @Translation("Related Articles"),
 *   category = @Translation("Related Articles"),
 * )
 */
class RelatedArticlesBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The related article service.
   *
   * @var \Drupal\an_related_articles\RelatedArticleService
   */
  protected $articleService;

  /**
   * RelatedArticlesBlock constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Form\RouteMatchInterface $route_match
   *   Route match.
   * @param \Drupal\an_related_articles\RelatedArticleService $article_service
   *   Article servce.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, RelatedArticleService $article_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->articleService = $article_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('an_related_articles.related_article_services')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $articles = [];

    if (!empty($this->routeMatch)) {
      $node = $this->routeMatch->getParameter('node');
    }

    // Check if node object is instance of node iterface or not.
    if (isset($node) && $node instanceof NodeInterface) {

      // Get related nodes through services.
      $articles = $this->articleService->getArticlesData($node);
    }

    // Build block content.
    return [
      '#theme' => 'an_related_articles_template',
      '#articles' => $articles,
      '#cache' => [
        'tags' => ['node_list'],
        'contexts' => ['url.path'],
      ],
    ];
  }

}
