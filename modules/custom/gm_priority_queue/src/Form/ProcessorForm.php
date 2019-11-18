<?php

namespace Drupal\gm_priority_queue\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to submit a new processor.
 */
class ProcessorForm extends FormBase {

  const TABLE = 'processor';

  /**
   * The database object.
   *
   * @var object
   */
  protected $database;

  /**
   * Constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gm_add_processor_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Processor Name'),
      '#required' => TRUE,
      '#default_value' => '',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    try {
      $field = $form_state->getValues();
      $name = $field['name'];

      $this->database->insert(self::TABLE)
        ->fields([
          'name' => $name,
        ])
        ->execute();

      drupal_set_message("Processor data successfully saved");
    }
    catch (\Exception $e) {
      watchdog_exception('processor', $e, 'Error trying to insert processor info.');
    }
  }
}
