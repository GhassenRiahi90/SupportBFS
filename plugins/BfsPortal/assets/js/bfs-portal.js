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

	onReady(function () {
		document.documentElement.classList.add('bfs-portal');

		if (!document.body.classList.contains('login-layout')) {
			return;
		}

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
	});
})();
