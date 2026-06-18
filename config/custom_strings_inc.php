<?php
/**
 * Support BFS — Vocabulaire portail (français)
 *
 * Format MantisBT : variables $s_* chargées lors de lang_load() pour $g_active_language.
 */

if( !isset( $g_active_language ) || 'french' != $g_active_language ) {
	return;
}

# Terminologie principale
$s_bug                    = 'ticket';
$s_bugs                   = 'tickets';
$s_new_bug                = 'Nouvelle demande';
$s_new_bug_title          = 'Nouvelle demande de support';
$s_new_bug_button         = 'Nouvelle demande';
$s_bugnote                = 'Commentaire';
$s_bug_history            = 'Historique du ticket';
$s_bug_deleted            = 'Demande supprimée';
$s_bug_monitor            = 'Ticket surveillé';
$s_bug_end_monitor        = 'Fin de surveillance du ticket';
$s_bug_relationships      = 'Relations';
$s_view_bugs_link         = 'Mes demandes';
$s_report_bug_link        = 'Nouvelle demande';
$s_view_submitted_bug_link = 'Voir la demande soumise %1$s';
$s_issue_id               = 'Demande n°';
$s_select_project_button  = 'Choisir un client';
$s_projects_link          = 'Clients';
$s_projects_title         = 'Clients';
$s_projects_title_label   = 'Clients';
$s_project_name           = 'Nom du client';
$s_create_new_project_link = 'Créer un nouveau client';
$s_all_projects           = 'Tous les clients';
$s_assigned_projects      = 'Clients assignés';
$s_assigned_projects_label = 'Clients assignés à cet utilisateur';
$s_unassigned_projects_label = 'Clients non assignés à cet utilisateur';
$s_add_user_title          = 'Ajouter un utilisateur au client';
$s_add_user_button         = 'Ajouter l\'utilisateur';
$s_category               = 'Solution / type de demande';
$s_severity               = 'Niveau de criticité';
$s_reporter               = 'Demandeur';
$s_resolution             = 'Résolution';
$s_status                 = 'Statut';
$s_issue_status_percentage = 'Pourcentage des statuts de demande';
$s_summary_link           = 'Tableau de bord';
$s_summary_title          = 'Tableau de bord';
$s_my_view_link           = 'Mon tableau de bord';
$s_mantis_link            = 'Portail Support BFS';
$s_login_title            = 'Connexion au portail support BFS';
$s_bfs_contact_support_link = 'Contact support BFS';
$s_bfs_website_link       = 'Site www.bfs.tn';

# Énumérations métier
$s_status_enum_string   = '10:Nouveau,20:En attente client,30:Pris en charge,40:Confirmé,50:En cours,80:Résolu,90:Fermé';
$s_severity_enum_string = '10:Information,50:Mineur,60:Majeur,80:Bloquant';

# Libellés champs personnalisés
$s_client_company     = 'Nom du client / société';
$s_contract_reference = 'Référence contrat client';
$s_environment        = 'Environnement';
$s_business_urgency   = 'Urgence métier déclarée';

# Notifications email (terminologie demande)
$s_email_notification_title_for_action_bug_submitted = 'La demande de support suivante a été SOUMISE.';
$s_email_notification_title_for_action_bug_assigned = 'La demande de support suivante a été AFFECTÉE.';
$s_email_notification_title_for_action_bug_unassigned = 'La demande de support suivante a été DÉSASSIGNÉE.';
$s_email_notification_title_for_action_bug_reopened = 'La demande de support suivante a été ROUVERTE.';
$s_email_notification_title_for_action_bug_deleted = 'La demande de support suivante a été SUPPRIMÉE.';
$s_email_notification_title_for_action_bug_updated = 'La demande de support suivante a été MISE À JOUR.';
$s_email_notification_title_for_action_bugnote_submitted = 'Un COMMENTAIRE a été ajouté à cette demande.';
$s_email_notification_title_for_status_bug_new = 'La demande suivante est au statut NOUVEAU.';
$s_email_notification_title_for_status_bug_resolved = 'La demande de support suivante a été RÉSOLUE.';
$s_email_notification_title_for_status_bug_closed = 'La demande de support suivante a été FERMÉE.';
