let menu=document.querySelector('#menu-icon');
let navbar=document.querySelector('.navbar');
menu.onclick=()=>{
 menu.classList.toggle('bx-x');
    navbar.classList.toggle('active');
}
window.onscroll=()=>{
    menu.classList.remove('bx-x');
    navbar.classList.remove('active');
}
const typed = new Typed('.multiple-test', {
    strings: ['studies CSE at KUET','am a Mobile App Developer(Native & Cross-platformed)', 'am a Full-stack Web developer'],
    typeSpeed: 80,
    backSpeed: 80,
    backdelay:1200,
    loop: true
  });
  const typed2 = new Typed('.multiple-info', {
    strings: ['studies CSE at KUET','am a Mobile App Developer(Native & Cross-platformed)', 'am a Full-stack Web developer.'],
    typeSpeed: 80,
    backSpeed: 80,
    backdelay:1200,
    loop: true
  });