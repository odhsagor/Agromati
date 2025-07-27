// Mobile Navigation Toggle
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');

navToggle.addEventListener('click', function() {
    navToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Sticky Navigation
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.main-nav');
    const backToTop = document.querySelector('.back-to-top');
    
    if (window.scrollY > 100) {
        nav.classList.add('scrolled');
        backToTop.classList.add('show');
    } else {
        nav.classList.remove('scrolled');
        backToTop.classList.remove('show');
    }
});

// Counter Animation
const counters = document.querySelectorAll('.counter');
const speed = 200;

counters.forEach(counter => {
    const updateCount = () => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCount, 1);
        } else {
            counter.innerText = target;
        }
    };

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            updateCount();
        }
    });

    observer.observe(counter);
});

// Testimonial Slider Navigation
let currentTestimonial = 0;
const testimonials = document.querySelectorAll('.testimonial-card');
const testimonialSlider = document.querySelector('.testimonial-slider');

function showTestimonial(index) {
    testimonials.forEach((testimonial, i) => {
        testimonial.style.transform = `translateX(${100 * (i - index)}%)`;
    });
}

// Auto-advance testimonials
setInterval(() => {
    currentTestimonial = (currentTestimonial + 1) % testimonials.length;
    showTestimonial(currentTestimonial);
}, 5000);

// Initialize
showTestimonial(currentTestimonial);