<?php

namespace Drupal\radlobby_discount_code\Twig;

use Drupal\node\Entity\Node;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Defines a custom Twig extension for discount codes.
 */
class RadlobbyDiscountCodeTwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('radlobby_discount_code_get', [$this, 'getDiscountCode']),
      new TwigFunction('radlobby_discount_code_value', [$this, 'getDiscountCodeValue']),
    ];
  }

  /**
   * Retrieves the Node ID of a valid discount code for the specified input.
   *
   * @param string $title
   *   The title of the discount code (which matches the title of the Node).
   * @param int $created
   *   The timestamp when the form was created (so that discount code is stil valid, when the form
   *   gets modified.
   *
   * @return int|null
   *   The Node ID of a valid discount code or null.
   */
  public function getDiscountCode(string $title, int $created) {
    // convert 'created' to RFC8601 value.
    // If field_zeitraum is a field with date only use 'Y-m-d', otherwise 'Y-m-d\TH:i:s'.
    $timestamp = date('Y-m-d', $created);

    // Load nodes with the given title.
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'title' => $title,
      'type' => 'discount_code', // Filter by content type 'discount_code'
      'status' => true, // Only published content
    ]);

    // search for a node where the timestamp is between start/end of field_zeitraum (which is a datetime range field)
    foreach ($nodes as $node) {
      $values = $node->get('field_zeitraum')->getValue();
      foreach ($values as $value) {
        if ($value['value'] <= $timestamp && $timestamp <= $value['end_value']) {
          return $node->id();
        }
      }
    }

    return NULL; // Return NULL if no node or field found.
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
        return $node->get($field_name)->getValue();
      }
    }

    return NULL; // Return NULL if no node or field found.
  }

}
