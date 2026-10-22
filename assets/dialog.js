/* Enhance real documentation links while retaining native dialog fallbacks. */
(() => {
  if (window.ddwOqnDialogReady) return;
  window.ddwOqnDialogReady = true;
  const prototype = window.HTMLDialogElement && window.HTMLDialogElement.prototype;
  let opener = null;
  const close = dialog => {
    if (prototype && typeof prototype.close === 'function') {
      try { prototype.close.call(dialog); } catch (_) { /* Hide even after a failed native close. */ }
    }
    dialog.removeAttribute('open');
    dialog.hidden = true;
    if (opener && opener.isConnected) opener.focus();
  };
  document.addEventListener('click', event => {
    const link = event.target.closest('[data-oqn-open]');
    if (!link) return;
    const dialog = document.getElementById(link.dataset.oqnOpen);
    if (!dialog || !prototype || typeof prototype.showModal !== 'function') return;
    try {
      dialog.hidden = false;
      prototype.showModal.call(dialog);
      opener = link;
      event.preventDefault();
      dialog.querySelector('[data-oqn-close]').focus();
    } catch (_) { dialog.hidden = true; /* Follow the original document link. */ }
  });
  document.querySelectorAll('#oqn-history').forEach(dialog => {
    dialog.querySelector('form').addEventListener('submit', event => { event.preventDefault(); close(dialog); });
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(dialog); });
    dialog.addEventListener('close', () => { dialog.hidden = true; if (opener && opener.isConnected) opener.focus(); });
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) close(dialog);
    });
  });
})();
