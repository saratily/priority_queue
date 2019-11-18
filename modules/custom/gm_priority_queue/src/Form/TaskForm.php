<?php

namespace Drupal\gm_priority_queue\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\gm_priority_queue\Queue\TaskQueueData;
use Drupal\priority_queue\Queue\PriorityQueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to submit a new task to the queue..
 */
class TaskForm extends FormBase {

  /**
   * The database object.
   *
   * @var object
   */
  protected $database;

  /**
   * The queue.
   *
   * @var \Drupal\priority_queue\queue\PriorityQueue
   */
  protected $queue;


  /**
   * Constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\priority_queue\queue\PriorityQueueFactory $queue
   *   The queue for tasks.
   */
  public function __construct(Connection $database, PriorityQueueFactory $queue) {
    $this->database = $database;
    $this->queue = $queue->get('task_queue');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('queue.priority_queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gm_task_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id='0') {

    $data = new TaskQueueData();

    if($id != 0) {
      $query = $this->database->select('queue_priority', 'q');
      $query->fields('q');
      $query->condition('item_id', $id);
      $result = $query->execute()->fetchAllAssoc('item_id');

      /** @var \Drupal\gm_priority_queue\Queue\TaskQueueData $data */
      $data = isset($result[$id]->data) ? unserialize($result[$id]->data) : $data;
      $priority = $result[$id]->priority;
    }

    $back_link = "<div>" . Link::fromTextAndUrl('<<< BACK',
        Url::fromRoute('gm_priority_queue.list_task'))->toString() .
      "</div>";

    $form['back'] = [
      '#type' => 'label',
      '#title' => $back_link
    ];

    $form['id']= [
      '#value' => $id,
      '#type' => 'hidden',
    ];

    $form['submitter_id'] = [
      '#title' => t('Submitter'),
      '#type' => 'select',
      '#options' => $this->getSubmitters(),
      '#default_value' => $data->getSubmitterId(),
      '#required' => TRUE,
    ];

    $form['command'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Command'),
      '#default_value' => $data->getCommand(),
      '#required' => TRUE,
    ];

    $form['priority'] = array(
      '#title' => t('priority'),
      '#type' => 'select',
      '#options' => [
        0 => '0',
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        ],
      '#default_value' => isset($priority) ? $priority : 0,
      '#required' => TRUE,
    );

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

    $field = $form_state->getValues();
    $id = $field['id'];
    $submitter_id = $field['submitter_id'];
    $command = $field['command'];
    $priority = $field['priority'];
    $data = new TaskQueueData($submitter_id, $command);

    if(!is_numeric($id)) {
      $this->queue->createItem($data, $priority);
    } else {
      $query = $this->database->update('queue_priority');
      $query->fields([
        'data' => serialize($data),
        'priority' => $priority,
      ]);
//      $query->expression('number_of_attempts', 'number_of_attempts+1');
      $query->condition('item_id', $id);
      $query->execute();
    }
    $form_state->setRedirect('gm_priority_queue.list_task');
    drupal_set_message(" data successfully saved");
  }


  /**
   * List of submitters for the dropdown.
   *
   * @return array
   */
  private function getSubmitters() {
    $options = [];
    $query = $this->database->select('submitter', 's');
    $query->fields('s');
    $result = $query->execute()->fetchAllAssoc('id');

    foreach ($result as $value) {
      $options[$value->id] = $value->name;
    }
    return $options;
  }
}
