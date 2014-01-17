CREATE TABLE `pickles` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `field1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field4` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field5` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;