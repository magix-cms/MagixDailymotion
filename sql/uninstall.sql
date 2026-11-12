-- Désactivation des contraintes pour éviter les blocages éventuels
SET FOREIGN_KEY_CHECKS = 0;

-- Suppression des tables du plugin
DROP TABLE IF EXISTS `mc_dailymotion_item`;
DROP TABLE IF EXISTS `mc_dailymotion_config`;

-- Réactivation des contraintes
SET FOREIGN_KEY_CHECKS = 1;