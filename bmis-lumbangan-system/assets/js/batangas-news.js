// Batangas News Fetcher - Reliable version
class BatangasNewsFetcher {
    constructor() {
        this.newsContent = document.getElementById('newsContent');
        this.newsLoading = document.getElementById('newsLoading');
        this.newsError = document.getElementById('newsError');
        this.refreshBtn = document.getElementById('refreshNews');
        
        this.init();
    }
    
    init() {
        this.fetchNews();
        
        if (this.refreshBtn) {
            this.refreshBtn.addEventListener('click', () => {
                this.fetchNews(false);
                this.refreshBtn.classList.add('rotating');
                setTimeout(() => {
                    this.refreshBtn.classList.remove('rotating');
                }, 1000);
            });
        }
    }
    
    showLoading() {
        if (this.newsLoading) {
            this.newsLoading.style.display = 'flex';
        }
        if (this.newsContent) {
            this.newsContent.style.display = 'none';
        }
        if (this.newsError) {
            this.newsError.style.display = 'none';
        }
    }

    showError() {
        if (this.newsLoading) {
            this.newsLoading.style.display = 'none';
        }
        if (this.newsContent) {
            this.newsContent.style.display = 'none';
        }
        if (this.newsError) {
            this.newsError.style.display = 'flex';
        }
    }

    showContent() {
        if (this.newsLoading) {
            this.newsLoading.style.display = 'none';
        }
        if (this.newsContent) {
            this.newsContent.style.display = 'flex';
        }
        if (this.newsError) {
            this.newsError.style.display = 'none';
        }
    }
    
    async fetchNews(silent = false) {
        if (!silent) {
            this.showLoading();
        }
        
        try {
            const response = await fetch('https://corsproxy.io/?url=' + encodeURIComponent('https://portal.batangas.gov.ph/news/'));
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const html = await response.text();
            const news = this.extractNewsFromHTML(html);
            this.displayNews(news);
        } catch (error) {
            console.error('Error fetching news:', error);
            if (!silent) {
                this.showError();
            }
        }
    }
    
    extractNewsFromHTML(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newsItems = [];
        const seen = new Set();
        const articles = doc.querySelectorAll('article, .post');
        articles.forEach(article => {
            const title = article.querySelector('h1, h2, h3')?.textContent.trim();
            const date = article.querySelector('time, .date')?.textContent.trim();
            const rawExcerpt = article.querySelector('.entry-content, .excerpt')?.textContent?.trim() || '';
            const excerpt = rawExcerpt ? (rawExcerpt.slice(0, 150) + '...') : '';
            const anchor = article.querySelector('a[href*="batangas.gov.ph"]');
            const link = anchor?.href || article.querySelector('a')?.href || null;
            let image = article.querySelector('img')?.getAttribute('src') || null;

            // Normalize relative image URLs to absolute using the fetched document base
            if (image && image.startsWith('/')) {
                const base = (new URL('https://portal.batangas.gov.ph')).origin;
                image = base + image;
            }

            // Deduplicate by link (if available) or title fallback
            const dedupeKey = link || title;
            if (!dedupeKey) return;
            if (seen.has(dedupeKey)) return;
            seen.add(dedupeKey);

            if (title && link) {
                newsItems.push({ title, date, excerpt, link, image });
            }
        });
        
        return newsItems;
    }
    
    displayNews(news) {
        if (!this.newsContent) return;
        
        this.newsContent.innerHTML = '';
        
        news.forEach(article => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';
            
            col.innerHTML = `
                <div class="card news-card h-100">
                    ${article.image ? `
                        <img src="${article.image}" class="card-img-top" alt="${article.title}"
                             onerror="this.src='https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png'">
                    ` : ''}
                    <div class="card-body d-flex flex-column">
                        ${article.date ? `
                            <div class="news-date">
                                <i class="far fa-calendar-alt me-2"></i>${article.date}
                            </div>
                        ` : ''}
                        <h5 class="card-title">${article.title}</h5>
                        <p class="card-text flex-grow-1">${article.excerpt}</p>
                        <a href="${article.link}" class="btn btn-link news-read-more mt-auto" target="_blank">
                            Read More <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            `;
            
            this.newsContent.appendChild(col);
        });
        
        this.showContent();
    }
}

// Initialize the news fetcher when the DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('newsContent')) {
            new BatangasNewsFetcher();
        }
    });
} else {
    if (document.getElementById('newsContent')) {
        new BatangasNewsFetcher();
    }
}