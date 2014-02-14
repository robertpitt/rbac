DROP TABLE IF EXISTS `rbac_roles`;
CREATE TABLE `rbac_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `left` (`left`),
  KEY `right` (`right`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `rbac_user_roles`;
CREATE TABLE `rbac_user_roles` (
  `account_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`role_id`),
  KEY `fk_rbac_user_roles_1` (`role_id`),
  CONSTRAINT `fk_rbac_user_roles_1` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `rbac_permissions`;
CREATE TABLE `rbac_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `name` char(64) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `left` (`left`),
  KEY `right` (`right`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `rbac_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rbac_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rbac_role_permissions_1` (`role_id`),
  KEY `fk_rbac_role_permissions_2` (`permission_id`),
  CONSTRAINT `fk_rbac_role_permissions_1` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_rbac_role_permissions_2` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;