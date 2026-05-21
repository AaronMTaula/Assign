/*
  File: booking.js
  Author: Aaron Taula - 15905800
  Description: Handles validation and form submission for the booking page.

  Functions:
    setDefaultDateTime() - fills date and time inputs with current values
    clearErrors()        - removes all error messages from the page
    validateForm()       - checks all required fields are filled correctly
    submitBooking()      - sends the form data to booking.php using fetch
*/

// setDefaultDateTime - runs on page load to pre-fill date and time fields
function setDefaultDateTime() {
  var now = new Date();

  // Build date string in YYYY-MM-DD format for the date input
  var year  = now.getFullYear();
  var month = String(now.getMonth() + 1).padStart(2, '0');
  var day   = String(now.getDate()).padStart(2, '0');
  document.getElementById('date').value = year + '-' + month + '-' + day;

  // Build time string in HH:MM format for the time input
  var hours   = String(now.getHours()).padStart(2, '0');
  var minutes = String(now.getMinutes()).padStart(2, '0');
  document.getElementById('time').value = hours + ':' + minutes;
}

// clearErrors - clears all error messages before re-validating
function clearErrors() {
  var errorSpans = document.querySelectorAll('.error');
  for (var i = 0; i < errorSpans.length; i++) {
    errorSpans[i].textContent = '';
  }
}

// validateForm - checks inputs and shows errors if something is wrong
// Returns true if everything is valid, false if there are errors
function validateForm() {
  clearErrors();
  var valid = true;

  var cname   = document.getElementById('cname').value.trim();
  var phone   = document.getElementById('phone').value.trim();
  var snumber = document.getElementById('snumber').value.trim();
  var stname  = document.getElementById('stname').value.trim();
  var date    = document.getElementById('date').value;
  var time    = document.getElementById('time').value;

  // Check customer name is not empty
  if (cname === '') {
    document.getElementById('err-cname').textContent = 'Customer name is required.';
    valid = false;
  }

  // Check phone is not empty and is 10-12 digits only
  if (phone === '') {
    document.getElementById('err-phone').textContent = 'Phone number is required.';
    valid = false;
  } else if (!/^\d{10,12}$/.test(phone)) {
    document.getElementById('err-phone').textContent = 'Phone must be 10 to 12 digits, numbers only.';
    valid = false;
  }

  // Check street number is not empty
  if (snumber === '') {
    document.getElementById('err-snumber').textContent = 'Street number is required.';
    valid = false;
  }

  // Check street name is not empty
  if (stname === '') {
    document.getElementById('err-stname').textContent = 'Street name is required.';
    valid = false;
  }

  // Check date is not empty
  if (date === '') {
    document.getElementById('err-date').textContent = 'Pick-up date is required.';
    valid = false;
  }

  // Check time is not empty
  if (time === '') {
    document.getElementById('err-time').textContent = 'Pick-up time is required.';
    valid = false;
  }

  // Check the pickup date and time are not in the past
  if (date !== '' && time !== '') {
    var pickupDateTime = new Date(date + 'T' + time);
    var now = new Date();
    if (pickupDateTime < now) {
      document.getElementById('err-date').textContent = 'Pick-up date and time cannot be in the past.';
      valid = false;
    }
  }

  return valid;
}

// submitBooking - called when the Book Taxi button is clicked
// Validates the form then sends data to booking.php using fetch
async function submitBooking() {

  // Stop here if validation fails
  if (!validateForm()) {
    return;
  }

  // Collect all form values into an object
  var data = {
    cname:   document.getElementById('cname').value.trim(),
    phone:   document.getElementById('phone').value.trim(),
    unumber: document.getElementById('unumber').value.trim(),
    snumber: document.getElementById('snumber').value.trim(),
    stname:  document.getElementById('stname').value.trim(),
    sbname:  document.getElementById('sbname').value.trim(),
    dsbname: document.getElementById('dsbname').value.trim(),
    date:    document.getElementById('date').value,
    time:    document.getElementById('time').value
  };

  // Send the data to booking.php as a POST request with JSON body
  var response = await fetch('http://webdev.aut.ac.nz/~cdt6246/assign/booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });

  var result = await response.json();

  // If successful, show the confirmation message on the page
  if (result.success) {
    // Convert date from YYYY-MM-DD to DD/MM/YYYY for display
    var parts = result.pickup_date.split('-');
    var displayDate = parts[2] + '/' + parts[1] + '/' + parts[0];

    // Insert confirmation into the #reference div on the same page
    document.getElementById('reference').innerHTML =
      '<p id="reference">Thank you for your booking!<br>' +
      'Booking reference number: ' + result.brn + '<br>' +
      'Pickup time: ' + result.pickup_time + '<br>' +
      'Pickup date: ' + displayDate + '</p>';

    // Reset the form after successful booking
    document.getElementById('bookingForm').reset();
    setDefaultDateTime();

  } else {
    // Show error message from server
    document.getElementById('reference').innerHTML =
      '<p style="color:red;">Error: ' + result.message + '</p>';
  }
}
