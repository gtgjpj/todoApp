alter table `task` modify `project_id` int(11) not null;
alter table `task` add foreign key `fk_project_id`(`project_id`) references `project`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;  