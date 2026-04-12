(function () {
  function getMetaContent(name) {
    var meta = document.querySelector('meta[name="' + name + '"]');
    return meta ? meta.getAttribute('content') || '' : '';
  }

  function getCsrfToken() {
    return getMetaContent('csrf-token');
  }

  function getCsrfHeaderName() {
    return getMetaContent('csrf-header') || 'X-CSRF-Token';
  }

  function isStateChangingMethod(method) {
    var normalized = (method || 'GET').toUpperCase();
    return normalized === 'POST' || normalized === 'PUT' || normalized === 'PATCH' || normalized === 'DELETE';
  }

  function isSameOrigin(targetUrl) {
    if (!targetUrl || targetUrl === '' || targetUrl.charAt(0) === '#') {
      return true;
    }

    try {
      var resolved = new URL(targetUrl, window.location.href);
      return resolved.origin === window.location.origin;
    } catch (error) {
      return true;
    }
  }

  function shouldAttachToken(method, url, token) {
    if (!token) {
      return false;
    }

    if (!isStateChangingMethod(method)) {
      return false;
    }

    return isSameOrigin(url);
  }

  function applyCsrfHeader(headers, token, headerName) {
    if (!token) {
      return headers;
    }

    if (headers instanceof Headers) {
      if (!headers.has(headerName)) {
        headers.set(headerName, token);
      }
      return headers;
    }

    var next = headers && typeof headers === 'object' ? Object.assign({}, headers) : {};
    if (!next[headerName]) {
      next[headerName] = token;
    }
    return next;
  }

  if (!window.__csrfFetchPatched) {
    var originalFetch = window.fetch;
    if (typeof originalFetch === 'function') {
      window.fetch = function (input, init) {
        var token = getCsrfToken();
        var headerName = getCsrfHeaderName();
        var options = init ? Object.assign({}, init) : {};
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

        if (options.method) {
          method = options.method;
        }

        if (shouldAttachToken(method, url, token)) {
          options.headers = applyCsrfHeader(options.headers, token, headerName);
        }

        return originalFetch(input, options);
      };
    }
    window.__csrfFetchPatched = true;
  }

  if (window.jQuery && !window.__csrfJqueryPatched) {
    window.jQuery.ajaxSetup({
      beforeSend: function (xhr, settings) {
        var token = getCsrfToken();
        var headerName = getCsrfHeaderName();
        var method = settings && (settings.type || settings.method) ? (settings.type || settings.method) : 'GET';
        var url = settings && settings.url ? settings.url : window.location.href;

        if (shouldAttachToken(method, url, token)) {
          xhr.setRequestHeader(headerName, token);
        }
      }
    });
    window.__csrfJqueryPatched = true;
  }
})();
