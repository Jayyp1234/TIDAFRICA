/*=============== SWIPER Js Form Home Page ===============*/
var swiper = new Swiper(".energy-slider", {
    slidesPerView: 3,
    spaceBetween: 30,
    grabCursor: true,
    slidesPerView: 'auto',
    loop: true,
    autoplay: {
        delay: 1500,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        320: {
            slidesPerView: 1,
        },
        480: {
            slidesPerView: 2,
        },
        768: {
            slidesPerView: 3,
        },
        1200: {
            slidesPerView: 4,
        },
    },
    mousewheel: true,
    keyboard: true,
});

//Countdown
let valueDisplays = document.querySelectorAll('.num');
let interval = 10000;

valueDisplays.forEach((valueDisplays) => {
    let startValue = 0;
    let endValue = parseInt(valueDisplays.getAttribute('data-val'));
    let duration = Math.floor(interval / endValue);

    let counter = setInterval(function(){
        startValue += 1;
        valueDisplays.textContent = startValue;

        if(startValue == endValue) {
            clearInterval(counter);
        }
    }, duration);
});

/*=============== SWIPER Js Form About Page ===============*/
var swiper = new Swiper(".team-slider", {
    effect: "coverflow",
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: "auto",
    coverflowEffect: {
        rotate: 0,
        stretch: -10,
        depth: 300,
        modifier: 1,
        slideShadows: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
    },
});

/*=============== SWIPER Js Form Home Page ===============*/
var swiper = new Swiper(".partner-swiper", {
    slidesPerView: 3,
    spaceBetween: 30,
    grabCursor: true,
    slidesPerView: 'auto',
    loop: true,
    autoplay: {
        delay: 1500,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        320: {
            slidesPerView: 3,
        },
        480: {
            slidesPerView: 4,
        },
        768: {
            slidesPerView: 5,
        },
    },
    mousewheel: true,
    keyboard: true,
});