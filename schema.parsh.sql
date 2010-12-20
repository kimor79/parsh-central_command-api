CREATE TABLE IF NOT EXISTS `jobs` (
 `job_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `real_user` VARCHAR(255) NOT NULL DEFAULT '',
 `run_from_node` VARCHAR(255) NOT NULL DEFAULT '',
 `run_as_user` VARCHAR(255) NOT NULL DEFAULT '',
 `start_time` INT UNSIGNED NOT NULL DEFAULT 0,
 `finish_time` INT UNSIGNED NOT NULL DEFAULT 0,
 `parallel` INT UNSIGNED NOT NULL DEFAULT 1,
 `command` LONGTEXT NOT NULL,
 `files` LONGTEXT NOT NULL,
 `argv` LONGTEXT NOT NULL,
 PRIMARY KEY (`job_id`),
 INDEX (`real_user`, `run_as_user`),
 INDEX (`run_as_user`),
 INDEX (`start_time`, `finish_time`),
 INDEX (`finish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `job_tokens` (
 `job_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
 `token` VARCHAR(255) NOT NULL DEFAULT '',
 UNIQUE KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `nodes` (
 `job_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
 `node` VARCHAR(255) NOT NULL DEFAULT '',
 `start_time` INT UNSIGNED NOT NULL DEFAULT 0,
 `finish_time` INT UNSIGNED NOT NULL DEFAULT 0,
 `stdout` LONGTEXT NOT NULL,
 `stderr` LONGTEXT NOT NULL,
 `exit_status` VARCHAR(20) NOT NULL DEFAULT '',
 `error_message` LONGTEXT NOT NULL,
 INDEX (`job_id`, `node`),
 INDEX (`node`),
 INDEX (`start_time`, `finish_time`),
 INDEX (`finish_time`),
 INDEX (`exit_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
