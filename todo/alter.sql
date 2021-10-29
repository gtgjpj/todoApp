alter table `task` modify `project_id` int(11) not null;
alter table `task` add foreign key `fk_project_id`(`project_id`) references `project`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;  

-- 2021/09/29 ver1.5.2以降
alter table `project` add `color` varchar(30);

-- 2021/10/29 ver1.7.0以降
create table `skin`(
  `key` varchar(30) NOT NULL,
  `row_index` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY(`key`, `row_index`)
);