<?php

declare(strict_types = 1);

namespace Drupal\gm_priority_queue\Queue;

/**
 * Class TaskQueueData
 *
 * @package Drupal\gm_priority_queue\Queue
 */
class TaskQueueData {

  /**
   * Id of the submitter.
   *
   * @var int
   */
  private $submitterId;

  /**
   * Command to be executed in this job.
   *
   * @var string
   */
  private $command;

  /**
   * Id of the processor, processing that job.
   *
   * @var int
   */
  private $processorId;

  /**
   * The start time in ("Y-m-d H:i:s") format.
   *
   * @var string
   */
  private $startTime;

  /**
   * The end time in ("Y-m-d H:i:s") format.
   *
   * @var int
   */
  private $endTime;

  /**
   * The processing time in seconds.
   *
   * @var int
   */
  private $processingTime;

  /**
   * The status of the job.
   * 0-open, 1-processing, 2-done.
   *
   * @var int
   */
  private $status;

  /**
   * FinancialAlertQueueData constructor.
   *
   * @param int $submitter_id
   *   Id of the submitter.
   * @param string $command
   *   Command to be executed.
   *
   */
  public function __construct(int $submitter_id = 0 , string $command = '') {
    $this->submitterId = $submitter_id;
    $this->command = $command;
    $this->processorId = 0;
    $this->status = 0;
  }

  /**
   * Get the submitter ID.
   *
   * @return int
   */
  public function getSubmitterId(){
    return $this->submitterId;
  }

  /**
   * Set ID of the submitter.
   *
   * @param int $id
   *   Processor ID
   */
  public function setSubmitterId(int $id) {
    $this->submitterId = $id;
  }

  /**
   * Get the command.
   *
   * @return string
   */
  public function getCommand(){
    return $this->command;
  }

  /**
   * Set the command.
   *
   * @param string $cmd
   *   Processor ID
   */
  public function setCommand(string $cmd) {
    $this->command = $cmd;
  }

  /**
   * Get the submitter ID.
   *
   * @return int
   */
  public function getProcessorId() {
    return $this->processorId;
  }

  /**
   * Set Id of the the processor.
   *
   * @param int $id
   *   Processor ID
   */
  public function setProcessorId(int $id) {
    $this->processorId = $id;
  }
  /**
   * Get timestamp when job start processing.
   *
   * @return string
   */
  public function getStartTime(){
    return $this->startTime;
  }

  /**
   * Timestamp when job was started.
   *
   * @param string $timestamp
   *   Timestamp in ("Y-m-d H:i:s") format.
   */
  public function setStartTime(string $timestamp) {
    $this->startTime = $timestamp;
  }

  /**
   * Get timestamp when job was done.
   *
   * @return string
   */
  public function getEndTime(){
    return $this->endTime;
  }

  /**
   * Timestamp when job was finished.
   *
   * @param string $timestamp
   *   Timestamp in ("Y-m-d H:i:s") format.
   */
  public function setEndTime(string $timestamp) {
    $this->endTime = $timestamp;
  }

  /**
   * Numberof seconds took to finish a job.
   * @return int
   */
  public function getProcessingTime() {
    return $this->processingTime;
  }

  /**
   * Set the processing time of the job.
   *
   * @param int $processing_time
   *   The processing time in seconds.
   *
   */
  public function setProcessingTime(int $processing_time){
    $this->processingTime = $processing_time;
  }

  /**
   * Get the current status of the job.
   *
   * @return int
   */
  public function getStatus(){
    return $this->status;
  }

  /**
   * Set the current status of the job.
   *
   * @param int $status
   *   0-open, 1-processing, 2-done.
   */
  public function setStatus(int $status){
    $this->status = $status;
  }

}
