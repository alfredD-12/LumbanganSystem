function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const img = document.createElement('img');
    img.className = 'img-thumbnail';
    img.style.maxWidth = '200px';
    img.src = URL.createObjectURL(file);
    preview.appendChild(img);
}

document.addEventListener('DOMContentLoaded', function(){
    // Open announcement modal when clicking a card (but not buttons/links inside)
    function initCardClicks(container){
        const cards = (container || document).querySelectorAll('.announcement-card');
        cards.forEach(card => {
            // avoid attaching multiple times
            if (card.dataset.clickInit) return;
            card.dataset.clickInit = '1';
            card.addEventListener('click', function(e){
                // ignore clicks from buttons/links inside the card
                if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form')) return;
                const title = card.dataset.title || '';
                const message = card.dataset.message || '';
                const image = card.dataset.image || '';
                const author = card.dataset.author || '';
                const audience = card.dataset.audience || '';
                const created = card.dataset.created || '';

                const modalTitle = document.getElementById('modalTitle');
                const modalMeta = document.getElementById('modalMeta');
                const modalMessage = document.getElementById('modalMessage');
                const modalImage = document.getElementById('modalImage');
                const modalImageWrap = document.getElementById('modalImageWrap');

                modalTitle.textContent = title;
                const type = card.dataset.type || '';
                const typeLabel = type ? type.charAt(0).toUpperCase() + type.slice(1) : '';
                const audienceLabel = audience ? audience.charAt(0).toUpperCase() + audience.slice(1) : '';
                modalMeta.textContent = `${typeLabel ? (typeLabel + ' • ') : ''}${audienceLabel} • ${author} • ${created}`;
                modalMessage.innerHTML = (message || '').replace(/\n/g, '<br>');

                if (image) {
                    // If the stored data-image already contains a path (or is a data URL), use it.
                    // Otherwise prefer the actual <img> on the card (which the browser already resolved),
                    // and fall back to the legacy relative path.
                    let imgSrc = '';
                    if (image.indexOf('/') !== -1 || image.indexOf('data:') === 0) {
                        imgSrc = image;
                    } else {
                        const cardImg = card.querySelector('.card-image');
                        if (cardImg && cardImg.src) {
                            imgSrc = cardImg.src;
                        } else {
                            const base = (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '../app/';
                            imgSrc = base + 'uploads/announcementimage/' + image;
                        }
                    }
                    modalImage.src = imgSrc;
                    modalImageWrap.style.display = 'block';
                } else {
                    modalImage.src = '';
                    modalImageWrap.style.display = 'none';
                }

                const annModalEl = document.getElementById('announcementModal');
                const annModal = new bootstrap.Modal(annModalEl);
                annModal.show();
            });
        });
    }

    // Detect overflow in card message previews and add a class to the card when present
    function detectOverflow(container) {
        const root = container || document;
        const cards = root.querySelectorAll('.announcement-card');
        cards.forEach(card => {
            const txt = card.querySelector('.card-text');
            if (!txt) return;
            // remove any previous class first
            if (txt.scrollHeight > txt.clientHeight + 1) {
                card.classList.add('has-overflow');
            } else {
                card.classList.remove('has-overflow');
            }
        });
    }

    // initialize existing cards
    initCardClicks(document);
    // detect overflow for initial cards
    detectOverflow(document);

    // AJAX submit for new announcement form: insert new card on success
    const annForm = document.getElementById('announcementForm');
    if (annForm) {
        annForm.addEventListener('submit', function(e){
            // Prevent double submissions: ignore if already processing
            if (annForm.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            const action = annForm.querySelector('input[name="action"]').value;

            // Basic client-side validation: require title and message
            const titleEl = annForm.querySelector('input[name="title"]');
            const msgEl = annForm.querySelector('textarea[name="message"]');
            const title = titleEl ? titleEl.value.trim() : '';
            const message = msgEl ? msgEl.value.trim() : '';
            if (!title || !message) {
                e.preventDefault();
                alert('Please fill in both Title and Message before continuing.');
                if (titleEl && !title) titleEl.focus();
                else if (msgEl) msgEl.focus();
                return;
            }

            const confirmMsg = annForm.dataset.confirm || (action === 'create' ? 'Are you sure you want to create this announcement?' : 'Are you sure you want to update this announcement?');

            if (action !== 'create') {
                // update: validate then confirm, allow default submit if confirmed
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return;
                }
                // mark as submitting and allow normal submission to proceed
                annForm.dataset.submitting = '1';
                return;
            }

            // create: intercept and submit via AJAX after confirmation
            e.preventDefault();
            annForm.dataset.submitting = '1';
            if (!confirm(confirmMsg)) return;

            const btn = annForm.querySelector('button[type="submit"]');
            const originalHTML = btn ? btn.innerHTML : null;
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...'; }

            const formData = new FormData(annForm);
            formData.set('ajax', '1');

            fetch(window.location.pathname + window.location.search, {
                method: 'POST',
                body: formData
            }).then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    if (data.reload) {
                        window.location.reload();
                        return;
                    }
                } else {
                    console.error('Create failed', data);
                    alert('Failed to create announcement.');
                }
            }).catch(err => {
                console.error(err);
                // On error, show message instead of automatically re-submitting
                alert('Network error while creating announcement. Please try again.');
                // Do not auto-submit to avoid duplicate inserts
            }).finally(() => {
                if (btn) { btn.disabled = false; if (originalHTML) btn.innerHTML = originalHTML; }
                // clear submitting flag
                delete annForm.dataset.submitting;
            });
        });
    }

    // When modal image clicked, open image modal
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        modalImage.addEventListener('click', function(){
            if (!this.src) return;
            const imgModal = document.getElementById('imageModal');
            const imgEl = document.getElementById('imageModalImg');
            imgEl.src = this.src;
            const bm = new bootstrap.Modal(imgModal);
            bm.show();
        });
    }

    // View more button (AJAX load more)
    const viewMoreBtn = document.getElementById('viewMoreBtn');
    const viewLessBtn = document.getElementById('viewLessBtn');
    const PAGE_SIZE = 9;
    const loadedChunks = []; // stack of row groups appended via "View more"
    if (viewMoreBtn && !viewMoreBtn.dataset.initialNextOffset) {
        viewMoreBtn.dataset.initialNextOffset = viewMoreBtn.dataset.nextOffset || String(PAGE_SIZE);
    }

    if (viewMoreBtn) {
            if (!viewMoreBtn.dataset.originalLabel) {
            viewMoreBtn.dataset.originalLabel = viewMoreBtn.innerHTML;
        }
            if (!viewMoreBtn.dataset.hiddenWhenExhausted) {
                viewMoreBtn.dataset.hiddenWhenExhausted = 'false';
            }

        viewMoreBtn.addEventListener('click', function(){
            const next = parseInt(this.dataset.nextOffset || viewMoreBtn.dataset.initialNextOffset || String(PAGE_SIZE), 10);
            const params = new URLSearchParams(window.location.search);
            params.set('offset', next);
            params.set('limit', String(PAGE_SIZE)); // Fixed limit for 3x3 grid
            params.set('ajax', '1');

            // Disable button during loading
            viewMoreBtn.disabled = true;
            viewMoreBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Loading...';

            fetch(window.location.pathname + '?' + params.toString())
                .then(r => {
                    if (!r.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return r.json();
                })
                .then(data => {
                    // Accept responses that either include HTML columns or indicate 0 results
                    if (data && data.html && data.html.trim() !== '') {
                        // target the announcements grid container
                        const gridContainer = document.querySelector('.announcements-grid');
                        const viewMoreWrap = document.getElementById('viewMoreWrap');
                        
                        if (!gridContainer) {
                            console.error('Grid container not found');
                            return;
                        }

                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = data.html.trim();

                        // For view more, we need to add new rows
                        // The server returns individual columns, we need to group them into rows
                        const newColumns = [];
                        let child = wrapper.firstElementChild;
                        while (child) {
                            if (child.classList && child.classList.contains('col-md-4')) {
                                newColumns.push(child);
                            }
                            child = child.nextElementSibling;
                        }

                        if (newColumns.length === 0) {
                            console.error('No columns found in response');
                            return;
                        }

                        // Group new columns into rows of 3
                        const columnsPerRow = 3;
                        const chunkRows = [];
                        for (let i = 0; i < newColumns.length; i += columnsPerRow) {
                            const rowColumns = newColumns.slice(i, i + columnsPerRow);
                            const newRow = document.createElement('div');
                            newRow.className = 'row g-4 mb-4';
                            rowColumns.forEach(col => newRow.appendChild(col));
                            chunkRows.push(newRow);

                            // Insert before view more wrap
                            if (viewMoreWrap && gridContainer) {
                                gridContainer.insertBefore(newRow, viewMoreWrap);
                            } else if (gridContainer) {
                                gridContainer.appendChild(newRow);
                            }
                        }

                        if (chunkRows.length) {
                            loadedChunks.push({ rows: chunkRows, requestedOffset: next });
                        }

                        // re-init clicks for new cards
                        initCardClicks(gridContainer);
                        // detect overflow for new cards
                        detectOverflow(gridContainer);

                        viewMoreBtn.dataset.nextOffset = data.next_offset;

                        if (data.has_more) {
                            viewMoreBtn.style.display = 'inline-block';
                            viewMoreBtn.dataset.hiddenWhenExhausted = 'false';
                        } else {
                            viewMoreBtn.style.display = 'none';
                            viewMoreBtn.dataset.hiddenWhenExhausted = 'true';
                        }

                        // Show view less button if we have loaded chunks
                        if (loadedChunks.length > 0 && viewLessBtn) {
                            viewLessBtn.style.display = 'inline-block';
                        }
                    } else {
                            // If the server returned a valid JSON with no rows, treat it as "no more results" rather than an error.
                            if (data && typeof data.count !== 'undefined' && data.count === 0) {
                                // Update next offset so further clicks (if any) remain consistent
                                if (viewMoreBtn) viewMoreBtn.dataset.nextOffset = data.next_offset;
                                if (data.has_more === false && viewMoreBtn) {
                                    viewMoreBtn.style.display = 'none';
                                    viewMoreBtn.dataset.hiddenWhenExhausted = 'true';
                                }
                                // Nothing to append; silently return
                                return;
                            }

                            console.error('Invalid response data:', data);
                            alert('Failed to load more announcements.');
                    }
                })
                .catch(err => {
                    console.error('View more error:', err);
                    alert('Error loading more announcements. Please try again.');
                })
                .finally(() => {
                    // Re-enable button
                    viewMoreBtn.disabled = false;
                    const originalLabel = viewMoreBtn.dataset.originalLabel || 'View more';
                    viewMoreBtn.innerHTML = originalLabel;
                });
        });
    }

    // Show less button functionality
    if (viewLessBtn) {
        viewLessBtn.addEventListener('click', function(){
            if (!viewMoreBtn) {
                viewLessBtn.style.display = 'none';
                return;
            }

            if (!loadedChunks.length) {
                viewLessBtn.style.display = 'none';
                viewMoreBtn.dataset.nextOffset = viewMoreBtn.dataset.initialNextOffset || String(PAGE_SIZE);
                viewMoreBtn.style.display = 'inline-block';
                viewMoreBtn.disabled = false;
                const originalLabel = viewMoreBtn.dataset.originalLabel || 'View more';
                viewMoreBtn.innerHTML = originalLabel;
                viewMoreBtn.dataset.hiddenWhenExhausted = 'false';
                return;
            }

            while (loadedChunks.length) {
                const chunk = loadedChunks.pop();
                if (chunk && Array.isArray(chunk.rows)) {
                    chunk.rows.forEach(row => {
                        if (row && row.parentNode) {
                            row.parentNode.removeChild(row);
                        }
                    });
                }
            }

            detectOverflow(document);

            const initialOffset = viewMoreBtn.dataset.initialNextOffset || String(PAGE_SIZE);
            viewMoreBtn.dataset.nextOffset = initialOffset;

            viewLessBtn.style.display = 'none';

            const viewMoreWrap = document.getElementById('viewMoreWrap');
            if (viewMoreWrap && viewMoreBtn.parentNode !== viewMoreWrap) {
                viewMoreWrap.insertBefore(viewMoreBtn, viewLessBtn || null);
            }

            viewMoreBtn.style.display = 'inline-block';
            viewMoreBtn.dataset.hiddenWhenExhausted = 'false';
            viewMoreBtn.disabled = false;
            const originalLabel = viewMoreBtn.dataset.originalLabel || 'View more';
            viewMoreBtn.innerHTML = originalLabel;
        });
    }
});