CREATE TABLE IF NOT EXISTS `mc_dailymotion_config` (
    `id_dm` SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `apikey_dm` VARCHAR(150) NOT NULL,
    `apisecret_dm` VARCHAR(150) NOT NULL,
    `username_dm` VARCHAR(150) NOT NULL,
    `password_dm` VARCHAR(50) NOT NULL,
    `date_register` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_dm`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_dailymotion_item` (
    `id_dm_item` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_type` VARCHAR(50) NOT NULL DEFAULT 'product',
    `item_id` INT(11) UNSIGNED NOT NULL,
    `name_pdn` VARCHAR(150) NULL,
    `video_id_pdn` VARCHAR(64) NULL,
    `private_id` VARCHAR(64) NULL,
    `visibility_pdm` ENUM('public','draft','private') DEFAULT 'private',
    `thumbnail_360_url` VARCHAR(180) NULL,
    `thumbnail_720_url` VARCHAR(180) NULL,
    `order_pdn` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `date_register` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_dm_item`),
    KEY `idx_item` (`item_type`, `item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;