(function () {
  "use strict";

  // ======= Sticky
  window.onscroll = function () {
    const ud_header = document.querySelector(".ud-header");
    const sticky = ud_header.offsetTop;
    const logo = document.querySelector(".logo img");

    if (window.pageYOffset > sticky) {
      ud_header.classList.add("sticky");
    } else {
      ud_header.classList.remove("sticky");
    }

    // === logo change
  

    // show or hide the back-top-top button
    const backToTop = document.querySelector(".back-to-top");
    if (
      document.body.scrollTop > 50 ||
      document.documentElement.scrollTop > 50
    ) {
      backToTop.style.display = "flex";
    } else {
      backToTop.style.display = "none";
    }
  };

  //===== close navbar-collapse when a  clicked
  

  // ===== submenu
  const submenuButton = document.querySelectorAll(".nav-item-has-children");
  submenuButton.forEach((elem) => {
    elem.querySelector("a").addEventListener("click", () => {
      elem.querySelector(".ud-submenu").classList.toggle("show");
    });
  });
  

  // ===== wow js
  new WOW().init();

  // ====== scroll top js
  function scrollTo(element, to = 0, duration = 500) {
    const start = element.scrollTop;
    const change = to - start;
    const increment = 20;
    let currentTime = 0;

    const animateScroll = () => {
      currentTime += increment;

      const val = Math.easeInOutQuad(currentTime, start, change, duration);

      element.scrollTop = val;

      if (currentTime < duration) {
        setTimeout(animateScroll, increment);
      }
    };

    animateScroll();
  }

  Math.easeInOutQuad = function (t, b, c, d) {
    t /= d / 2;
    if (t < 1) return (c / 2) * t * t + b;
    t--;
    return (-c / 2) * (t * (t - 2) - 1) + b;
  };

  document.querySelector(".back-to-top").onclick = () => {
    scrollTo(document.documentElement);
  };
})();

var swiper = new Swiper(".mySwiper", {
  effect: "coverflow",
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: "auto",
  coverflowEffect: {
    rotate: 50,
    stretch: 0,
    depth: 100,
    modifier: 1,
    slideShadows: true,
  },
  pagination: {
    el: ".swiper-pagination",
  },
});



    window.addEventListener('DOMContentLoaded', (event) => {
        // Ambil tombol "Masuk" pada tampilan desktop
        const desktopLoginButton = document.querySelector('.desktop-login a');

        // Fungsi untuk memindahkan tombol "Masuk"
        function moveLoginButton() {
            // Ambil lebar layar browser
            const screenWidth = window.innerWidth;

            // Jika lebar layar kurang dari atau sama dengan 991px (tampilan mobile)
            if (screenWidth <= 991) {
                // Pindahkan tombol "Masuk" ke dalam elemen <li> pada tampilan mobile
                document.querySelector('.mobile-login').appendChild(desktopLoginButton);
            } else {
                // Jika lebar layar lebih dari 991px (tampilan desktop)
                // Kembalikan tombol "Masuk" ke posisi awal di luar elemen <li>
                document.querySelector('.desktop-login').appendChild(desktopLoginButton);
            }
        }

        // Panggil fungsi saat halaman dimuat dan saat layar berubah ukurannya
        moveLoginButton();
        window.addEventListener('resize', moveLoginButton);
    });



    AOS.init();

    const submenuButton = document.querySelectorAll(".nav-item-has-children");
    submenuButton.forEach((elem) => {
        elem.querySelector("a").addEventListener("click", () => {
            elem.querySelector(".ud-submenu").classList.toggle("show");
        });
    });




    let navbarToggler = document.querySelector(".navbar-toggler");
    const navbarCollapse = document.querySelector(".navbar-collapse");

    document.querySelectorAll(".ud-menu-scroll").forEach((e) =>
        e.addEventListener("click", () => {
            navbarToggler.classList.remove("active");
            navbarCollapse.classList.remove("show");
        })
    );
    navbarToggler.addEventListener("click", function() {
        navbarToggler.classList.toggle("active");
        navbarCollapse.classList.toggle("show");
    });


    window.addEventListener('scroll', function () {
      const header = document.querySelector('.ud-header');
      const logo = document.getElementById('logo');
    
      if (window.scrollY > 50) {
        header.classList.add('sticky');
        logo.src = 'assets2/images/genbibg.png'; // ganti dengan path logo berwarna kamu
      } else {
        header.classList.remove('sticky');
        logo.src = 'assets2/images/GenBI white (1).png'; // ganti dengan path logo putih kamu
      }
    });
    

