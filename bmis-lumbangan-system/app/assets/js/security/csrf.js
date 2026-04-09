/**
 * Global CSRF bootstrapper.
 *
 * Responsibilities:
 * - Reads CSRF config from meta tags or window fallback values.
 * - Attaches CSRF header to same-origin state-changing fetch/XHR/jQuery requests.
 * - Injects hidden CSRF fields into same-origin HTML forms using POST/PUT/PATCH/DELETE.
 */
(function (window, document) {
  'use strict';

  var csrfToken = '';
  var csrfHeaderName = 'X-CSRF-Token';
  var csrfFieldName = 'csrf_token';

  function getMetaContent(name) {
    var node = document.querySelector('meta[name="' + name + '"]');
    return node ? (node.getAttribute('content') || '') : '';
  }

  function refreshCsrfConfig() {
    csrfToken = getMetaContent('csrf-token') || window.CSRF_TOKEN || csrfToken || '';
    csrfHeaderName = getMetaContent('csrf-header') || window.CSRF_HEADER || csrfHeaderName;
    csrfFieldName = getMetaContent('csrf-field') || window.CSRF_FIELD || csrfFieldName;

    window.CSRF_TOKEN = csrfToken;
    window.CSRF_HEADER = csrfHeaderName;
    window.CSRF_FIELD = csrfFieldName;
  }

  function isStateChangingMethod(method) {
    var m = (method || 'GET').toUpperCase();
    return m === 'POST' || m === 'PUT' || m === 'PATCH' || m === 'DELETE';
  }

  function isSameOrigin(targetUrl) {
    if (!targetUrl || targetUrl === '' || targetUrl.charAt(0) === '#') {
      return true;
    }

    try {
      var resolved = new URL(targetUrl, window.location.href);
      return resolved.origin === window.location.origin;
    } catch (e) {
      return true;
    }
  }

  function shouldAttachToken(method, url) {
    if (!csrfToken) {
      return false;
    }

    if (!isStateChangingMethod(method)) {
      return false;
    }

    return isSameOrigin(url);
  }

  function ensureFormToken(form) {
    if (!form || form.tagName !== 'FORM') {
      return;
    }

    if (form.hasAttribute('data-csrf-skip')) {
      return;
    }

    refreshCsrfConfig();
    if (!csrfToken) {
      return;
    }

    var method = (form.getAttribute('method') || 'GET').toUpperCase();
    if (!isStateChangingMethod(method)) {
      return;
    }

    var action = form.getAttribute('action') || window.location.href;
    if (!isSameOrigin(action)) {
      return;
    }

    var selector = 'input[name="' + csrfFieldName.replace(/"/g, '\\"') + '"]';
    var input = form.querySelector(selector);

    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = csrfFieldName;
      form.appendChild(input);
    }

    input.value = csrfToken;
  }

  function hydrateForms(root) {
    var scope = root || document;
    var forms = scope.querySelectorAll ? scope.querySelectorAll('form') : [];
    for (var i = 0; i < forms.length; i += 1) {
      ensureFormToken(forms[i]);
    }
  }

  function installFetchHook() {
    if (!window.fetch || window.__csrfFetchPatched) {
      return;
    }

    window.__csrfFetchPatched = true;
    var originalFetch = window.fetch.bind(window);

    window.fetch = function (input, init) {
      refreshCsrfConfig();

      var url = '';
      var method = 'GET';

      if (typeof input === 'string') {
        url = input;
      } else if (input && typeof input.url === 'string') {
        url = input.url;
        if (input.method) {
          method = input.method;
        }
      }

      init = init || {};
      if (init.method) {
        method = init.method;
      }

      if (shouldAttachToken(method, url)) {
        var headers = new Headers(init.headers || (input && input.headers) || undefined);
        if (!headers.has(csrfHeaderName)) {
          headers.set(csrfHeaderName, csrfToken);
        }
        init.headers = headers;
      }

      return originalFetch(input, init);
    };
  }

  function installXhrHook() {
    if (!window.XMLHttpRequest || window.__csrfXhrPatched) {
      return;
    }

    window.__csrfXhrPatched = true;

    var originalOpen = window.XMLHttpRequest.prototype.open;
    var originalSend = window.XMLHttpRequest.prototype.send;

    window.XMLHttpRequest.prototype.open = function (method, url) {
      this.__csrfMethod = method || 'GET';
      this.__csrfUrl = url || window.location.href;
      return originalOpen.apply(this, arguments);
    };

    window.XMLHttpRequest.prototype.send = function () {
      refreshCsrfConfig();
      if (shouldAttachToken(this.__csrfMethod, this.__csrfUrl)) {
        try {
          this.setRequestHeader(csrfHeaderName, csrfToken);
        } catch (e) {
          // Ignore header set failures to keep compatibility with existing calls.
        }
      }
      return originalSend.apply(this, arguments);
    };
  }

  function installJqueryHook() {
    var $ = window.jQuery;
    if (!$ || !$.ajaxSetup || window.__csrfJqueryPatched) {
      return;
    }

    window.__csrfJqueryPatched = true;

    $.ajaxSetup({
      beforeSend: function (xhr, settings) {
        refreshCsrfConfig();

        var method = 'GET';
        var targetUrl = window.location.href;

        if (settings) {
          method = (settings.type || settings.method || method).toUpperCase();
          targetUrl = settings.url || targetUrl;
        }

        if (shouldAttachToken(method, targetUrl)) {
          xhr.setRequestHeader(csrfHeaderName, csrfToken);
        }
      }
    });
  }

  function observeNewForms() {
    if (!window.MutationObserver) {
      return;
    }

    var observer = new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i += 1) {
        var addedNodes = mutations[i].addedNodes || [];
        for (var j = 0; j < addedNodes.length; j += 1) {
          var node = addedNodes[j];
          if (!node || node.nodeType !== 1) {
            continue;
          }

          if (node.tagName === 'FORM') {
            ensureFormToken(node);
          } else if (node.querySelectorAll) {
            hydrateForms(node);
          }
        }
      }
    });

    observer.observe(document.documentElement || document.body, {
      childList: true,
      subtree: true
    });
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
      return;
    }
    fn();
  }

  function bootstrap() {
    refreshCsrfConfig();
    installFetchHook();
    installXhrHook();
    installJqueryHook();

    document.addEventListener('submit', function (event) {
      ensureFormToken(event.target);
    }, true);

    onReady(function () {
      refreshCsrfConfig();
      installJqueryHook();
      hydrateForms(document);
      observeNewForms();
    });

    window.AppCsrf = {
      refresh: refreshCsrfConfig,
      getToken: function () { return csrfToken; },
      getHeaderName: function () { return csrfHeaderName; },
      getFieldName: function () { return csrfFieldName; },
      ensureFormToken: ensureFormToken
    };
  }

  bootstrap();
})(window, document);
