SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bing
-- ----------------------------
DROP TABLE IF EXISTS `bing`;
CREATE TABLE `bing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片地址',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片名称',
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片配文',
  `day` int(11) unsigned NOT NULL COMMENT '图片日期',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for threeSixty
-- ----------------------------
DROP TABLE IF EXISTS `threeSixty`;
CREATE TABLE `threeSixty` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `img_1600_900` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_1440_900` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_1366_768` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_1280_800` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_1280_1024` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_1024_768` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_thumb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb_id` int(11) unsigned NOT NULL,
  `class_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
