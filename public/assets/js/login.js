document.addEventListener("DOMContentLoaded", function () {

const images = [
                "../assets/images/medicine-doctor-with-stethoscope-in-hand-on-hospital-background-medical-technology-healthcare-and-medical-concept-photo.jpg",
                "../assets/images/stethoscope-pills-desk.jpg",
                "../assets/images/61806.jpg",
                "../assets/images/laptop-medical-equipment.jpg",
                "../assets/images/a-serene-hospital-corridor-featuring-modern-design-plants-and-soft-lighting-creating-a-calming-atmosphere-photo.jpg"
];
const loginBg = document.getElementById("login-bg");

    
images.sort(() => Math.random() - 0.5);

let slides = [];
let currentIndex = 0;

// Create slides for login background images
images.forEach((img, i) => {
    const div = document.createElement("div");
    div.className = "bg-slide" + (i === 0 ? " active" : "");
    div.style.backgroundImage = `url(${img})`;
    loginBg.appendChild(div);
    slides.push(div);
});

// fade logic in login bg images
setInterval(() => {
   slides[currentIndex].classList.remove("active");
   currentIndex = (currentIndex + 1) % slides.length;
   slides[currentIndex].classList.add("active");
}, 6000); // change background image every 5 seconds
    
    console.log("Slides created:", slides.length);
console.log(slides);
});