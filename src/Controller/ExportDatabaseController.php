<?php

namespace Drupal\export_database\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for export database routes.
 */
class ExportDatabaseController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
