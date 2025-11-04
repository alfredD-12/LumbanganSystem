// News fetching functionality
let currentPage = 1;
const newsPerPage = 10;

// Function to extract news from HTML content
function extractNewsFromHTML(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newsItems = [];
    
    // Find all news articles
    const articles = doc.querySelectorAll('article, .news-item, .post, .entry');
    
    articles.forEach(article => {
        const titleElement = article.querySelector('h1, h2, h3, .entry-title, .post-title');
        const dateElement = article.querySelector('time, .date, .entry-date, .post-date');
        const excerptElement = article.querySelector('.entry-content, .post-content, .excerpt');
        const linkElement = article.querySelector('a[href*="batangas.gov.ph"]');
        const imageElement = article.querySelector('img');

        if (titleElement && linkElement) {
            newsItems.push({
                title: titleElement.textContent.trim(),
                date: dateElement ? dateElement.textContent.trim() : 'Recent',
                excerpt: excerptElement ? 
                    excerptElement.textContent.trim().substring(0, 150) + '...' : 
                    'Click to read more about this news.',
                link: linkElement.href,
                image: imageElement ? imageElement.src : null
            });
        }
    });
    
    return newsItems;
}

async function fetchNews(page = 1) {
    console.log('Fetching news for page:', page);
    const newsContainer = document.getElementById('newsContainer');
    const loadMoreBtn = document.getElementById('loadMoreNews');
    const loadingSpinner = document.getElementById('newsLoading');

    if (!newsContainer) {
        console.error('News container not found!');
        return;
    }

    if (page === 1) {
        newsContainer.innerHTML = `
            <div class="col-12 text-center">
                <h3>Batangas News & Updates</h3>
                <p class="text-muted mb-4">Real-time news from Batangas Provincial Government</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Fetching latest news from Batangas...</p>
            </div>
        `;
    }

    loadMoreBtn.style.display = 'none';
    loadingSpinner.classList.remove('d-none');

    try {
        // Using fetch-inject technique with backup URLs
        const urls = [
            'https://corsproxy.io/?url=' + encodeURIComponent('https://portal.batangas.gov.ph/news/'),
            'https://api.allorigins.win/raw?url=' + encodeURIComponent('https://portal.batangas.gov.ph/news/'),
            'https://thingproxy.freeboard.io/fetch/' + encodeURIComponent('https://portal.batangas.gov.ph/news/')
        ];

        let content = null;
        let error = null;

        // Try each URL until one works
        for (const url of urls) {
            try {
                console.log('Trying URL:', url);
                const response = await fetch(url);
                if (response.ok) {
                    content = await response.text();
                    console.log('Successfully fetched content');
                    break;
                }
            } catch (e) {
                console.log('Failed with URL:', url, e);
                error = e;
                continue;
            }
        }

        if (!content) {
            throw error || new Error('Failed to fetch news from all sources');
        }

        // Extract news items from the HTML
        const news = extractNewsFromHTML(content);
        console.log('Extracted news items:', news);
        
        if (page === 1) {
            newsContainer.innerHTML = ''; // Clear loading message
        }

        if (news.length === 0) {
            newsContainer.innerHTML = `
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">No News Available</h4>
                        <p>We're having trouble fetching the latest news. Please try again later.</p>
                        <button onclick="fetchNews(1)" class="btn btn-primary mt-3">
                            <i class="fas fa-sync-alt me-2"></i>Refresh News
                        </button>
                    </div>
                </div>
            `;
            return;
        }

        // Display the news items
        news.forEach(article => {
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4 mb-4';
            card.innerHTML = `
                <div class="card news-card h-100">
                    ${article.image ? `
                        <img src="${article.image}" class="card-img-top" alt="${article.title}"
                             onerror="this.onerror=null; this.src='https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png';">
                    ` : ''}
                    <div class="card-body d-flex flex-column">
                        <div class="news-date mb-2">
                            <i class="far fa-calendar-alt me-2"></i>${article.date}
                        </div>
                        <h5 class="card-title mb-3">${article.title}</h5>
                        <p class="card-text mb-3">${article.excerpt}</p>
                        <a href="${article.link}" class="news-read-more mt-auto" target="_blank">
                            Read More 
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            `;
            newsContainer.appendChild(card);
        });

        // Show load more button if we got full page of results
        if (news.length >= 10) {
            loadMoreBtn.style.display = 'inline-block';
        } else {
            loadMoreBtn.style.display = 'none';
        }

    } catch (error) {
        console.error('Error fetching news:', error);
        newsContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Error Loading News</h4>
                    <p>${error.message}</p>
                    <button onclick="fetchNews(${page})" class="btn btn-danger mt-3">
                        <i class="fas fa-sync-alt me-2"></i>Try Again
                    </button>
                </div>
            </div>
        `;
    } finally {
        loadingSpinner.classList.add('d-none');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing news functionality');
    fetchNews().catch(error => {
        console.error('Error in initial news fetch:', error);
    });

    // Load more button handler
    const loadMoreBtn = document.getElementById('loadMoreNews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentPage++;
            fetchNews(currentPage);
        });
    }
});