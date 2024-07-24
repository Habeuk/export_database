<?php

namespace Drupal\export_database\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a export database form.
 */
class ExportDB extends FormBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'export_database_export_d_b';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query = \Drupal::database()->query("SHOW TABLES");
    $tables = $query->fetchAll(\PDO::FETCH_ASSOC);
    $form['tables'] = [
      '#type' => 'details',
      '#open' => false,
      '#title' => 'Liste de tables : ' . count($tables)
    ];
    $links = [];
    foreach ($tables as $table) {
      $links[] = [
        'title' => reset($table),
        'url' => Url::fromUserInput('#')
      ];
    }
    $form['tables']['lists'] = [
      '#theme' => 'links',
      '#links' => $links,
      '#attributes' => [
        'class' => []
      ]
    ];
    
    $form['actions'] = [
      '#type' => 'actions'
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => "Export database"
    ];
    
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t("The message has been sent."));
    $conf = \Drupal::database()->getConnectionOptions();
    if (!empty($conf['database']) && !empty($conf['username']) && !empty($conf['password'])) {
      $database = $conf['database'];
      $username = $conf['username'];
      $password = $conf['password'];
      $path = \DRUPAL_ROOT;
      $command = "mysqldump";
      $cmd = "command -v " . $command;
      $testCmd = $this->excuteCmd($cmd);
      if ($testCmd['return_var']) {
        $command = "mariadb-dump";
        $cmd = "command -v " . $command;
        $testCmd = $this->excuteCmd($cmd);
      }
      if (!$testCmd['return_var']) {
        $cmd = "cd $path && cd ../ && $command -u $username -p$password $database > $database.sql ";
        $result = $this->excuteCmd($cmd);
        if (!$result['return_var']) {
          $this->messenger()->addStatus("La base de donnée a été exporter : $database.sql");
          $cmd = "cd $path && cd ../ && zip -r $database.sql.zip  $database.sql";
          $result = $this->excuteCmd($cmd);
          if (!$result['return_var']) {
            $this->messenger()->addStatus("Le fichier zip de la bd a été creer : $database.sql.zip");
          }
          else {
            $this->messenger()->addError("Le fichier zip de la bd n'a pas été creer : $database.sql.zip");
          }
        }
        else {
          $this->messenger()->addError("Impossible d'exporter la bd : $database.sql");
        }
      }
      else {
        $this->messenger()->addWarning(" aucune commande n'est installé sur le serveur permettant d'exporter la BD, regarder du coté d'adminer");
      }
    }
    else {
      $this->messenger()->addWarning(" Impossible de determiner les informations sur la BD.");
    }
  }
  
  /**
   * Permet d'executé une commande.
   *
   * @param string $cmd
   * @return array
   */
  protected function excuteCmd(string $cmd) {
    ob_start();
    $return_var = '';
    $output = '';
    exec($cmd . " 2>&1", $output, $return_var);
    $result = ob_get_contents();
    ob_end_clean();
    $debug = [
      'output' => $output,
      'return_var' => $return_var,
      'result' => $result,
      'script' => $cmd
    ];
    return $debug;
  }
}
