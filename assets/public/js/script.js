
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      if (entry.target.classList.contains('scroll-animate-left')) {
        entry.target.classList.add('animate-slide-left');
      }
      if (entry.target.classList.contains('scroll-animate-right')) {
        entry.target.classList.add('animate-slide-right');
      }
      if (entry.target.classList.contains('scroll-animate-top')) {
        entry.target.classList.add('animate-slide-top');
      }
    }
  });
}, { threshold: 0.2 });

document.querySelectorAll('[class*="scroll-animate"]').forEach(el => {
  observer.observe(el);
});
