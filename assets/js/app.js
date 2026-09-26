/* assets/js/app.js */
$(document.body).ready(function() {
  // Theme Toggle Logic
  const themeToggleBtn = $('#themeToggle');
  const themeIcon = $('#themeIcon');

  function applyTheme(theme) {
    $('html').attr('data-bs-theme', theme);
    localStorage.setItem('cms_theme', theme);
    if (theme === 'dark') {
      themeIcon.removeClass('fa-moon').addClass('fa-sun');
    } else {
      themeIcon.removeClass('fa-sun').addClass('fa-moon');
    }
  }

  const savedTheme = localStorage.getItem('cms_theme') || 'light';
  applyTheme(savedTheme);

  themeToggleBtn.on('click', function() {
    const currentTheme = $('html').attr('data-bs-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    applyTheme(newTheme);
  });

  // Sidebar Toggle Logic
  const sidebar = $('#sidebar');
  const sidebarOverlay = $('.sidebar-overlay');
  const toggleBtn = $('#sidebarToggle');

  toggleBtn.on('click', function() {
    if ($(window).width() < 992) {
      sidebar.toggleClass('show');
      sidebarOverlay.toggleClass('show');
    } else {
      sidebar.toggleClass('collapsed');
      $('#main-content').toggleClass('collapsed');
    }
  });

  sidebarOverlay.on('click', function() {
    sidebar.removeClass('show');
    sidebarOverlay.removeClass('show');
  });

  // Toast notification helper
  window.showToast = function(message, type = 'success') {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });

    Toast.fire({
      icon: type,
      title: message
    });
  };

  // Global Delete Confirmation Dialog
  window.confirmDelete = function(title, text, callback) {
    Swal.fire({
      title: title || 'Are you sure?',
      text: text || 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, Delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        callback();
      }
    });
  };
});
