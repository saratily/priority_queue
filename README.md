# Priority Queue
  
Provides a customized Priority Queue class that can be used to process higher priority 
items before lower priority items. (where 0 is the highest priority and 5 is the least priority)
 
 * This is a Drupal 8 based implementation, which provides a page, with the following information:
 ** Average processing time
 ** Count of un-processed tasks
 ** Next highest priority job
 ** List of un-processed task
 
 * It also let users to add submitters, processors and tasks and store this information in the database. 

 * When user added a job, it will aded to the priority queue. There is a link available on this page to 'process tasks'.
 
 Note: processing job can be done by cron jobs, schedule to be triggered every minute. I haven't implemented cron job in this project because that requires drush and adding ultimate cron contrib module, which is substantial amount of work.   

## Requirements 

* LAMP stack

## Installation

 * Clone this repository and install drupal site.
 * After site installation, login to the site using 'Site login' button (you will be logged in as user 1, super admin user)
 * Go to Extend (/admin/modules), and enable 'GluMobile Priority Queue' module

## How it works

 Go to /task/list and you can 
 * Add submitter
 * Add processors
 * Add few task
 * Process task list, which will assign task to available processor
 
 ## Implementation Details
 
  In addition to Drupal 8.7.8 standard project, this project uses:
  * 'priority_queue' contrib module, defined under /module/contrib folder, and
  * 'gm_priority_queue' custom module, defined under /module/custom folder
  
  _