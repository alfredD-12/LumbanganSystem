// Global function to open announcement modal
function openAnnouncementModal(card) {
	const title = card.dataset.title || '';
	const message = card.dataset.message || '';
	const image = card.dataset.image || '';
	const author = card.dataset.author || '';
	const audience = card.dataset.audience || '';
	const created = card.dataset.created || '';

	const displayAudience = audience
		? audience.charAt(0).toUpperCase() + audience.slice(1)
		: 'All';

	const modalTitle = document.getElementById('modalTitle');
	const modalMeta = document.getElementById('modalMeta');
	const modalMessage = document.getElementById('modalMessage');
	const modalImage = document.getElementById('modalImage');
	const modalImageWrap = document.getElementById('modalImageWrap');
	const viewMoreBtn = document.getElementById('viewMoreBtn');
	const annModalEl = document.getElementById('announcementModal');

	if (modalTitle) modalTitle.textContent = title;
	if (modalMeta) modalMeta.textContent = `${displayAudience} • ${author} • ${created}`;
	
	// Set message content
	if (modalMessage) {
		const formattedMessage = (message || '').replace(/\n/g, '<br>');
		modalMessage.innerHTML = formattedMessage;
		
		// Check if content is long enough to need View More button
		const messageLength = message.length;
		const lineBreaks = (message.match(/\n/g) || []).length;
		
		if (viewMoreBtn) {
			// Show View More button if message is long (more than 500 chars or 8+ lines)
			if (messageLength > 500 || lineBreaks > 8) {
				modalMessage.classList.add('collapsed');
				viewMoreBtn.style.display = 'block';
					viewMoreBtn.innerHTML = '<i class="fas fa-chevron-down me-1"></i>View More';
				
				// Remove old event listeners by cloning
				const newBtn = viewMoreBtn.cloneNode(true);
				viewMoreBtn.parentNode.replaceChild(newBtn, viewMoreBtn);
				
				// Add new click handler
				newBtn.addEventListener('click', function() {
							if (modalMessage.classList.contains('collapsed')) {
								modalMessage.classList.remove('collapsed');
								this.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Show Less';
					} else {
						modalMessage.classList.add('collapsed');
								this.innerHTML = '<i class="fas fa-chevron-down me-1"></i>View More';
					}
				});
			} else {
				modalMessage.classList.remove('collapsed');
				viewMoreBtn.style.display = 'none';
			}
		}
	}

	if (modalImage && modalImageWrap) {
		if (image) {
			// If the image value already looks like a full URL or absolute path, use it directly.
			// Otherwise, fall back to constructing the URL using BASE_URL + uploads path.
			let src = image;
			const isAbsoluteUrl = /^https?:\/\//i.test(image) || image.startsWith('/');
			if (!isAbsoluteUrl) {
				const base = (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '../app/';
				src = base + 'uploads/announcementimage/' + image;
			}
			// show spinner while the image loads to avoid flashes
			const spinner = document.getElementById('modalImageSpinner');
			if (spinner) spinner.style.display = 'inline-block';
			modalImage.style.display = 'none';
			modalImageWrap.style.display = 'block';

			// remove any previous handlers to avoid duplicate behavior
			modalImage.onload = function () {
				if (spinner) spinner.style.display = 'none';
				modalImage.style.display = 'block';
			};
			modalImage.onerror = function () {
				if (spinner) spinner.style.display = 'none';
				modalImage.style.display = 'none';
			};

			// finally set src to start loading
			modalImage.src = src;
		} else {
			modalImage.src = '';
			modalImage.style.display = 'none';
			const spinner = document.getElementById('modalImageSpinner');
			if (spinner) spinner.style.display = 'none';
			modalImageWrap.style.display = 'none';
		}
	}

	if (annModalEl) {
		const modalInstance = bootstrap.Modal.getOrCreateInstance(annModalEl);
		modalInstance.show();
	}
}

document.addEventListener('DOMContentLoaded', () => {
	// Initialize 3D Carousels
	init3DCarousel('todayCarousel', 'prevBtn', 'nextBtn', 'carouselIndicators');
	init3DCarousel('earlierCarousel', 'earlierPrevBtn', 'earlierNextBtn', 'earlierCarouselIndicators', true);
	
	// Initialize Masonry Grid Animations
	initMasonryAnimations();
	
	const sliderWindows = Array.from(document.querySelectorAll('.slider-window'));
	const annModalEl = document.getElementById('announcementModal');
	const modalTitle = document.getElementById('modalTitle');
	const modalMeta = document.getElementById('modalMeta');
	const modalMessage = document.getElementById('modalMessage');
	const modalImage = document.getElementById('modalImage');
	const modalImageWrap = document.getElementById('modalImageWrap');

	function handleCardClick(card) {
		openAnnouncementModal(card);
	}

	function initCardClicks(scope) {
		const targets = (scope || document).querySelectorAll('.slider-card');
		targets.forEach((card) => {
			if (card.dataset.clickInit) return;
			card.dataset.clickInit = '1';
			card.addEventListener('click', () => handleCardClick(card));
		});
	}

	sliderWindows.forEach((sliderWindow) => {
		const track = sliderWindow.querySelector('.slider-track');
		if (!track) return;
		if (track.dataset.sliderInit === '1') return;
		let cards = Array.from(track.querySelectorAll('.slider-card'));
		if (!cards.length) return;

		track.dataset.sliderInit = '1';

		const prevBtn = sliderWindow.querySelector('.slider-nav.prev');
		const nextBtn = sliderWindow.querySelector('.slider-nav.next');

		function getGap() {
			const style = getComputedStyle(track);
			return parseFloat(style.columnGap || style.gap || '0');
		}

		function totalContentWidth(elements) {
			const gap = getGap();
			return elements.reduce((sum, el) => sum + el.getBoundingClientRect().width, 0) + gap * Math.max(0, elements.length - 1);
		}

		const baseContentWidth = totalContentWidth(cards);
		const loopEnabled = cards.length > 1 && baseContentWidth > sliderWindow.clientWidth + 1;

		if (loopEnabled) {
			const fragment = document.createDocumentFragment();
			cards.forEach((card) => {
				const clone = card.cloneNode(true);
				clone.classList.add('clone');
				fragment.appendChild(clone);
			});
			track.appendChild(fragment);
		} else {
			if (prevBtn) prevBtn.style.display = 'none';
			if (nextBtn) nextBtn.style.display = 'none';
			sliderWindow.classList.add('slider-static');
		}

	initCardClicks(track);

	let currentX = 0;
	let manualPause = false;
	let manualTimer = null;
	let isHovered = false;

	function computeStep() {
		const firstCard = track.querySelector('.slider-card');
		if (!firstCard) return 0;
		return firstCard.getBoundingClientRect().width + getGap();
	}

	let cardStep = computeStep();

	function updateTransform(withTransition = false) {
		if (withTransition) {
			track.classList.add('with-transition');
		} else {
			track.classList.remove('with-transition');
		} 
		track.style.transform = `translateX(${currentX}px)`;
	}

	function contentWidth() {
		return loopEnabled ? track.scrollWidth / 2 : track.scrollWidth;
	}

	function normalizePosition() {
		if (!loopEnabled) return;
		const width = contentWidth();
		if (width <= 0) return;

		let adjusted = false;
		while (currentX <= -width) {
			currentX += width;
			adjusted = true;
		}
		while (currentX > 0) {
			currentX -= width;
			adjusted = true;
		}

		if (adjusted) updateTransform(false);
	}

	function requestManualPause() {
		manualPause = true;
		clearTimeout(manualTimer);
		manualTimer = setTimeout(() => {
			manualPause = false;
			normalizePosition();
		}, 650);
	}

	function slideBy(direction) {
		if (!loopEnabled) return;
		const distance = (cardStep || sliderWindow.clientWidth * 0.75) * direction;
		currentX -= distance;
		requestManualPause();
		updateTransform(true);
		setTimeout(() => normalizePosition(), 480);
	}

	if (prevBtn && loopEnabled) {
		prevBtn.addEventListener('click', (event) => {
			event.preventDefault();
			slideBy(-1);
		});
		prevBtn.addEventListener('focusin', requestManualPause);
	}

	if (nextBtn && loopEnabled) {
		nextBtn.addEventListener('click', (event) => {
			event.preventDefault();
			slideBy(1);
		});
		nextBtn.addEventListener('focusin', requestManualPause);
	}

	sliderWindow.addEventListener('mouseenter', () => {
		isHovered = true;
		requestManualPause();
	});

	sliderWindow.addEventListener('mouseleave', () => {
		isHovered = false;
	});

	const autoSpeed = 0.6;

	function autoLoop() {
		if (!loopEnabled) return;
		if (!isHovered && !manualPause) {
			currentX -= autoSpeed;
			const width = contentWidth();
			if (width > 0 && currentX <= -width) {
				currentX += width;
			}
			updateTransform(false);
		}

		requestAnimationFrame(autoLoop);
	}

	autoLoop();

	let resizeTimer = null;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(() => {
			cardStep = computeStep();
		}, 200);
	});

	updateTransform(false);
	});

	initCardClicks(document);

	if (modalImage) {
		modalImage.addEventListener('click', function () {
			if (!this.src) return;

			const imgModal = document.getElementById('imageModal');
			const imgEl = document.getElementById('imageModalImg');
			if (!imgModal || !imgEl) return;

			// Accessibility: if another modal (e.g., announcementModal) is open, hide it
			// before showing the image modal to avoid aria-hidden being set on a focused
			// element. When the image modal closes, restore the previous modal.
			const activeModalEl = document.querySelector('.modal.show');
			let previousModal = null;
			if (activeModalEl && activeModalEl.id && activeModalEl.id !== 'imageModal') {
				previousModal = activeModalEl;
				const prevInstance = bootstrap.Modal.getInstance(previousModal) || bootstrap.Modal.getOrCreateInstance(previousModal);
				try {
					prevInstance.hide();
				} catch (e) {
					// ignore
				}
			}

			// Set the image and show the image modal
			imgEl.src = this.src;
			const fullModal = bootstrap.Modal.getOrCreateInstance(imgModal);
			fullModal.show();

			// When the image modal hides, restore focus/back to the previous modal (if any)
			if (previousModal) {
				const onHidden = function () {
					// remove this listener to avoid duplicate work
					imgModal.removeEventListener('hidden.bs.modal', onHidden);
					// Re-open previous modal
					const prevInstance2 = bootstrap.Modal.getOrCreateInstance(previousModal);
					prevInstance2.show();
					// focus the previously focused element inside the reopened modal
					const focusable = previousModal.querySelector('[autofocus], button, [tabindex]:not([tabindex="-1"])');
					if (focusable) focusable.focus();
				};
				imgModal.addEventListener('hidden.bs.modal', onHidden);
			}
		});
	}
});

