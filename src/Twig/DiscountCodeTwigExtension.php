<?php

namespace Drupal\discount_code\Twig;

use Drupal\node\Entity\Node;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Defines a custom Twig extension for discount codes.
 */
class DiscountCodeTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('discount_code_value', [$this, 'getDiscountCodeValue']),
    ];
  }

  /**
   * Retrieves the value of a field from a node by its title.
   *
   * @param string $title
   *   The title of the node.
   * @param string $field_name
   *   The machine name of the field.
   *
   * @return mixed
   *   The value of the field or NULL if not found.
   */
  public function getDiscountCodeValue($title, $field_name) {
    if (!$title) {
      return NULL;
    }

    // Load nodes with the given title.
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'title' => $title,
      'type' => 'discount_code', // Filter by content type 'discount_code'
    ]);

    // If a node is found, return the field value.
    if (!empty($nodes)) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = reset($nodes);
      if ($node->hasField($field_name)) {
        return $node->get($field_name)->value; // Adjust this if the field is a complex field.
      }
    }

    return NULL; // Return NULL if no node or field found.
  }

}
