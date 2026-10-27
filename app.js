(() => {
  const storageKey = 'suara-siswa-pages-v1';
  const categories = ['Fasilitas Sekolah', 'Kegiatan Ekstrakurikuler', 'Akademik', 'Kedisiplinan', 'Masukan Umum'];
  const pages = [...document.querySelectorAll('[data-page]')];
  const nav = document.querySelector('[data-nav]');
  const menuButton = document.querySelector('[data-menu-toggle]');
  const toast = document.querySelector('[data-toast]');
  const filterForm = document.querySelector('[data-filter-form]');
  const themeToggle = document.querySelector('[data-theme-toggle]');
  let selectedCategory = '';
  let notificationTimer;

  const savedTheme = localStorage.getItem('suara-siswa-theme');
  if (savedTheme === 'dark') document.documentElement.dataset.theme = 'dark';
  themeToggle.addEventListener('click', () => {
    const darkMode = document.documentElement.dataset.theme !== 'dark';
    document.documentElement.dataset.theme = darkMode ? 'dark' : 'light';
    localStorage.setItem('suara-siswa-theme', darkMode ? 'dark' : 'light');
    themeToggle.setAttribute('aria-label', darkMode ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
  });

  const readStore = () => {
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
      return {
        aspirations: Array.isArray(saved.aspirations) ? saved.aspirations : [],
        votes: Array.isArray(saved.votes) ? saved.votes : []
      };
    } catch {
      return { aspirations: [], votes: [] };
    }
  };

  const saveStore = (store) => {
    try {
      localStorage.setItem(storageKey, JSON.stringify(store));
      return true;
    } catch {
      showToast('Browser tidak dapat menyimpan data. Periksa ruang penyimpanan atau mode privat.');
      return false;
    }
  };

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(notificationTimer);
    notificationTimer = window.setTimeout(() => toast.classList.remove('show'), 3200);
  }

  function navigate(page, updateHash = true) {
    const target = pages.some((item) => item.dataset.page === page) ? page : 'home';
    pages.forEach((item) => { item.hidden = item.dataset.page !== target; });
    document.querySelectorAll('[data-navigate]').forEach((button) => {
      button.classList.toggle('active', button.dataset.navigate === target && button.matches('.nav-link'));
    });
    nav.classList.remove('nav-open');
    menuButton.setAttribute('aria-expanded', 'false');
    if (updateHash) history.replaceState(null, '', `#${target === 'home' ? 'beranda' : target}`);
    if (target === 'aspirations') renderAspirations();
    if (target === 'home') renderStats();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function formatDate(value) {
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(value));
  }

  function renderStats() {
    const aspirations = readStore().aspirations;
    document.querySelector('[data-stat="total"]').textContent = aspirations.length;
    document.querySelector('[data-stat="done"]').textContent = aspirations.filter((item) => item.status === 'selesai').length;
  }

  function renderAspirations() {
    const store = readStore();
    const formData = new FormData(filterForm);
    const query = String(formData.get('q') || '').trim().toLocaleLowerCase('id');
    const category = String(formData.get('category') || '');
    const sort = String(formData.get('sort') || 'newest');
    const results = store.aspirations.filter((item) => {
      const textMatches = `${item.title} ${item.body}`.toLocaleLowerCase('id').includes(query);
      return textMatches && (!category || item.category === category);
    });
    results.sort((first, second) => sort === 'votes'
      ? second.votes - first.votes || new Date(second.createdAt) - new Date(first.createdAt)
      : new Date(second.createdAt) - new Date(first.createdAt));

    document.querySelector('[data-results-count]').textContent = `${results.length} aspirasi`;
    const list = document.querySelector('[data-aspiration-list]');
    if (!results.length) {
      list.innerHTML = `<div class="empty-state"><span class="empty-state-mark">✳</span><h2>${query || category ? 'Belum ada hasil yang cocok.' : 'Belum ada aspirasi di browser ini.'}</h2><p>${query || category ? 'Coba kata kunci atau kategori lain.' : 'Aspirasi yang kamu kirim dari perangkat ini akan muncul di sini.'}</p><button class="button button-dark" type="button" data-navigate="submit">Tulis aspirasi <span>↗</span></button></div>`;
      return;
    }

    list.innerHTML = results.map((item) => {
      const id = escapeHtml(item.id);
      const voted = store.votes.includes(item.id);
      const excerpt = item.body.length > 180 ? `${item.body.slice(0, 177)}...` : item.body;
      return `<article class="asp-card"><div class="asp-card-top"><span class="tag">${escapeHtml(item.category)}</span><span class="status">${escapeHtml(item.statusLabel || 'Diterima')}</span></div><h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(excerpt)}</p><div class="asp-card-foot"><span>${escapeHtml(formatDate(item.createdAt))} · ${item.anonymous ? 'Anonim' : escapeHtml(item.name || 'Siswa')}</span><button class="vote-button${voted ? ' voted' : ''}" type="button" data-vote="${id}" ${voted ? 'aria-pressed="true"' : 'aria-pressed="false"'}>${voted ? '♥ Didukung' : '♡ Dukung'} · ${item.votes}</button></div></article>`;
    }).join('');
  }

  function showTracking(code) {
    const normalized = code.trim().toUpperCase();
    const item = readStore().aspirations.find((aspiration) => aspiration.code === normalized);
    const result = document.querySelector('[data-track-result]');
    if (!item) {
      result.innerHTML = normalized ? '<div class="track-error">Kode tidak ditemukan di penyimpanan browser ini. Pastikan kode benar dan gunakan perangkat yang sama.</div>' : '';
      return;
    }

    const steps = ['Diterima', 'Ditinjau', 'Tindak lanjut', 'Selesai'];
    result.innerHTML = `<article class="track-card"><div class="asp-card-top"><span class="tag">${escapeHtml(item.category)}</span><span class="status">${escapeHtml(item.statusLabel || 'Diterima')}</span></div><h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(item.body)}</p><div class="track-timeline">${steps.map((step, index) => `<div class="timeline-item${index === 0 ? ' done' : ''}"><span class="timeline-dot">${index === 0 ? '✓' : index + 1}</span><span>${step}</span></div>`).join('')}</div><div class="track-meta">KODE ${escapeHtml(item.code)} · DIKIRIM ${escapeHtml(formatDate(item.createdAt).toUpperCase())}</div></article>`;
  }

  document.addEventListener('click', (event) => {
    const navigationButton = event.target.closest('[data-navigate]');
    if (navigationButton) {
      navigate(navigationButton.dataset.navigate);
      if (navigationButton.dataset.navigate === 'track') {
        document.querySelector('[data-track-form] [name="code"]').focus();
      }
      return;
    }
    const categoryButton = event.target.closest('[data-category]');
    if (categoryButton) {
      selectedCategory = categoryButton.dataset.category;
      navigate('submit');
      document.querySelector('[data-submission-form] [name="category"]').value = selectedCategory;
      return;
    }
    const voteButton = event.target.closest('[data-vote]');
    if (voteButton) {
      const store = readStore();
      const aspiration = store.aspirations.find((item) => item.id === voteButton.dataset.vote);
      if (!aspiration || store.votes.includes(aspiration.id)) return;
      aspiration.votes += 1;
      store.votes.push(aspiration.id);
      if (saveStore(store)) {
        renderAspirations();
        showToast('Dukunganmu sudah dicatat di browser ini.');
      }
    }
  });

  menuButton.addEventListener('click', () => {
    const open = nav.classList.toggle('nav-open');
    menuButton.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      nav.classList.remove('nav-open');
      menuButton.setAttribute('aria-expanded', 'false');
    }
  });

  filterForm.addEventListener('submit', (event) => {
    event.preventDefault();
    renderAspirations();
  });
  filterForm.addEventListener('input', (event) => {
    if (event.target.matches('[type="search"]')) renderAspirations();
  });
  document.querySelector('[data-clear-filters]').addEventListener('click', () => {
    filterForm.reset();
    renderAspirations();
  });

  document.querySelectorAll('[data-count]').forEach((counter) => {
    const field = document.querySelector(`[name="${counter.dataset.count}"]`);
    const update = () => { counter.textContent = `${field.value.length} / ${field.maxLength}`; };
    field.addEventListener('input', update);
    update();
  });

  const submissionForm = document.querySelector('[data-submission-form]');
  const anonymousToggle = submissionForm.elements.anonymous;
  const nameField = document.querySelector('[data-name-field]');
  const nameInput = submissionForm.elements.name;
  const syncNameField = () => {
    nameField.hidden = anonymousToggle.checked;
    nameInput.required = !anonymousToggle.checked;
  };
  anonymousToggle.addEventListener('change', syncNameField);
  syncNameField();
  submissionForm.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!submissionForm.reportValidity()) return;
    const formData = new FormData(submissionForm);
    const store = readStore();
    const code = `SS-${crypto.getRandomValues(new Uint32Array(1))[0].toString(16).toUpperCase().padStart(8, '0')}`;
    const item = {
      id: crypto.randomUUID(),
      code,
      category: String(formData.get('category')),
      title: String(formData.get('title')).trim(),
      body: String(formData.get('body')).trim(),
      anonymous: anonymousToggle.checked,
      name: anonymousToggle.checked ? '' : String(formData.get('name') || '').trim(),
      status: 'diterima',
      statusLabel: 'Diterima',
      votes: 0,
      createdAt: new Date().toISOString()
    };
    store.aspirations.push(item);
    if (!saveStore(store)) return;

    const confirmation = document.querySelector('[data-confirmation]');
    confirmation.innerHTML = `<h2>Aspirasi tersimpan di browser ini.</h2><p>Simpan kode berikut untuk melacak aspirasi di perangkat yang sama.</p><div class="code-display"><strong>${escapeHtml(code)}</strong><button class="copy-button" type="button" data-copy-code="${escapeHtml(code)}">Salin kode</button></div><br><button class="button button-dark" type="button" data-track-code="${escapeHtml(code)}">Lihat status <span>→</span></button>`;
    confirmation.hidden = false;
    confirmation.scrollIntoView({ behavior: 'smooth', block: 'center' });
    submissionForm.reset();
    syncNameField();
    document.querySelectorAll('[data-count]').forEach((counter) => { counter.textContent = `0 / ${document.querySelector(`[name="${counter.dataset.count}"]`).maxLength}`; });
    renderStats();
    showToast('Aspirasi berhasil disimpan pada perangkat ini.');
  });

  const trackForm = document.querySelector('[data-track-form]');
  trackForm.addEventListener('submit', (event) => {
    event.preventDefault();
    showTracking(trackForm.elements.code.value);
  });
  document.addEventListener('click', async (event) => {
    const copyButton = event.target.closest('[data-copy-code]');
    if (copyButton) {
      try {
        await navigator.clipboard.writeText(copyButton.dataset.copyCode);
        showToast('Kode pelacakan disalin.');
      } catch {
        showToast(`Kode pelacakan: ${copyButton.dataset.copyCode}`);
      }
      return;
    }
    const trackButton = event.target.closest('[data-track-code]');
    if (trackButton) {
      navigate('track');
      document.querySelector('[data-track-form] [name="code"]').value = trackButton.dataset.trackCode;
      showTracking(trackButton.dataset.trackCode);
    }
  });

  const firstPage = location.hash.slice(1);
  navigate(firstPage === 'beranda' || !firstPage ? 'home' : firstPage, false);
  if (selectedCategory && categories.includes(selectedCategory)) {
    document.querySelector('[data-submission-form] [name="category"]').value = selectedCategory;
  }
  renderStats();
})();