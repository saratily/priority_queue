<?php

declare(strict_types = 1);

namespace Drupal\gm_priority_queue\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\gm_priority_queue\Queue\TaskQueue;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to display next available tasks in queue.
 *
 * @package Drupal\gm_priority_queue\Controller
 */
class TaskController extends ControllerBase {

  /**
   * The database object.
   *
   * @var object
   */
  protected $database;

  /**
   * TaskQueue instance.
   *
   * @var \Drupal\gm_priority_queue\Queue\TaskQueue
   */
  protected $queue;

  /**
   * TaskController constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\gm_priority_queue\Queue\TaskQueue $queue
   *   Provides TaskQueue instance.
   */
  public function __construct(Connection $database, TaskQueue $queue) {
    $this->database = $database;
    $this->queue = $queue;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('queue.gm_priority_queue')
    );
  }

  /**
   * Defines the page content to display the next available task with highest priority.
   *
   * @return array
   *   Render array with the page content.
   */
  public function list() : array {

    $build = [];

    $header = [
      $this->t('Item ID'),
      $this->t('Submitter_id'),
      $this->t('Command'),
      $this->t('Priority'),
      $this->t('Status'),
      $this->t('View'),
      $this->t('Edit'),
    ];
    $next_item = $this->queue->getNextTask();
    $items = $this->queue->getTaskList();

    $next_row = $this->displayRows($next_item);
    $rows = $this->displayRows($items);

    $processor = "<div>" . Link::fromTextAndUrl('PROCESS NEXT TASK',
        Url::fromRoute('gm_priority_queue.process_task'))->toString() .
      "</div>";

    $task_link = "<div>" . Link::fromTextAndUrl('ADD TASK',
        Url::fromRoute('gm_priority_queue.task'))->toString() .
      "</div>";

    $submitter_link = "<div>" . Link::fromTextAndUrl('ADD SUBMITTER',
        Url::fromRoute('gm_priority_queue.add_submitter'))->toString() .
      "</div>";

    $processor_link = "<div>" . Link::fromTextAndUrl('ADD PROCESSOR',
        Url::fromRoute('gm_priority_queue.add_processor'))->toString() .
      "</div>";

    $avg_time = $this->getAverageTime();
    $build['intro'] = [
      '#type' => 'markup',
      '#prefix' => '<p>' . $processor. $task_link . $submitter_link . $processor_link,
      '#suffix' => '</p>',
      '#markup' => $this->t(
        'The following table lists the @count items waiting to be processed.<br />' .
        'The average processing time is: @avg_time seconds',
        ['@count' => count($rows),
          '@avg_time' => $avg_time]
      ),
    ];

    $build['next_item_header'] = [
      '#type' => 'markup',
      '#prefix' => '<h1>',
      '#suffix' => '</h1>',
      '#markup' => $this->t(
        'Next Available task.'),
    ];

    $build['next_item_header']['next_item'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $next_row,
      '#empty' => $this->t('No pending task found.'),
    ];

    $build['items_header'] = [
      '#type' => 'markup',
      '#prefix' => '<h1>',
      '#suffix' => '</h1>',
      '#markup' => $this->t(
        'Task List.'),
    ];

    $build['items_header']['items'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No pending task found.'),
    ];
    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;


  }

  /**
   * Defines the page content to display task with id = $id
   *
   * @param int $id
   *   Task ID
   *
   * @return array
   *   Render task in page content.
   */
  public function getTask(int $id) : array {

    $back_link = "<div>" . Link::fromTextAndUrl('<<< BACK',
        Url::fromRoute('gm_priority_queue.list_task'))->toString() .
      "</div>";

    $edit_url = "/task/" . $id;
    $edit_link = "<div>" . Link::fromTextAndUrl('EDIT',
        Url::fromUri('base:' . $edit_url))->toString() . "</div>";

    $item = $this->queue->getTask($id);
    /** @var \Drupal\gm_priority_queue\Queue\TaskQueueData $data */
    $data = unserialize($item->data);
    $sid = $data->getSubmitterId();

    return [
      '#type' => 'markup',
      '#markup' => $this->t("$back_link<br />$edit_link <br />
      Item ID: " . $item->item_id . "<br />" .
        "Submitter ID:" . $this->getSubmitter($sid) . " ($sid)<br />" .
        "Command: " . $data->getCommand() ."<br />" .
        "Priority: " . $item->priority ."<br />" .
        "Status: " . $data->getStatus() . "<br />" .
        "Start Time: " . $data->getStartTime() . "<br />" .
        "End Time: " . $data->getEndTime() . "<br />" .
        "Processing Time: " . $data->getProcessingTime()
      ),
    ];

  }


  /**
   * Defines a controller to call processor service.
   * @return array
   */
  public function processTask() {
    /** @var \Drupal\gm_priority_queue\TaskQueueProcessor $processor*/
    $processor = \Drupal::service('gm_priority_queue.processor');
    $processor->processQueue();

    return [];

  }

  /**
   * Returns Submitter name.
   *
   * @param int $id
   *   Submitter ID
   *
   * @return string
   */
  private function getSubmitter(int $id) : string {
    $query = $this->database->select('submitter', 's');
    $query->fields('s');
    $query->condition('id', $id);
    $result = $query->execute()->fetchAllAssoc('id');

    return isset($result[$id]->name) ? $result[$id]->name : '';

  }


  /**
   * Returns average processing time.
   *
   * @return float|int
   */
  private function getAverageTime() {
    $query = $this->database->select('queue_priority', 'q');
    $query->fields('q');
    $or_condition = $query->orConditionGroup();
    $or_condition->condition('expire', time(), '>=');
    $or_condition->condition('expire', 0, '!=');
    $query->condition($or_condition);
    $query->orderBy('priority','ASC');
    $results = $query->execute()->fetchAllAssoc('item_id');

    $sum = 0;
    $count = 0;

    foreach ($results as $item) {

      /** @var \Drupal\gm_priority_queue\Queue\TaskQueueData $data */
      $data = unserialize($item->data);
      $sum += $data->getProcessingTime();
      $count++;
    }

    return $sum/$count;

  }

  /**
   * @param array $items
   *
   * @return array
   */
  private function displayRows(array $items) {

    $rows = [];

    foreach ($items as $item) {

      /** @var \Drupal\gm_priority_queue\Queue\TaskQueueData $data */
      $data = unserialize($item->data);
      $item_id = $item->item_id;
      $submitter_id = $data->getSubmitterId();
      $view_url = "/task/view/" . $item_id;
      $view_link = Link::fromTextAndUrl('VIEW', Url::fromUri('base:' . $view_url));
      $edit_url = "/task/" . $item_id;
      $edit_link = Link::fromTextAndUrl('EDIT', Url::fromUri('base:' . $edit_url));

      $rows[] = [
        $item_id,
        $this->getSubmitter($submitter_id) . " ($submitter_id)",
        $data->getCommand(),
        $item->priority,
        $data->getStatus(),
        $view_link,
        $edit_link,
      ];

    }
    return $rows;

  }
}
