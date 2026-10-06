let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');
let menuButton = document.querySelector('#menu-btn');
let userButton = document.querySelector('#user-btn');

if (menuButton) {
   menuButton.onclick = () =>{
      navbar?.classList.toggle('active');
      profile?.classList.remove('active');
   }
}

if (userButton) {
   userButton.onclick = () =>{
      profile?.classList.toggle('active');
      navbar?.classList.remove('active');
   }
}

window.onscroll = () =>{
   navbar?.classList.remove('active');
   profile?.classList.remove('active');
}

let mainImage = document.querySelector('.update-product .image-container .main-image img');
let subImages = document.querySelectorAll('.update-product .image-container .sub-image img');

subImages.forEach(images =>{
   images.onclick = () =>{
      src = images.getAttribute('src');
      mainImage.src = src;
   }
});
