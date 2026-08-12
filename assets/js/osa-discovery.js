/**
 * OSA discovery layer. Everything here is client-side so LiteSpeed's
 * full-page cache stays byte-identical for every visitor:
 *   1) Reddit-referral banner (session-persistent, dismissible)
 *   2) Recently-viewed strip on product pages (localStorage) — Task 10
 */
(function () {
	'use strict';

	var SS_REDDIT = 'osaReddit';
	var SS_HIDE   = 'osaRedditHide';

	window.gtag = window.gtag || function () {
		(window.dataLayer = window.dataLayer || []).push(arguments);
	};

	/* ---------------- Reddit banner ---------------- */

	function referrerHost() {
		try {
			return new URL(document.referrer).hostname;
		} catch (e) {
			return '';
		}
	}

	function redditSession() {
		try {
			// A reddit UTM on THIS page always marks the session — even if an
			// earlier non-reddit pageview already cached '0'.
			if (/[?&]utm_source=reddit/i.test(location.search)) {
				sessionStorage.setItem(SS_REDDIT, '1');
				return true;
			}
			var cached = sessionStorage.getItem(SS_REDDIT);
			if (cached !== null) {
				return cached === '1';
			}
			var hit = /(^|\.)reddit\.com$/i.test(referrerHost());
			sessionStorage.setItem(SS_REDDIT, hit ? '1' : '0');
			return hit;
		} catch (e) {
			return false;
		}
	}

	function renderBanner() {
		if (!redditSession()) {
			return;
		}
		try {
			if (sessionStorage.getItem(SS_HIDE) === '1') {
				return;
			}
		} catch (e) { /* private mode: banner shows every page, fine */ }

		var bar = document.createElement('div');
		bar.className = 'osa-reddit-banner';
		bar.innerHTML =
			'<span class="osa-reddit-banner__text">Fresh surplus hits the rack every week.</span>' +
			'<a class="osa-reddit-banner__link" href="/shop/?orderby=date">See what just landed →</a>' +
			'<button class="osa-reddit-banner__close" type="button" aria-label="Dismiss">×</button>';

		bar.querySelector('.osa-reddit-banner__close').addEventListener('click', function () {
			try { sessionStorage.setItem(SS_HIDE, '1'); } catch (e) {}
			bar.remove();
		});
		bar.querySelector('.osa-reddit-banner__link').addEventListener('click', function () {
			gtag('event', 'select_promotion', { promotion_id: 'reddit_banner' });
		});

		var header = document.querySelector('header');
		if (header && header.parentNode) {
			header.parentNode.insertBefore(bar, header.nextSibling);
		} else {
			document.body.insertBefore(bar, document.body.firstChild);
		}
		gtag('event', 'view_promotion', { promotion_id: 'reddit_banner' });
	}

	/* ---------------- Recently viewed ---------------- */

	var LS_RECENT = 'osaRecent';

	function esc(s) {
		var d = document.createElement('div');
		d.textContent = s == null ? '' : String(s);
		return d.innerHTML.replace(/"/g, '&quot;');
	}

	function readRecent() {
		try {
			var list = JSON.parse(localStorage.getItem(LS_RECENT) || '[]');
			return Array.isArray(list) ? list : [];
		} catch (e) {
			return [];
		}
	}

	function writeRecent(list) {
		try {
			localStorage.setItem(LS_RECENT, JSON.stringify(list.slice(0, 8)));
		} catch (e) { /* storage full/blocked: skip */ }
	}

	function recentlyViewed() {
		var node = document.getElementById('osa-product-json');
		if (!node) {
			return; // not a product page
		}
		var current = null;
		try { current = JSON.parse(node.textContent); } catch (e) {}

		var previous = readRecent().filter(function (p) {
			return p && p.id && (!current || p.id !== current.id);
		});
		if (current && current.id) {
			writeRecent([current].concat(previous));
		}
		if (previous.length < 2) {
			return;
		}

		var anchor = document.querySelector('.osa-same-floor');
		if (!anchor || !anchor.parentNode) {
			return;
		}

		var html = '<h2 class="osa-recent__title">You Were Looking At</h2><div class="osa-recent__strip">';
		previous.slice(0, 8).forEach(function (p) {
			html += '<a class="osa-recent__card" href="' + esc(p.url) + '"' +
				' data-pid="' + esc(String(p.id)) + '" data-pname="' + esc(p.name) + '">' +
				(p.img ? '<img loading="lazy" src="' + esc(p.img) + '" alt="' + esc(p.name) + '">' : '') +
				'<span class="osa-recent__name">' + esc(p.name) + '</span>' +
				(p.price ? '<span class="osa-recent__price">' + esc(p.price) + '</span>' : '') +
				'</a>';
		});

		var wrap = document.createElement('section');
		wrap.className = 'osa-recent';
		wrap.setAttribute('data-osa-list', 'recently_viewed');
		wrap.innerHTML = html + '</div>';
		anchor.parentNode.insertBefore(wrap, anchor);
	}

	function init() {
		renderBanner();
		recentlyViewed();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
