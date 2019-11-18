<?php

declare(strict_types = 1);

namespace Drupal\gm_priority_queue\Queue;

use Drupal\Core\Database\Connection;
use Drupal\gm_priority_queue\Queue\TaskQueueData;
use Drupal\priority_queue\queue\PriorityQueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TaskQueue {

  /**
   * Name of the queue.
   */
  const QUEUE_NAME = 'task_queue';

  /**
   * The database object.
   *
   * @var object
   */
  protected $database;

  /**
   * The queue.
   *
   * @var \Drupal\priority_queue\Queue\PriorityQueue
   */
  protected $queue;

  /**
   * Constructor.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\priority_queue\queue\PriorityQueueFactory $queue_factory
   *   The queue for tasks
   */
  public function __construct(Connection $database,
                              PriorityQueueFactory $queue_factory) {
    $this->database = $database;
    $this->queue = $queue_factory->get(self::QUEUE_NAME);
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
   * Defines the page content to display the next available task with highest priority.
   *
   * @return array
   *   Render array with the page content.
   */
  public function getTaskList() : array {
    if (!$this->database->schema()->tableExists('queue_priority')) {
      return [];
    }
    $query = $this->database->select('queue_priority', 'q');
    $query->fields('q');
    $query->condition('name', static::QUEUE_NAME);
    $or_condition = $query->orConditionGroup();
    $or_condition->condition('expire', time(), '>=');
    $or_condition->condition('expire', 0);
    $query->condition($or_condition);
    $query->orderBy('priority','ASC');
    $results = $query->execute()->fetchAllAssoc('item_id');

    return isset($results) ? $results : [];

  }

  /**
   * Defines the page content to display the next available task with highest priority.
   *
   * @param int $id
   *   Task ID.
   *
   * @return \stdClass|Null
   *   Render array with the page content.
   */
  public function getTask(int $id) : \stdClass {
    if (!$this->database->schema()->tableExists('queue_priority')) {
      return NULL;
    }
    $query = $this->database->select('queue_priority', 'q');
    $query->fields('q');
    $query->condition('name', static::QUEUE_NAME);
    $query->condition('item_id', $id);
    $result = $query->execute()->fetchAllAssoc('item_id');
    return isset($result[$id]) ? $result[$id] : NULL;

  }

  /**
   * Defines the page content to display the next available task with highest priority.
   *
   * @return array
   *   Render array with the page content.
   */
  public function getNextTask() : array {
    if (!$this->database->schema()->tableExists('queue_priority')) {
      return NULL;
    }
    $query = $this->database->select('queue_priority', 'q');
    $query->fields('q');
    $query->condition('name', static::QUEUE_NAME);
    $or_condition = $query->orConditionGroup();
    $or_condition->condition('expire', time(), '>=');
    $or_condition->condition('expire', 0);
    $query->condition($or_condition);
    $query->orderBy('priority','ASC');
    $results = $query->execute();

    return [(object)$results->fetchAssoc()];



  }

}
