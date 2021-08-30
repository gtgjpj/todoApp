create table `project`(
  `project_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_name` varchar(30),
  `project_status` int(1)
);

create table `task`(
  `task_id` int(11) AUTO_INCREMENT NOT NULL PRIMARY KEY,
  `project_id` int(11),
  `task_value` varchar(30),
  `completetion_date` date,
  `task_status` int(1)
);