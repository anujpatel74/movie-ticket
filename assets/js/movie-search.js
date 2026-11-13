/**
 * CINEMA BOOKING - Real-Time Movie Search Engine (TMDB + Local Catalogue)
 * 
 * Features:
 * - 300ms input debouncing
 * - 2-character minimum query threshold
 * - In-flight cancellation using AbortController (prevents race conditions)
 * - In-memory query cache to eliminate redundant network requests
 * - Posters with consistent 2:3 aspect ratio and local fallback
 * - Full metadata: Title, Release Year, Rating, Genre, Language, Short Overview
 * - Full keyboard accessibility: ArrowDown, ArrowUp, Enter, Escape
 * - Clear button (X) and animated loading spinner
 * - Mobile responsive drawer support
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('navSearchForm');
    const searchInput = document.getElementById('navSearchInput');
    const searchIcon = document.getElementById('searchIcon');
    const searchSpinner = document.getElementById('searchSpinner');
    const clearBtn = document.getElementById('clearSearchBtn');
    const dropdown = document.getElementById('searchSuggestionsDropdown');
    const list = document.getElementById('searchSuggestionsList');
    const countBadge = document.getElementById('searchResultCount');
    const viewAllLink = document.getElementById('searchViewAllLink');

    if (!searchInput || !dropdown || !list) {
        return;
    }

    const DEBOUNCE_DELAY_MS = 300;
    const MIN_QUERY_LENGTH = 2;

    let debounceTimer = null;
    let activeAbortController = null;
    let currentQuery = '';
    let activeIndex = -1;
    let currentResults = [];

    // In-memory query cache (query -> JSON response) to prevent duplicate API requests
    const searchCache = new Map();

    // Helper: Safely escape HTML to prevent XSS
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Helper: Dynamic base URL resolution
    function getBaseUrl() {
        const base = document.body.getAttribute('data-base-url') || '';
        return base ? base.replace(/\/$/, '') : '';
    }

    function getSearchEndpoint() {
        return getBaseUrl() + '/backend/movie-search.php';
    }

    function getPlaceholderPoster() {
        return getBaseUrl() + '/assets/images/poster-placeholder.svg';
    }

    // 1. Perform Debounced Search with AbortController and Cache
    function performSearch(query) {
        query = query.trim();

        if (query.length < MIN_QUERY_LENGTH) {
            cancelActiveRequest();
            hideDropdown();
            setLoading(false);
            return;
        }

        currentQuery = query;
        const cacheKey = query.toLowerCase();

        // Check in-memory cache first to prevent duplicate network calls
        if (searchCache.has(cacheKey)) {
            cancelActiveRequest();
            setLoading(false);
            const cachedData = searchCache.get(cacheKey);
            currentResults = cachedData.results || [];
            renderResults(currentResults, query);
            return;
        }

        // Cancel previous in-flight request before starting a new one
        cancelActiveRequest();

        // Create new AbortController for this request
        activeAbortController = new AbortController();
        const signal = activeAbortController.signal;

        setLoading(true);

        const url = `${getSearchEndpoint()}?q=${encodeURIComponent(query)}`;

        fetch(url, { signal })
            .then(res => {
                if (!res.ok) throw new Error('Search HTTP error ' + res.status);
                return res.json();
            })
            .then(data => {
                setLoading(false);
                activeAbortController = null;

                // Ignore if query changed while in flight
                if (searchInput.value.trim() !== currentQuery) return;

                if (data && data.success) {
                    // Cache result for quick backspacing / repeated typing
                    searchCache.set(cacheKey, data);
                    currentResults = data.results || [];
                    renderResults(currentResults, query);
                } else {
                    renderNoResults(query);
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') {
                    // Request was cancelled due to rapid typing; silent exit
                    return;
                }
                setLoading(false);
                activeAbortController = null;
                console.warn('Real-time search error:', err);
                renderError('Search temporarily unavailable. Please try again.');
            });
    }

    function cancelActiveRequest() {
        if (activeAbortController) {
            activeAbortController.abort();
            activeAbortController = null;
        }
    }

    // 2. Render Results in Suggestions Dropdown
    function renderResults(results, query) {
        list.innerHTML = '';
        activeIndex = -1;

        if (countBadge) {
            countBadge.textContent = `${results.length} found`;
        }

        if (viewAllLink) {
            const moviesUrl = getBaseUrl() + '/movies.php';
            viewAllLink.href = `${moviesUrl}?q=${encodeURIComponent(query)}`;
            viewAllLink.classList.remove('d-none');
        }

        if (results.length === 0) {
            renderNoResults(query);
            showDropdown();
            return;
        }

        const fragment = document.createDocumentFragment();
        const placeholder = getPlaceholderPoster();

        results.forEach((item, idx) => {
            const row = document.createElement('a');
            row.href = item.details_url;
            row.className = 'search-item';
            row.setAttribute('data-index', idx);
            row.setAttribute('role', 'option');

            // Dynamic status badge
            let statusBadge = '';
            if (item.is_local) {
                if (item.has_shows) {
                    statusBadge = '<span class="search-badge badge-now-showing"><i class="bi bi-ticket-perforated-fill me-1"></i>Book Tickets</span>';
                } else {
                    statusBadge = '<span class="search-badge badge-local">In Theatres</span>';
                }
            } else {
                statusBadge = '<span class="search-badge badge-tmdb">TMDB Global</span>';
            }

            // Rating display
            let ratingDisplay = '';
            if (item.rating && item.rating > 0) {
                ratingDisplay = `<span class="search-item-rating"><i class="bi bi-star-fill text-warning me-1"></i>${item.rating}</span>`;
            }

            // Year & Meta
            const metaParts = [];
            if (item.release_year) metaParts.push(item.release_year);
            if (item.language) metaParts.push(item.language);
            if (item.genre && item.genre !== 'Global Release') metaParts.push(item.genre);
            const metaText = metaParts.join(' &bull; ');

            // Short overview snippet (Requirement 9)
            let overviewHtml = '';
            if (item.overview) {
                overviewHtml = `<div class="search-item-overview">${escapeHtml(item.overview)}</div>`;
            }

            const posterSrc = item.poster_url || placeholder;

            row.innerHTML = `
                <div class="search-item-poster">
                    <img src="${escapeHtml(posterSrc)}" alt="${escapeHtml(item.title)}" loading="lazy" onerror="this.onerror=null; this.src='${escapeHtml(placeholder)}';">
                </div>
                <div class="search-item-info">
                    <div class="search-item-title-row">
                        <span class="search-item-title">${escapeHtml(item.title)}</span>
                        ${statusBadge}
                    </div>
                    <div class="search-item-meta">
                        ${metaText ? `<span>${escapeHtml(metaText)}</span>` : ''}
                        ${ratingDisplay}
                    </div>
                    ${overviewHtml}
                </div>
                <div class="search-item-action">
                    <i class="bi bi-chevron-right"></i>
                </div>
            `;

            row.addEventListener('mouseenter', () => {
                setActiveIndex(idx);
            });

            fragment.appendChild(row);
        });

        list.appendChild(fragment);
        showDropdown();
    }

    // 3. Render Empty / Error states
    function renderNoResults(query) {
        if (countBadge) countBadge.textContent = '0 found';
        list.innerHTML = `
            <div class="search-empty-state">
                <i class="bi bi-film"></i>
                <div class="search-empty-title">No matching movies found</div>
                <div class="search-empty-subtitle">We couldn't find "${escapeHtml(query)}". Try searching for Avengers, Spider-Man, Batman, or Interstellar.</div>
            </div>
        `;
        showDropdown();
    }

    function renderError(msg) {
        list.innerHTML = `
            <div class="search-empty-state">
                <i class="bi bi-exclamation-circle text-danger"></i>
                <div class="search-empty-title">Search Unavailable</div>
                <div class="search-empty-subtitle">${escapeHtml(msg)}</div>
            </div>
        `;
        showDropdown();
    }

    // 4. Loading state toggling
    function setLoading(isLoading) {
        if (searchSpinner && searchIcon) {
            if (isLoading) {
                searchSpinner.classList.remove('d-none');
                searchIcon.classList.add('d-none');
            } else {
                searchSpinner.classList.add('d-none');
                searchIcon.classList.remove('d-none');
            }
        }
    }

    function showDropdown() {
        dropdown.classList.remove('d-none');
    }

    function hideDropdown() {
        dropdown.classList.add('d-none');
        activeIndex = -1;
    }

    function setActiveIndex(newIdx) {
        const items = list.querySelectorAll('.search-item');
        items.forEach(el => el.classList.remove('active'));

        if (newIdx >= 0 && newIdx < items.length) {
            activeIndex = newIdx;
            items[activeIndex].classList.add('active');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else {
            activeIndex = -1;
        }
    }

    // 5. Input Events with 300ms Debounce
    searchInput.addEventListener('input', (e) => {
        const val = e.target.value;

        if (val.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(val);
        }, DEBOUNCE_DELAY_MS);
    });

    searchInput.addEventListener('focus', () => {
        const val = searchInput.value.trim();
        if (val.length >= MIN_QUERY_LENGTH && currentResults.length > 0) {
            showDropdown();
        }
    });

    // 6. Keyboard Navigation (ArrowDown, ArrowUp, Enter, Escape)
    searchInput.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.search-item');
        if (dropdown.classList.contains('d-none') || items.length === 0) {
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIdx = (activeIndex + 1 >= items.length) ? 0 : activeIndex + 1;
            setActiveIndex(nextIdx);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIdx = (activeIndex - 1 < 0) ? items.length - 1 : activeIndex - 1;
            setActiveIndex(prevIdx);
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && activeIndex < items.length) {
                e.preventDefault();
                items[activeIndex].click();
            }
            // Otherwise let form submit normally to movies.php?q=...
        } else if (e.key === 'Escape') {
            hideDropdown();
            searchInput.blur();
        }
    });

    // 7. Clear Button Action
    if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            cancelActiveRequest();
            clearTimeout(debounceTimer);
            searchInput.value = '';
            clearBtn.classList.add('d-none');
            hideDropdown();
            currentResults = [];
            setLoading(false);
            searchInput.focus();
        });
    }

    // 8. Dismiss on Outside Click
    document.addEventListener('click', (e) => {
        if (!searchForm.contains(e.target) && !dropdown.contains(e.target)) {
            hideDropdown();
        }
    });
});