// ========================================
// MASONRY GRID ANIMATIONS
// ========================================

function initMasonryAnimations() {
	const masonryCards = document.querySelectorAll('.masonry-card');
	
	if (masonryCards.length === 0) return;
	
	// Intersection Observer for scroll animations
	const observerOptions = {
		root: null,
		threshold: 0.1,
		rootMargin: '0px 0px -50px 0px'
	};
	
	const observer = new IntersectionObserver((entries) => {
		entries.forEach((entry, index) => {
			if (entry.isIntersecting) {
				setTimeout(() => {
					entry.target.style.opacity = '1';
					entry.target.style.transform = 'translateY(0)';
				}, index * 50);
				observer.unobserve(entry.target);
			}
		});
	}, observerOptions);
	
	masonryCards.forEach((card) => {
		if (!card.classList.contains('hidden')) {
			card.style.opacity = '0';
			card.style.transform = 'translateY(30px)';
			card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
			observer.observe(card);
		}
	});
}

// ========================================
// 3D CAROUSEL FUNCTIONALITY
// ========================================

function init3DCarousel(carouselId, prevBtnId, nextBtnId, indicatorsId, hasPagination = false) {
	const carousel = document.getElementById(carouselId);
	if (!carousel) return;
	
	const cards = Array.from(carousel.querySelectorAll('.carousel-card-3d'));
	if (cards.length === 0) return;
	
	const prevBtn = document.getElementById(prevBtnId);
	const nextBtn = document.getElementById(nextBtnId);
	const indicatorsContainer = document.getElementById(indicatorsId);
	const indicators = indicatorsContainer ? Array.from(indicatorsContainer.querySelectorAll('.indicator-dot')) : [];
	
	// Pagination controls (only for earlier carousel)
	const prevPageBtn = hasPagination ? document.getElementById('prevPageBtn') : null;
	const nextPageBtn = hasPagination ? document.getElementById('nextPageBtn') : null;
	const currentSlideEl = hasPagination ? document.getElementById('currentSlide') : null;
	const totalSlidesEl = hasPagination ? document.getElementById('totalSlides') : null;
	
	let currentIndex = 0;
	let isAnimating = false;
	let autoRotateInterval = null;
	let currentPage = 0;
	const indicatorsPerPage = 10;
	const totalPages = Math.ceil(cards.length / indicatorsPerPage);
	
	// Calculate positions for 3D carousel effect
	function updateCarousel(animate = true) {
		// re-query cards in case DOM changed
		const currentCards = Array.from(carousel.querySelectorAll('.carousel-card-3d'));
		const totalCards = currentCards.length;
		if (!totalCards) return;
		// measure container and card sizes
		const containerW = carousel.clientWidth || carousel.offsetWidth || 800;
		let maxW = 0, maxH = 0;
		currentCards.forEach(c => {
			const r = c.getBoundingClientRect();
			if (r.width > maxW) maxW = r.width;
			if (r.height > maxH) maxH = r.height;
			// clear inline transform for accurate measurement
			c.style.transform = 'none';
		});
		// normalize sizes to help stable layout (only if measurements found)
		if (maxW > 0) {
			currentCards.forEach(c => {
				c.style.width = Math.ceil(maxW) + 'px';
				c.style.height = Math.ceil(maxH) + 'px';
				c.style.transformOrigin = '50% 50%';
			});
		}
		// dynamic radius based on container size
		const radius = Math.max(220, Math.min(900, containerW * 0.45));
		const angleStep = 360 / totalCards;
		
		currentCards.forEach((card, idx) => {
			const relative = ((idx - currentIndex) % totalCards + totalCards) % totalCards;
			// center indices near 0 for easier distance calculations
			let distIndex = relative;
			if (distIndex > totalCards / 2) distIndex = distIndex - totalCards;
			const angle = distIndex * angleStep;
			const rad = (angle * Math.PI) / 180;
			let x = Math.sin(rad) * radius;
			let z = Math.cos(rad) * radius - radius;
			// clamp x so cards never fly too far outside
			const clampX = containerW * 1.15;
			x = Math.max(-clampX, Math.min(clampX, x));
			// distance factor influences scale/opacity
			const distanceFactor = Math.min(1, Math.abs(distIndex) / (totalCards / 2));
			const scale = 1 - (0.35 * distanceFactor);
			const opacity = 1 - (0.55 * distanceFactor);
			const zIndex = Math.round(100 - Math.abs(distIndex));
			const transformStyle = `translate(-50%, -50%) translate3d(${x}px, 0, ${z}px) scale(${scale})`;
			card.style.transition = animate ? 'transform 0.8s cubic-bezier(0.4, 0.0, 0.2, 1), opacity 0.6s ease' : 'none';
			card.style.transform = transformStyle;
			card.style.opacity = opacity;
			card.style.zIndex = zIndex;
			if (distIndex === 0) {
				card.style.pointerEvents = 'auto';
				card.classList.add('active');
			} else {
				card.style.pointerEvents = 'none';
				card.classList.remove('active');
			}
		});
		
		// Update indicators
		updateIndicators();
		
		// Update counter
		if (currentSlideEl) {
			currentSlideEl.textContent = currentIndex + 1;
		}
		
		// Update pagination buttons
		if (hasPagination) {
			updatePaginationButtons();
		}
	}
	
	// Update indicators display
	function updateIndicators() {
		if (!indicators.length) return;
		
		const startIndex = currentPage * indicatorsPerPage;
		const endIndex = Math.min(startIndex + indicatorsPerPage, cards.length);
		
		indicators.forEach((dot, index) => {
			const cardIndex = startIndex + index;
			
			if (cardIndex < endIndex) {
				dot.style.display = 'block';
				dot.dataset.slideTo = cardIndex;
				
				if (cardIndex === currentIndex) {
					dot.classList.add('active');
				} else {
					dot.classList.remove('active');
				}
			} else {
				dot.style.display = 'none';
			}
		});
	}
	
	// Update pagination buttons
	function updatePaginationButtons() {
		if (!prevPageBtn || !nextPageBtn) return;
		
		prevPageBtn.disabled = currentPage === 0;
		nextPageBtn.disabled = currentPage === totalPages - 1;
	}
	
	// Navigate to specific index
	function goToSlide(index) {
		if (isAnimating) return;
		isAnimating = true;
		
		currentIndex = (index + cards.length) % cards.length;
		
		// Update current page if needed
		if (hasPagination) {
			currentPage = Math.floor(currentIndex / indicatorsPerPage);
		}
		
		updateCarousel(true);
		
		setTimeout(() => {
			isAnimating = false;
		}, 800);
		
		if (!hasPagination) {
			resetAutoRotate();
		}
	}
	
	// Previous slide
	function prevSlide() {
		goToSlide(currentIndex - 1);
	}
	
	// Next slide
	function nextSlide() {
		goToSlide(currentIndex + 1);
	}
	
	// Previous page
	function prevPage() {
		if (currentPage > 0) {
			currentPage--;
			goToSlide(currentPage * indicatorsPerPage);
		}
	}
	
	// Next page
	function nextPage() {
		if (currentPage < totalPages - 1) {
			currentPage++;
			goToSlide(currentPage * indicatorsPerPage);
		}
	}
	
	// Auto rotate (only for today's carousel)
	function startAutoRotate() {
		if (hasPagination) return; // Don't auto-rotate earlier carousel
		
		stopAutoRotate();
		autoRotateInterval = setInterval(() => {
			nextSlide();
		}, 5000); // Rotate every 5 seconds
	}
	
	function stopAutoRotate() {
		if (autoRotateInterval) {
			clearInterval(autoRotateInterval);
			autoRotateInterval = null;
		}
	}
	
	function resetAutoRotate() {
		if (hasPagination) return;
		stopAutoRotate();
		startAutoRotate();
	}
	
	// Event listeners
	if (prevBtn) {
		prevBtn.addEventListener('click', (e) => {
			e.preventDefault();
			prevSlide();
		});
	}
	
	if (nextBtn) {
		nextBtn.addEventListener('click', (e) => {
			e.preventDefault();
			nextSlide();
		});
	}
	
	// Pagination event listeners
	if (prevPageBtn) {
		prevPageBtn.addEventListener('click', (e) => {
			e.preventDefault();
			prevPage();
		});
	}
	
	if (nextPageBtn) {
		nextPageBtn.addEventListener('click', (e) => {
			e.preventDefault();
			nextPage();
		});
	}
	
	// Indicator clicks
	indicators.forEach((dot) => {
		dot.addEventListener('click', () => {
			const slideIndex = parseInt(dot.dataset.slideTo);
			if (!isNaN(slideIndex)) {
				goToSlide(slideIndex);
			}
		});
	});
	
	// Keyboard navigation
	document.addEventListener('keydown', (e) => {
		if (e.key === 'ArrowLeft') {
			prevSlide();
		} else if (e.key === 'ArrowRight') {
			nextSlide();
		}
	});
	
	// Pause auto-rotate on hover (only for today's carousel)
	if (!hasPagination) {
		carousel.addEventListener('mouseenter', stopAutoRotate);
		carousel.addEventListener('mouseleave', startAutoRotate);
	}
	
	// Touch/swipe support
	let touchStartX = 0;
	let touchEndX = 0;
	
	carousel.addEventListener('touchstart', (e) => {
		touchStartX = e.changedTouches[0].screenX;
		if (!hasPagination) stopAutoRotate();
	}, { passive: true });
	
	carousel.addEventListener('touchend', (e) => {
		touchEndX = e.changedTouches[0].screenX;
		handleSwipe();
		if (!hasPagination) startAutoRotate();
	}, { passive: true });
	
	function handleSwipe() {
		const swipeThreshold = 50;
		const diff = touchStartX - touchEndX;
		
		if (Math.abs(diff) > swipeThreshold) {
			if (diff > 0) {
				nextSlide();
			} else {
				prevSlide();
			}
		}
	}
	
	// Initialize carousel
	updateCarousel(false);
	if (!hasPagination) {
		startAutoRotate();
	}
	
	// Handle window resize
	let resizeTimer;
	window.addEventListener('resize', () => {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(() => {
			updateCarousel(false);
		}, 250);
	});
}
