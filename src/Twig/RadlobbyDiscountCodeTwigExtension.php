<?php

namespace Drupal\radlobby_discount_code\Twig;

use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Query\QueryInterface;
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
   * @param int $bundesland
   *   Check if the discount code is valid for the selected bundesland (group id).
   *
   * @return int|null
   *   The Node ID of a valid discount code or null.
   */
  public function getDiscountCode(string $title, int $created, int|null $bundesland) {
    // convert 'created' to RFC8601 value.
    // If field_zeitraum is a field with date only use 'Y-m-d', otherwise 'Y-m-d\TH:i:s'.
    $timestamp = date('Y-m-d', $created);

    // Create query for nodes.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('title', $title) // Discount Code
      ->condition('type', 'discount_code') // Filter by content type 'discount_code'
      ->condition('status', true) // Only published content
      ->condition('field_zeitraum.value', $timestamp, '<=') // check if timestamp is between field_zeitraum
      ->condition('field_zeitraum.end_value', $timestamp, '>=') // see above
      ->condition('field_bundeslaender', $bundesland) // Check bundesland
    ;

    // Execute the query and get the result.
    $nids = $query->execute();

    // Return first found node id.
    if (sizeof($nids)) {
      return array_values($nids)[0];
    }

    return NULL; // Return NULL if no node or field found.
  }

  /**
   * Retrieves the value of a field from a node by its title.
   *
   * @param int|null $id
   *   The id of the discount code node.
   * @param string $field_name
   *   The machine name of the field.
   *
   * @return mixed
   *   The value of the field or NULL if not found.
   */
  public function getDiscountCodeValue($id, string $field_name) {
    if (!$id) {
      return NULL;
    }

    // Load nodes with the given title.
    $node = Node::load($id);

    // If a node is found, return the field value.
    if (!empty($node)) {
      return $node->get($field_name)->getValue();
    }

    return NULL; // Return NULL if no node or field found.
  }

}
