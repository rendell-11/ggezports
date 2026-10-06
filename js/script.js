let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');
let menuBtn = document.querySelector('#menu-btn');
let userBtn = document.querySelector('#user-btn');

if (menuBtn && navbar && profile) {
   menuBtn.onclick = () =>{
      navbar.classList.toggle('active');
      profile.classList.remove('active');
   }
}

if (userBtn && navbar && profile) {
   userBtn.onclick = () =>{
      profile.classList.toggle('active');
      navbar.classList.remove('active');
   }
}

window.onscroll = () =>{
   if (navbar) {
      navbar.classList.remove('active');
   }

   if (profile) {
      profile.classList.remove('active');
   }
}

let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');
let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');

subImages.forEach(images =>{
   images.onclick = () =>{
      let src = images.getAttribute('src');

      if (mainImage) {
         mainImage.src = src;
      }
   }
});
