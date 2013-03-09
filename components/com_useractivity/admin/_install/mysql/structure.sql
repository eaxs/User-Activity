CREATE TABLE IF NOT EXISTS `#__user_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `item_id` int(10) unsigned NOT NULL COMMENT 'FK to the user_activity_items table',
  `event_id` smallint(5) unsigned NOT NULL COMMENT 'FK to the user_activity_events table',
  `client_id` tinyint(3) NOT NULL COMMENT 'Location ID. 0 = Site, 1 = Admin',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Record date and time',
  `created_day` smallint(5) NOT NULL COMMENT 'Days since unix epoch. Used to better index records by date',
  `created_by` int(10) unsigned NOT NULL COMMENT 'FK to the users table',
  `delta_time` int(10) unsigned NOT NULL COMMENT 'Minutes past since the last activity of the same type, event and user. Used for grouping.',
  `access` int(10) unsigned NOT NULL COMMENT 'FK to the viewlevels tables',
  `state` tinyint(3) NOT NULL COMMENT 'Record state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
  PRIMARY KEY (`id`),
  KEY `idx_event_by` (`event_id`,`created_by`),
  KEY `idx_client_state` (`client_id`,`state`),
  KEY `idx_day` (`created_day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity';

CREATE TABLE IF NOT EXISTS `#__user_activity_events` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `name` varchar(16) NOT NULL COMMENT 'Event name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity events';

CREATE TABLE IF NOT EXISTS `#__user_activity_items` (
  `asset_id` int(10) unsigned NOT NULL COMMENT 'Primary Key and FK to the assets table',
  `type_id` smallint(5) unsigned NOT NULL COMMENT 'FK to the user_activity_item_types table',
  `xref_id` int(10) unsigned NOT NULL COMMENT 'Cross Reference ID. Plugin controlled',
  `id` int(10) unsigned NOT NULL COMMENT 'The id of the item itself',
  `state` tinyint(3) NOT NULL COMMENT 'Last known item state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
  `title` varchar(255) NOT NULL COMMENT 'Last known item title',
  PRIMARY KEY (`asset_id`),
  KEY `idx_type_id` (`type_id`),
  KEY `idx_xref_id` (`xref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user activity item information';

CREATE TABLE IF NOT EXISTS `#__user_activity_item_types` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `plugin` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'The type and name of the plugin that handles this type',
  `extension` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'The extension name',
  `name` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT 'The item type name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity item type information';