<!DOCTYPE html>
<html lang="en">

<head>
  <title>About Us</title>
  <!-- CSS Files -->
  <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-5/assets/css/about-5.css" />
  <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-1/assets/css/about-1.css" />
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php
  include "inc/head.inc.php";
  ?>
</head>

<body>
  <?php
  include "inc/nav.inc.php";
  ?>
  <!-- Success Message Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="successModalLabel">Success!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Your message has been sent successfully!
        </div>
      </div>
    </div>
  </div>
  <!-- ======= Contact Section ======= -->
  <section id="contact" class="contact">
    <div data-aos="fade-up">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.7220781180426!2d103.77330417587022!3d1.3431492615954845!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da11d085b7bbbf%3A0xa1c1d2dd91fe299d!2sBukit%20Timah%20Shopping%20Centre!5e0!3m2!1sen!2ssg!4v1710872433099!5m2!1sen!2ssg" style="border:0; width: 100%; height: 350px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <!-- <iframe style="border:0; width: 100%; height: 350px;" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d12097.433213460943!2d-74.0062269!3d40.7101282!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xb89d1fe6bc499443!2sDowntown+Conference+Center!5e0!3m2!1smk!2sbg!4v1539943755621" frameborder="0" allowfullscreen></iframe> -->
    </div>

    <div class="container" data-aos="fade-up">

      <div class="row mt-5">

        <div class="col-lg-4">
          <div class="info">
            <div class="address">
              <h4>Main Location:</h4>
              <p>Bukit Timah Shopping Centre, 170 Upper Bukit Timah Rd, Singapore 588179</p><br>
              <p>Other locations: City Square Mall, Goldhill Plaza, Royal Square, Bugis Junction, Our Tampines Hub, Parkway Parade</p>
            </div>

            <div class="email">
              <h4>Email:</h4>
              <p>info@thetuitioncenter.com</p>
            </div>

            <div class="phone">
              <h4>Call:</h4>
              <p>+65 9052 2420</p>
            </div>

          </div>

        </div>

        <div class="col-lg-8 mt-5 mt-lg-0">

          <form action="contactus/process_contactus.php" method="post" role="form" class="php-email-form">
            <div class="row">
              <div class="col-md-6 form-group">
                <label for="name">Your name:</label><br>
                <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
              </div>
              <div class="col-md-6 form-group mt-3 mt-md-0">
                <label for="email">Email address:</label><br>
                <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
              </div>
            </div>
            <div class="form-group mt-3">
              <label for="contactno">Contact No:</label><br>
              <input type="text" class="form-control" name="contactno" id="contactno" placeholder="Contact No." required>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label for="acadLvl">Student's Academic Level:</label>
                <select name="acadLvl" id="acadLvl" required>
                  <option value="" disabled selected>Select</option>
                  <option value="Secondary 1">Secondary 1</option>
                  <option value="Secondary 2">Secondary 2</option>
                  <option value="Secondary 3">Secondary 3</option>
                  <option value="Secondary 4">Secondary 4</option>
                </select>
              </div>
              <div class="col-md-6 form-group mt-3 mt-md-0">
                <label for="subject">Subject:</label>
                <select name="subject" id="subject" required>
                  <option value="" disabled selected>Select</option>
                  <option value="English">English</option>
                  <option value="Mathematics">Mathematics</option>
                  <option value="Science">Science</option>
                </select>
              </div>
            </div>
            <div class="form-group mt-3">
              <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
            </div>

            <div class="text-center" style="margin-bottom: 30px;"><button type="submit">Send Message</button></div>
          </form>

        </div>

      </div>

    </div>
  </section><!-- End Contact Section -->
  <?php
  include "inc/footer.inc.php";
  ?>
  </main>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function(){
      var form = document.querySelector('.php-email-form');
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);

        fetch('contactus/process_contactus.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            localStorage.setItem('formSuccess', 'true');
            window.location.reload(); // Reload the page or redirect
          } else {
            // Handle errors, maybe show them in a div on the form
            document.querySelector('.error-message').innerText = data.message;
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
      });

      // Check for success message in localStorage
      if(localStorage.getItem('formSuccess') === 'true') {
        new bootstrap.Modal(document.getElementById('successModal')).show();
        localStorage.removeItem('formSuccess'); // Clear the local storage
      }
    });

  </script>
</body>

</html>
