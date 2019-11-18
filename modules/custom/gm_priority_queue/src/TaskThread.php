<?php


namespace Drupal\gm_priority_queue;

use Drupal\comment\Plugin\views\sort\Thread;
use Drupal\Core\Database\Connection;
use Drupal\gm_priority_queue\Queue\TaskQueueData;

class TaskThread extends Thread{

  /**
   * The database object.
   *
   * @var object
   */
  protected $database;

  /**
   * The processor id.
   *
   * @var int
   */
  protected $pid;

  /**
   * The task id.
   *
   * @var int
   */
  protected $taskId;

  /**
   * TaskQueue instance.
   *
   * @var \Drupal\gm_priority_queue\Queue\TaskQueueData
   */
  protected $data;

  /**
   * TaskController constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param int $pid
   *   Processor ID.
   * @param int $task_id
   *   Task ID.
   * .@param \Drupal\gm_priority_queue\Queue\TaskQueueData $data
   *    Task form the queue.
   */
  public function __construct(Connection $database, int $pid, int $task_id, TaskQueueData $data) {
    $this->database = $database;
    $this->pid = $pid;
    $this->taskId = $task_id;
    $this->data = $data;
  }

  public function run() {

    // Mark processor as unavailable.
    $query = $this->database->update('processor');
    $query->fields([
      'status' => 0,
    ]);
    $query->condition('id', $this->pid);
    $query->execute();

    $this->data->setStartTime(time());

    // Execute command
    $cmd = $this->data->getCommand();
    exec($cmd);

    // Release processor
    $query = $this->database->update('processor');
    $query->fields([
      'status' => 1,
    ]);
    $query->condition('id', $this->pid);
    $query->execute();

    $this->data->setStartTime(time());

    $this->data->setProcessingTime($this->data->getEndTime() - $this->data->getStartTime());

    // Release processor
    $query = $this->database->update('queue_priority');
    $query->fields([
      'data' => $this->data,
    ]);
    $query->condition('item_id', $this->taskId);
  }

}
