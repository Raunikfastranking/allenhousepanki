
var closeMenu = document.getElementById("closeMenu");
var NavMenu = document.getElementById("NavMenu");
var menubrop = document.getElementById("menubrop")
closeMenu.addEventListener('click',function(){
  NavMenu.style.webkitTransform = "translateX(-100%)"
})
menubrop.addEventListener('click',function(){
    NavMenu.style.webkitTransform = "translateX(0%)"
  })

document.querySelectorAll('p').forEach(paragraph => {
    paragraph.innerHTML = paragraph.innerHTML.replace(/\s([^\s<]{1,10})\s*$/, '&nbsp;$1');
});

var heroSlider = new Glide('.heroSlider', {
    type: 'slider',
    focusAt: 0,
    perView: 1,
    autoplay: 3500,
    animationDuration: 700,
    gap: 0,
    peek: 0,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1024: {
            type: 'slider',
            perView: 1,
            gap: 0,
            peek: 0,
            focusAt: 0
        },
        640: {
            type: 'slider',
            perView: 1,
            gap: 0,
            peek: 0,
            focusAt: 0
        }
    },

});
heroSlider.mount();

var excellence = new Glide('.excellence', {
    type: 'carousel',
    focusAt: 1,
    perView: 4,
    autoplay: 3500,
    animationDuration: 700,
    gap: 24,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1680: {
            perView: 4
        },
        1250: {
            perView: 3
        },
        1024: {
            perView: 2
        },
        955: {
            perView: 2
        },
        820: {
            perView: 2
        },
        640: {
            perView: 1
        }
    },
});
excellence.mount();

var philosophy = new Glide('.philosophy', {
    type: 'carousel',
    perView: 3,
    focusAt: 'center',
    autoplay: 3500,
    animationDuration: 700,
    gap: 0,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1680: {
            perView:4
        },
        1380: {
            perView:3
        },
        1024: {
            perView:2
        },
        767: {
            perView:2
        },
        640: {
            perView: 1
        }
    },
});
philosophy.mount();

var campus = new Glide('.campus', {
    type: 'carousel',
    focusAt: 1,
    perView: 4,
    autoplay: 3500,
    animationDuration: 700,
    gap: 24,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1680: {
            perView: 4
        },
        1024: {
            perView: 3
        },
        820: {
            perView: 2
        },
        640: {
            perView: 1
        }
    },
});
campus.mount();

var testimonials = new Glide('.Testimonials', {
    type: 'carousel',
    perView: 4,
    focusAt: 1,
    autoplay: 2000,
    rewind: true,
    animationDuration: 700,
    gap: 24,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1680: {
            perView: 4
        },
        1024: {
            perView: 3
        },
        820: {
            perView: 2
        },
        640: {
            perView: 1
        }
    },
});
testimonials.mount();

var latestNews = new Glide('.latestNews', {
    type: 'carousel',
    focusAt: 1,
    perView: 4,
    autoplay: 3500,
    animationDuration: 700,
    gap: 24,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1024: {
            perView: 4
        },
        640: {
            perView: 1
        }
    },
});
latestNews.mount();


// About Carsousel

var aboutCarousel = new Glide('.about-carousel', {
    type: 'carousel',
    focusAt: 'center',
    perView: 4,
    autoplay: 3500,
    animationDuration: 700,
    gap: 24,
    classes: {
        activeNav: '[&>*]:bg-slate-700',
    },
    breakpoints: {
        1680: {
            perView: 4
        },
        1024: {
            perView: 3
        },
        820: {
            perView: 2
        },
        640: {
            perView: 1
        }
    },
});
aboutCarousel.mount();

