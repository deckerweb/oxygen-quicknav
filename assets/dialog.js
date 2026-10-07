/* Native dialog preserves Escape, focus containment, and focus return. */
(() => {
  let opener = null;
  document.querySelectorAll('[data-oqn-open]').forEach(button => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.oqnOpen);
      if (!dialog || typeof dialog.showModal !== 'function') return;
      opener = button;
      dialog.showModal();
      dialog.querySelector('[data-oqn-close]').focus();
    });
  });
  document.querySelectorAll('#oqn-history').forEach(dialog => {
    dialog.querySelector('[data-oqn-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { if (opener) opener.focus(); });
  });
})();
