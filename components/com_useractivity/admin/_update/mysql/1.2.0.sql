ALTER TABLE `#__user_activity` CHANGE `access` `vaccess` INT( 10 ) UNSIGNED NOT NULL COMMENT 'FK to the viewlevels table';
ALTER TABLE `#__user_activity_items` CHANGE `access` `vaccess` INT( 10 ) UNSIGNED NOT NULL COMMENT 'FK to the viewlevels table';
