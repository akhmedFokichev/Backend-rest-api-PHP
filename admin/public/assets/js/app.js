/**
 * app.js — общая инициализация админки.
 *
 * Назначение: boot Alpine AppStore из #app-boot, автоскрытие flash-алертов.
 */
document.addEventListener('alpine:init', () => {
  const bootEl = document.getElementById('app-boot');
  if (!bootEl) return;

  let boot = {};
  try {
    boot = JSON.parse(bootEl.textContent || '{}');
  } catch (e) {
    boot = {};
  }

  // store.js регистрируется раньше; инициализируем после создания store
  queueMicrotask(() => {
    const store = Alpine.store('app');
    if (store && typeof store.initFromBoot === 'function') {
      store.initFromBoot(boot);
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach((el) => {
    if (el.closest('[x-data]') && el.hasAttribute('x-show')) {
      return;
    }
    setTimeout(() => el.remove(), 5000);
  });
});
