create table `project`(
  `project_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_name` varchar(30),
  `project_status` int(1),
  `color` varchar(30)
);

create table `task`(
  `task_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_id` int(11) NOT NULL,
  `task_value` varchar(30),
  `completetion_date` date,
  `task_status` int(1),
  FOREIGN KEY `fk_project_id`(`project_id`) REFERENCES `project`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
);