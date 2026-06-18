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

	function markProprietarySolutions() {
		var needle = 'Treasury Analytics';
		var badge = '<span class="bfs-proprietary-badge">Solution propri&eacute;taire BFS</span>';
		var selectors = '#project-selector option, .projects-selector option, #category_id option, select[name="category_id"] option';

		document.querySelectorAll(selectors).forEach(function (opt) {
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

	function hideMantisFooter() {
		document.querySelectorAll('.footer .footer-content > .col-md-6').forEach(function (col) {
			if (!col.classList.contains('bfs-portal-footer')) {
				col.style.display = 'none';
			}
		});
		var logo = document.getElementById('powered-by-mantisbt-logo');
		if (logo && logo.parentElement) {
			logo.parentElement.style.display = 'none';
		}
	}

	function hideClientAdminControls() {
		if (!document.body.classList.contains('bfs-client-user')) {
			return;
		}

		document.querySelectorAll('a[href*="manage_user_create_page"]').forEach(function (el) {
			el.style.display = 'none';
		});

		document.querySelectorAll('#projects-list a').forEach(function (link) {
			var href = link.getAttribute('href') || '';
			var text = (link.textContent || '').toLowerCase();
			if (href.indexOf('project_id=0') !== -1 || text.indexOf('tous les') !== -1 || text.indexOf('all projects') !== -1) {
				link.closest('li') && (link.closest('li').style.display = 'none');
			}
		});
	}

	onReady(function () {
		document.documentElement.classList.add('bfs-portal');
		document.body.classList.add('bfs-portal');
		hideMantisFooter();
		hideClientAdminControls();

		if (document.body.classList.contains('login-layout')) {
			var container = document.querySelector('.login-container');
			if (!container) {
				return;
			}

			var welcome = document.createElement('div');
			welcome.className = 'bfs-login-welcome';
			welcome.innerHTML =
				'<h1 class="bfs-login-welcome__title">Portail Support BFS</h1>' +
				'<p class="bfs-login-welcome__lead">Espace s&eacute;curis&eacute; par client : vos tickets ne sont visibles que par votre organisation et l\'&eacute;quipe BFS.</p>' +
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

		markProprietarySolutions();
	});
})();
