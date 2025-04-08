document.addEventListener("DOMContentLoaded", function () {
  // Code to be executed when the DOM is ready (i.e. the document is
  // fully loaded):
  console.log("test");
  registerEventListeners(); // You need to write this function...
  activateMenu();
});

/*
 * This function sets the currently selected menu item to the 'active' state.
 * It should be called whenever the page first loads.
 */
function activateMenu() {
  const navLinks = document.querySelectorAll("nav a");
  navLinks.forEach((link) => {
    if (link.href === location.href) {
      link.classList.add("active");
    }
  });
}

function registerEventListeners() {
  const images = document.getElementsByClassName("img-thumbnail");
  if (images !== null) {
    for (let image of images) {
      image.addEventListener("click", function () {
        removeExistingPopup();
        const popupContent = document.createElement("span");
        const popupImage = document.createElement("img");
        popupContent.classList.add("img-popup");
        popupImage.src = image.src;
        popupContent.appendChild(popupImage);
        image.insertAdjacentElement("afterend", popupContent);
        popupContent.addEventListener("click", function () {
          removeExistingPopup();
        });
      });
    }
  }
}

function removeExistingPopup() {
  const existingPopup = document.querySelector(".img-popup");
  if (existingPopup) {
    existingPopup.parentNode.removeChild(existingPopup);
  }
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add animation class when elements come into view
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(element => observer.observe(element));
};

// Initialize animations
document.addEventListener('DOMContentLoaded', animateOnScroll);
