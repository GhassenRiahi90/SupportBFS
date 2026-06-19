-- Support BFS — réinitialisation base MantisBT (préfixe mantis_)
-- Exécuter dans phpMyAdmin sur la base choisie (ex. bfstn1_mantis).
-- NE PAS exécuter sur la base WordPress sans vérifier le préfixe.

SET FOREIGN_KEY_CHECKS = 0;

-- Génère les DROP pour toutes les tables mantis_* de la base active
SET @drops = (
	SELECT GROUP_CONCAT(CONCAT('DROP TABLE IF EXISTS `', table_name, '`') SEPARATOR '; ')
	FROM information_schema.tables
	WHERE table_schema = DATABASE()
	AND table_name LIKE 'mantis\\_%'
);

SET @sql = IFNULL(@drops, 'SELECT "Aucune table mantis_* trouvée" AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
