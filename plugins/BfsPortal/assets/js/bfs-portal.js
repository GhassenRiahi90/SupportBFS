/**
 * Support BFS — enrichissements UI (login, branding)
 */
(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function markProprietaryProjects() {
		var needle = 'Treasury Analytics';
		var badge = '<span class="bfs-proprietary-badge">Solution propri&eacute;taire BFS</span>';
		document.querySelectorAll('#project-selector option, .projects-selector option').forEach(function (opt) {
			if (opt.textContent.indexOf(needle) !== -1 && opt.textContent.indexOf('Solution propri') === -1) {
				opt.textContent = opt.textContent + ' — Solution propriétaire BFS';
			}
		});
		document.querySelectorAll('#sidebar a, .nav-list a').forEach(function (link) {
			if (link.textContent.indexOf(needle) !== -1 && !link.querySelector('.bfs-proprietary-badge')) {
				link.insertAdjacentHTML('beforeend', badge);
			}
		});
	}

	onReady(function () {
		document.documentElement.classList.add('bfs-portal');

		if (document.body.classList.contains('login-layout')) {
			var container = document.querySelector('.login-container');
			if (!container) {
				return;
			}

			var welcome = document.createElement('div');
			welcome.className = 'bfs-login-welcome';
			welcome.innerHTML =
				'<h1 class="bfs-login-welcome__title">Portail Support BFS</h1>' +
				'<p class="bfs-login-welcome__lead">D&eacute;clarez et suivez vos demandes d\'assistance pour les solutions BFS.</p>' +
				'<ul class="bfs-login-welcome__solutions">' +
				'<li>Sage XRT</li>' +
				'<li>Sage SXA</li>' +
				'<li>BFS Treasury Analytics</li>' +
				'<li>Support g&eacute;n&eacute;ral</li>' +
				'</ul>';

			var logo = container.querySelector('.login-logo');
			if (logo) {
				logo.parentNode.insertBefore(welcome, logo.nextSibling);
			} else {
				container.insertBefore(welcome, container.firstChild);
			}
			return;
		}

		markProprietaryProjects();
	});
})();
