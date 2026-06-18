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
		'select_project_button'  => 'Choisir un client',
		'projects_link'          => 'Clients',
		'projects_title'         => 'Clients',
		'projects_title_label'   => 'Clients',
		'project_name'           => 'Nom du client',
		'create_new_project_link'=> 'Créer un nouveau client',
		'all_projects'           => 'Tous les clients',
		'assigned_projects'      => 'Clients assignés',
		'assigned_projects_label'=> 'Clients assignés',
		'unassigned_projects_label' => 'Clients non assignés',
		'category'               => 'Solution / type de demande',
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
