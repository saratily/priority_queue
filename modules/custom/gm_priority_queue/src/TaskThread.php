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
   * .@param \Drupal\gm_priority_queue\Queue\TaskQueueData $data
   *    Task form the queue.
   */
  public function __construct(Connection $database, int $pid, TaskQueueData $data) {
    $this->database = $database;
    $this->pid = $pid;
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

  }

}
