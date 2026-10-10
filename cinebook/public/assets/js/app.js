document.addEventListener("DOMContentLoaded", function () {

    const slides = document.querySelectorAll(".hero-slide");
    const nextBtn = document.querySelector(".hero-next");
    const prevBtn = document.querySelector(".hero-prev");

    let current = 0;

    if (slides.length === 0) return;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove("active"));
        slides[index].classList.add("active");
    }

    nextBtn?.addEventListener("click", () => {
        current = (current + 1) % slides.length;
        showSlide(current);
    });

    prevBtn?.addEventListener("click", () => {
        current = (current - 1 + slides.length) % slides.length;
        showSlide(current);
    });

    setInterval(() => {
        current = (current + 1) % slides.length;
        showSlide(current);
    }, 4000);

});