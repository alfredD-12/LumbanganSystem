(function () {
  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
  }

  function applyCsrfHeader(headers, token) {
    if (!token) return headers;

    if (headers instanceof Headers) {
      if (!headers.has('X-CSRF-Token')) {
        headers.set('X-CSRF-Token', token);
      }
      return headers;
    }

    var next = headers && typeof headers === 'object' ? Object.assign({}, headers) : {};
    if (!next['X-CSRF-Token']) {
      next['X-CSRF-Token'] = token;
    }
    return next;
  }

  if (!window.__csrfFetchPatched) {
    var originalFetch = window.fetch;
    if (typeof originalFetch === 'function') {
      window.fetch = function (input, init) {
        var token = getCsrfToken();
        var options = init ? Object.assign({}, init) : {};
        options.headers = applyCsrfHeader(options.headers, token);
        return originalFetch(input, options);
      };
    }
    window.__csrfFetchPatched = true;
  }

  if (window.jQuery && !window.__csrfJqueryPatched) {
    window.jQuery.ajaxSetup({
      beforeSend: function (xhr) {
        var token = getCsrfToken();
        if (token) {
          xhr.setRequestHeader('X-CSRF-Token', token);
        }
      }
    });
    window.__csrfJqueryPatched = true;
  }
})();
