<?php
/**
 * Support BFS — Vocabulaire portail (français)
 */

$s_custom_messages = array(
	'french' => array(
		# Terminologie principale
		'bug'                    => 'ticket',
		'bugs'                   => 'tickets',
		'new_bug'                => 'Nouvelle demande',
		'new_bug_title'          => 'Nouvelle demande de support',
		'new_bug_button'         => 'Nouvelle demande',
		'bugnote'                => 'Commentaire',
		'bug_history'            => 'Historique du ticket',
		'bug_deleted'            => 'Demande supprimée',
		'bug_monitor'            => 'Ticket surveillé',
		'bug_end_monitor'        => 'Fin de surveillance du ticket',
		'bug_relationships'      => 'Relations',
		'view_bugs_link'         => 'Afficher les demandes',
		'view_submitted_bug_link'=> 'Voir la demande soumise %1$s',
		'select_project_button'  => 'Choisir une solution',
		'projects_link'          => 'Solutions',
		'projects_title'         => 'Solutions',
		'projects_title_label'   => 'Solutions',
		'project_name'           => 'Nom de la solution',
		'create_new_project_link'=> 'Créer une nouvelle solution',
		'all_projects'           => 'Toutes les solutions',
		'assigned_projects'      => 'Solutions assignées',
		'assigned_projects_label'=> 'Solutions assignées',
		'unassigned_projects_label' => 'Solutions non assignées',
		'category'               => 'Type de demande',
		'severity'               => 'Niveau de criticité',
		'reporter'               => 'Demandeur',
		'resolution'             => 'Résolution',
		'status'                 => 'Statut',
		'issue_status_percentage'=> 'Pourcentage des statuts de demande',
		'summary_link'           => 'Tableau de bord',
		'summary_title'          => 'Tableau de bord',
		'mantis_link'            => 'Portail Support BFS',
		'login_title'            => 'Connexion au portail support BFS',

		# Énumérations métier (libellés affichés — clés config dans BfsSeed)
		'status_enum_string' => '10:Nouveau,20:En attente client,30:Pris en charge,40:Confirmé,50:En cours,80:Résolu,90:Fermé',
		'severity_enum_string' => '10:Information,50:Mineur,60:Majeur,80:Bloquant',

		# Libellés champs personnalisés
		'client_company'     => 'Nom du client / société',
		'contract_reference' => 'Référence contrat client',
		'environment'        => 'Environnement',
		'business_urgency'   => 'Urgence métier déclarée',
	),
);
