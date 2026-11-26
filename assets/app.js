// All DOM updates use textContent / createElement (never innerHTML), so server data cannot inject markup.
(function () {
  // Confirm before destructive actions (CSP forbids inline JS, so it lives here).
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) { e.preventDefault(); } });
  });

  // 1) Autocomplete search
  var q = document.getElementById('q'), box = document.getElementById('suggestions'), timer = null;
  if (q && box) {
    q.addEventListener('input', function () {
      clearTimeout(timer);
      var term = q.value.trim();
      if (term.length < 2) { box.hidden = true; return; }
      timer = setTimeout(function () {
        fetch('ajax/suggest.php?q=' + encodeURIComponent(term))
          .then(function (r) { return r.json(); })
          .then(function (items) {
            box.textContent = '';
            items.forEach(function (it) {
              var li = document.createElement('li');
              li.textContent = it.label + '  (' + it.kind + ')';
              li.addEventListener('mousedown', function () { q.value = it.label; box.hidden = true; q.form.submit(); });
              box.appendChild(li);
            });
            box.hidden = items.length === 0;
          }).catch(function () { box.hidden = true; });
      }, 250);
    });
    q.addEventListener('blur', function () { setTimeout(function () { box.hidden = true; }, 150); });
  }

  // 2) Live ISBN validation / duplicate check on the add/edit form
  var isbn = document.getElementById('isbn'), status = document.getElementById('isbn-status');
  if (isbn && status) {
    var idEl = document.getElementById('book-id'), t2 = null;
    isbn.addEventListener('input', function () {
      clearTimeout(t2);
      var v = isbn.value.trim();
      if (v === '') { status.textContent = ''; return; }
      t2 = setTimeout(function () {
        fetch('ajax/check_isbn.php?isbn=' + encodeURIComponent(v) + '&id=' + encodeURIComponent(idEl ? idEl.value : 0))
          .then(function (r) { return r.json(); })
          .then(function (d) {
            status.className = 'hint ' + (d.valid && d.available ? 'good' : 'bad');
            status.textContent = !d.valid ? 'Invalid ISBN format.' : (d.available ? 'ISBN is available.' : 'This ISBN is already in the catalogue.');
          });
      }, 300);
    });
  }
})();
