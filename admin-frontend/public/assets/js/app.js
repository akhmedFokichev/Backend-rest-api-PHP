// Global admin helpers
document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach((el) => {
    setTimeout(() => el.remove(), 5000);
  });
});
