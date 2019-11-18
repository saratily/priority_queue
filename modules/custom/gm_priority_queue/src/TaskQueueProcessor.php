<?php

declare(strict_types = 1);

namespace Drupal\gm_priority_queue;

use Drupal\Core\Database\Connection;
use Drupal\priority_queue\Queue\PriorityQueueFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TaskQueueProcessor {

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
   * TaskQueue instance.
   *
   * @var \Drupal\gm_priority_queue\Queue\TaskQueue
   */
  protected $taskQueue;

  /**
   * TaskController constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\priority_queue\queue\PriorityQueueFactory $queue
   *   The queue for tasks.
   */
  public function __construct(Connection $database,
                              PriorityQueueFactory $queue) {
    $this->database = $database;
    $this->queue = $queue->get('task_queue');
  }
  /**
   * Processes items in the TaskQueue.
   */
  public function processQueue() {

    $available_processor = $this->getProcessor();

    foreach ($available_processor as $pid => $value) {


      $task = $this->queue->claimItem();
      /** @var \Drupal\gm_priority_queue\Queue\TaskQueueData $data */
      $data = $task->data;

      // No task in the queue.
      if ($task == FALSE) {
        return;
      }

      $thread = new TaskThread($this->database, $pid, $data);
      $thread->run();
    }

    $back_link =\Drupal::url('gm_priority_queue.list_task');

    $response = new RedirectResponse($back_link);
    $response->send();

    drupal_set_message("Some of the tasks have been processed successfully.");

  }

  private function getProcessor() {

    $query = $this->database->select('processor', 'p');
    $query->fields('p', ['id']);
    $query->condition('status', 1);
    $result = $query->execute()->fetchAllAssoc('id');

    return isset($result) ? $result : [];
  }

}
