CREATE TABLE `settings` (
  `module` VARCHAR(50) NULL DEFAULT NULL,
  `item` VARCHAR(50) NOT NULL,
  `value` TEXT NULL,
  UNIQUE INDEX `module_item` (`module`, `item`)
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM;