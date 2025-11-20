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
            this.newsContent.style.display = 'block';
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
        
        const container = document.getElementById('newsCarouselContainer');
        const indicators = document.getElementById('newsCarouselIndicators');
        if (!container) return;
        
        container.innerHTML = '';
        const newsItems = news.slice(0, 6);
        
        newsItems.forEach((article, index) => {
            const card = document.createElement('div');
            card.className = 'news-carousel-card';
            card.setAttribute('data-index', index);
            card.innerHTML = `
                <div class="news-carousel-card-img">
                    ${article.image ? `
                        <img src="${article.image}" alt="${article.title}"
                             onerror="this.style.display='none'; this.parentElement.querySelector('i').style.display='flex';">
                        <i class="fas fa-newspaper" style="display: none;"></i>
                    ` : `<i class="fas fa-newspaper"></i>`}
                </div>
                <div class="news-carousel-card-content">
                    ${article.date ? `
                        <div class="news-carousel-card-date">
                            <i class="far fa-calendar-alt"></i> ${article.date}
                        </div>
                    ` : ''}
                    <h4 class="news-carousel-card-title">${article.title}</h4>
                    <p class="news-carousel-card-excerpt">${article.excerpt}</p>
                    <a href="${article.link}" class="news-carousel-card-link" target="_blank">
                        Read Full Article <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            `;
            container.appendChild(card);
        });
        
        if (indicators) {
            indicators.innerHTML = '';
            newsItems.forEach((_, index) => {
                const indicator = document.createElement('span');
                indicator.className = 'news-indicator' + (index === 0 ? ' active' : '');
                indicator.setAttribute('onclick', `goToNews(${index})`);
                indicators.appendChild(indicator);
            });
        }
        
        this.showContent();
        if (typeof updateNewsCarousel === 'function') {
            setTimeout(() => updateNewsCarousel(), 100);
        }
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

// News Carousel Functions
let currentNewsIndex = 0;

function updateNewsCarousel() {
    const cards = document.querySelectorAll('.news-carousel-card');
    const indicators = document.querySelectorAll('.news-indicator');
    const totalNewsItems = cards.length;
    if (totalNewsItems === 0) return;
    
    cards.forEach((card, index) => {
        const dataIndex = parseInt(card.getAttribute('data-index'));
        let position = dataIndex - currentNewsIndex;
        if (position > totalNewsItems / 2) position -= totalNewsItems;
        if (position < -totalNewsItems / 2) position += totalNewsItems;
        
        card.classList.remove('active');
        if (position === 0) {
            card.style.transform = 'translateX(0) scale(1) rotateY(0deg)';
            card.style.zIndex = '3';
            card.style.opacity = '1';
            card.style.filter = 'blur(0px)';
            card.style.pointerEvents = 'auto';
            card.classList.add('active');
        } else if (position === 1) {
            card.style.transform = 'translateX(100%) scale(0.85) rotateY(-15deg)';
            card.style.zIndex = '2';
            card.style.opacity = '0.5';
            card.style.filter = 'blur(2px)';
            card.style.pointerEvents = 'none';
        } else if (position === -1) {
            card.style.transform = 'translateX(-100%) scale(0.85) rotateY(15deg)';
            card.style.zIndex = '2';
            card.style.opacity = '0.5';
            card.style.filter = 'blur(2px)';
            card.style.pointerEvents = 'none';
        } else {
            card.style.transform = position > 0 ? 'translateX(100%) scale(0.7)' : 'translateX(-100%) scale(0.7)';
            card.style.zIndex = '1';
            card.style.opacity = '0';
            card.style.filter = 'blur(3px)';
            card.style.pointerEvents = 'none';
        }
    });
    
    indicators.forEach((indicator, index) => {
        if (index === currentNewsIndex) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

function nextNews() {
    const totalNewsItems = document.querySelectorAll('.news-carousel-card').length;
    if (totalNewsItems === 0) return;
    currentNewsIndex = (currentNewsIndex + 1) % totalNewsItems;
    updateNewsCarousel();
}

function previousNews() {
    const totalNewsItems = document.querySelectorAll('.news-carousel-card').length;
    if (totalNewsItems === 0) return;
    currentNewsIndex = (currentNewsIndex - 1 + totalNewsItems) % totalNewsItems;
    updateNewsCarousel();
}

function goToNews(index) {
    currentNewsIndex = index;
    updateNewsCarousel();
}