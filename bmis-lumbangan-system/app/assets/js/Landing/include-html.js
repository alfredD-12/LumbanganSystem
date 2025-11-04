// Simple client-side HTML include utility
// Usage: add <div data-include="components/header.html"></div> in your HTML
document.addEventListener('DOMContentLoaded', function () {
  const includes = document.querySelectorAll('[data-include]');
  const promises = Array.from(includes).map(function (el) {
    const path = el.getAttribute('data-include');
    if (!path) return Promise.resolve();
    return fetch(path)
      .then(function (res) {
        if (!res.ok) throw new Error('Failed to fetch ' + path + ' — ' + res.status);
        return res.text();
      })
      .then(function (html) {
        el.innerHTML = html;
      })
      .catch(function (err) {
        console.error('Include failed for', path, err);
      });
  });

  // After all includes have been fetched and injected, dispatch a custom event
  Promise.all(promises).then(function () {
    // small delay to ensure DOM is updated
    setTimeout(function () {
      const ev = new Event('includes:loaded');
      console.log('includes:loaded dispatched');
      document.dispatchEvent(ev);
    }, 0);
  });
});
