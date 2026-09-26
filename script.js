/**
 * Cloudinary Media Gallery Application JavaScript
 * Clean, modern Vanilla JS implementation.
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- DOM Elements ---
  const mediaGrid = document.getElementById('mediaGrid');
  const loadingSpinner = document.getElementById('loadingSpinner');
  const emptyGallery = document.getElementById('emptyGallery');
  const searchInput = document.getElementById('searchInput');
  const filterButtons = document.querySelectorAll('.filter-btn');
  const zipFileInput = document.getElementById('zipFileInput');
  const dropZoneZipInput = document.getElementById('dropZoneZipInput');
  const dropZone = document.getElementById('dropZone');
  const folderFileInput = document.getElementById('folderFileInput');
  const currentYearSpan = document.getElementById('currentYear');

  // Lightbox Modal Elements
  const lightboxModalEl = document.getElementById('lightboxModal');
  const lightboxModal = new bootstrap.Modal(lightboxModalEl);
  const lightboxBody = document.getElementById('lightboxBody');
  const lightboxModalLabel = document.getElementById('lightboxModalLabel');
  const modalMediaTypeBadge = document.getElementById('modalMediaTypeBadge');
  const lightboxFilename = document.getElementById('lightboxFilename');

  // Badge count elements
  const countAll = document.getElementById('count-all');
  const countImage = document.getElementById('count-image');
  const countVideo = document.getElementById('count-video');

  // --- State Variables ---
  let allMediaItems = [];
  let currentFilter = 'all';
  let searchQuery = '';

  // Supported Extensions
  const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'];
  const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'ogg', 'm4v'];

  // Set current year in footer
  if (currentYearSpan) {
    currentYearSpan.textContent = new Date().getFullYear();
  }

  /**
   * Helper: Get file extension from filename or URL
   */
  function getFileExtension(filename) {
    if (!filename) return '';
    const cleanUrl = filename.split('?')[0].split('#')[0];
    const parts = cleanUrl.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
  }

  /**
   * Helper: Determine media type ('image' or 'video') from filename or URL
   */
  function detectMediaType(urlOrFilename) {
    const ext = getFileExtension(urlOrFilename);
    if (IMAGE_EXTENSIONS.includes(ext)) {
      return 'image';
    }
    if (VIDEO_EXTENSIONS.includes(ext)) {
      return 'video';
    }
    return 'image'; // default fallback
  }

  /**
   * Helper: Extract filename from URL or path
   */
  function getFilename(urlOrPath) {
    if (!urlOrPath) return 'Media Item';
    const cleanUrl = urlOrPath.split('?')[0].split('#')[0];
    return cleanUrl.split('/').pop() || 'Media Item';
  }

  /**
   * Initialize gallery data from CLOUDINARY_CONFIG or default samples
   */
  function initGallery() {
    loadingSpinner.classList.remove('d-none');

    // Check if configuration exists
    if (typeof CLOUDINARY_CONFIG !== 'undefined' && Array.isArray(CLOUDINARY_CONFIG.mediaItems)) {
      allMediaItems = CLOUDINARY_CONFIG.mediaItems.map((item, index) => ({
        id: `media-${index}-${Date.now()}`,
        url: item.url,
        poster: item.poster || '',
        title: item.title || getFilename(item.url),
        type: item.type || detectMediaType(item.url),
        format: item.format || getFileExtension(item.url)
      }));
    } else {
      allMediaItems = [];
    }

    loadingSpinner.classList.add('d-none');
    renderGallery();
  }

  /**
   * Filter and render media items into grid
   */
  function renderGallery() {
    mediaGrid.innerHTML = '';

    // Filter items based on active tab and search query
    const filteredItems = allMediaItems.filter(item => {
      const matchesFilter = (currentFilter === 'all') || (item.type === currentFilter);
      const matchesSearch = item.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            getFilename(item.url).toLowerCase().includes(searchQuery.toLowerCase());
      return matchesFilter && matchesSearch;
    });

    // Update Counts
    updateCounts();

    if (filteredItems.length === 0) {
      emptyGallery.classList.remove('d-none');
      return;
    } else {
      emptyGallery.classList.add('d-none');
    }

    // Render cards
    filteredItems.forEach(item => {
      const col = document.createElement('div');
      col.className = 'col-12 col-sm-6 col-lg-4 col-xl-3';

      const filename = getFilename(item.url);
      const badgeText = item.type.toUpperCase();
      const badgeClass = item.type === 'image' ? 'badge-image' : 'badge-video';

      let mediaContentHtml = '';

      if (item.type === 'image') {
        mediaContentHtml = `
          <img src="${item.url}"
               alt="${item.title}"
               loading="lazy"
               onerror="handleMediaError(this, 'image')"
               class="img-fluid">
        `;
      } else {
        // Video
        const posterAttr = item.poster ? `poster="${item.poster}"` : '';
        mediaContentHtml = `
          <video ${posterAttr}
                 preload="metadata"
                 muted
                 playsinline
                 onerror="handleMediaError(this, 'video')">
            <source src="${item.url}" type="video/${item.format || 'mp4'}">
            Your browser does not support video playback.
          </video>
          <div class="video-play-overlay">
            <i class="bi bi-play-fill"></i>
          </div>
        `;
      }

      col.innerHTML = `
        <div class="card h-100 media-card shadow-sm">
          <div class="media-wrapper" data-id="${item.id}">
            <span class="media-badge ${badgeClass}">${badgeText}</span>
            ${mediaContentHtml}
          </div>
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <h6 class="card-title text-truncate fw-semibold mb-1" title="${item.title}">${item.title}</h6>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <small class="text-muted text-truncate font-monospace" style="font-size: 0.75rem;" title="${filename}">
                <i class="bi bi-file-earmark me-1"></i>${filename}
              </small>
              <button class="btn btn-sm btn-light text-primary border-0 preview-btn" data-id="${item.id}" title="Preview">
                <i class="bi bi-arrows-angle-expand"></i>
              </button>
            </div>
          </div>
        </div>
      `;

      // Event listener for opening lightbox
      const mediaWrapper = col.querySelector('.media-wrapper');
      const previewBtn = col.querySelector('.preview-btn');

      const openLightboxHandler = () => openLightbox(item);
      mediaWrapper.addEventListener('click', openLightboxHandler);
      previewBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        openLightbox(item);
      });

      mediaGrid.appendChild(col);
    });
  }

  /**
   * Fallback error handler for failed image/video loading
   */
  window.handleMediaError = function(element, type) {
    const parentWrapper = element.closest('.media-wrapper');
    if (parentWrapper) {
      const errorMessage = type === 'image' ? 'Image unavailable' : 'Video unavailable';
      const errorIcon = type === 'image' ? 'bi-image-fill' : 'bi-film';

      parentWrapper.innerHTML = `
        <div class="media-error-placeholder">
          <i class="bi ${errorIcon}"></i>
          <span>${errorMessage}</span>
        </div>
      `;
    }
  };

  /**
   * Update category badges counts
   */
  function updateCounts() {
    const totalCount = allMediaItems.length;
    const imageCount = allMediaItems.filter(i => i.type === 'image').length;
    const videoCount = allMediaItems.filter(i => i.type === 'video').length;

    if (countAll) countAll.textContent = totalCount;
    if (countImage) countImage.textContent = imageCount;
    if (countVideo) countVideo.textContent = videoCount;
  }

  /**
   * Open media lightbox preview modal
   */
  function openLightbox(item) {
    lightboxModalLabel.textContent = item.title;
    lightboxFilename.textContent = getFilename(item.url);

    if (item.type === 'image') {
      modalMediaTypeBadge.textContent = 'IMAGE';
      modalMediaTypeBadge.className = 'badge bg-primary';
      lightboxBody.innerHTML = `
        <img src="${item.url}" alt="${item.title}" class="img-fluid rounded">
      `;
    } else {
      modalMediaTypeBadge.textContent = 'VIDEO';
      modalMediaTypeBadge.className = 'badge bg-danger';
      const posterAttr = item.poster ? `poster="${item.poster}"` : '';
      lightboxBody.innerHTML = `
        <video controls autoplay ${posterAttr} style="max-height: 75vh; width: 100%;">
          <source src="${item.url}" type="video/${item.format || 'mp4'}">
          Your browser does not support playing this video.
        </video>
      `;
    }

    lightboxModal.show();
  }

  // Stop video playback when lightbox modal is closed
  lightboxModalEl.addEventListener('hidden.bs.modal', () => {
    lightboxBody.innerHTML = '';
  });

  // Filter Buttons Click Events
  filterButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      filterButtons.forEach(b => {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-outline-primary');
      });
      const targetBtn = e.currentTarget;
      targetBtn.classList.remove('btn-outline-primary');
      targetBtn.classList.add('btn-primary', 'active');

      currentFilter = targetBtn.getAttribute('data-filter');
      renderGallery();
    });
  });

  // Search Input Handler
  searchInput.addEventListener('input', (e) => {
    searchQuery = e.target.value.trim();
    renderGallery();
  });

  /**
   * Helper: Process ZIP File
   */
  async function processZipFile(file) {
    if (!file) return;

    if (typeof JSZip === 'undefined') {
      alert('JSZip library is loading or unavailable.');
      return;
    }

    loadingSpinner.classList.remove('d-none');
    mediaGrid.innerHTML = '';

    try {
      const zip = new JSZip();
      const zipContent = await zip.loadAsync(file);
      const zipMediaItems = [];

      const entries = Object.keys(zipContent.files);

      for (const filename of entries) {
        const zipObj = zipContent.files[filename];
        if (zipObj.dir) continue; // Skip directories

        const ext = getFileExtension(filename);
        const isImage = IMAGE_EXTENSIONS.includes(ext);
        const isVideo = VIDEO_EXTENSIONS.includes(ext);

        if (isImage || isVideo) {
          const blob = await zipObj.async('blob');
          const objectUrl = URL.createObjectURL(blob);
          const type = isImage ? 'image' : 'video';

          zipMediaItems.push({
            id: `zip-${Math.random().toString(36).substr(2, 9)}`,
            url: objectUrl,
            poster: '',
            title: filename.split('/').pop(),
            type: type,
            format: ext
          });
        }
      }

      if (zipMediaItems.length > 0) {
        allMediaItems = zipMediaItems;
        renderGallery();
      } else {
        alert('No supported image or video files were found inside the selected ZIP archive.');
        renderGallery();
      }
    } catch (err) {
      console.error('Error processing ZIP archive:', err);
      alert('Failed to read ZIP archive file.');
    } finally {
      loadingSpinner.classList.add('d-none');
    }
  }

  /**
   * Handle ZIP File Inputs & Drag and Drop Events
   */
  zipFileInput.addEventListener('change', async (e) => {
    await processZipFile(e.target.files[0]);
    zipFileInput.value = '';
  });

  if (dropZoneZipInput) {
    dropZoneZipInput.addEventListener('change', async (e) => {
      await processZipFile(e.target.files[0]);
      dropZoneZipInput.value = '';
    });
  }

  if (dropZone) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('dragover');
      }, false);
    });

    dropZone.addEventListener('drop', async (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files.length > 0) {
        const zipFile = Array.from(files).find(f => f.name.endsWith('.zip'));
        if (zipFile) {
          await processZipFile(zipFile);
        } else {
          alert('Please drop a valid .zip file containing media.');
        }
      }
    });
  }

  /**
   * Handle Media Folder Upload
   */
  folderFileInput.addEventListener('change', (e) => {
    const files = Array.from(e.target.files);
    if (!files || files.length === 0) return;

    const folderItems = [];

    files.forEach(file => {
      const ext = getFileExtension(file.name);
      const isImage = IMAGE_EXTENSIONS.includes(ext);
      const isVideo = VIDEO_EXTENSIONS.includes(ext);

      if (isImage || isVideo) {
        const objectUrl = URL.createObjectURL(file);
        folderItems.push({
          id: `folder-${Math.random().toString(36).substr(2, 9)}`,
          url: objectUrl,
          poster: '',
          title: file.name,
          type: isImage ? 'image' : 'video',
          format: ext
        });
      }
    });

    if (folderItems.length > 0) {
      allMediaItems = folderItems;
      renderGallery();
    } else {
      alert('No supported media files were found in the selected folder.');
    }

    folderFileInput.value = '';
  });

  // Initialize Gallery on Page Load
  initGallery();
});
