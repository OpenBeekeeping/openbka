// Interactive honeycomb background, from Can I Check My Bees: a soft glow and clearer cell
// outlines follow the pointer. Mouse and trackpad only; touch screens keep the faint dots.
(() => {
  const field = document.querySelector('.hexfield');
  if (!field || !matchMedia('(hover: hover) and (pointer: fine)').matches) return;
  let x = 0, y = 0, queued = false;
  const paint = () => { field.style.setProperty('--mx', `${x}px`); field.style.setProperty('--my', `${y}px`); queued = false; };
  addEventListener('pointermove', (e) => {
    if (e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
    x = e.clientX; y = e.clientY; field.classList.add('lit');
    if (!queued) { queued = true; requestAnimationFrame(paint); }
  }, { passive: true });
  document.documentElement.addEventListener('pointerleave', () => field.classList.remove('lit'));
  addEventListener('blur', () => field.classList.remove('lit'));
})();
