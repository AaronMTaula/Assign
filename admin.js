/*
  File: admin.js
  Author: Aaron Taula - 15905800
  Description: Handles search, current bookings, past rides, and taxi
               assignment on the admin page.

  Functions:
    searchBookings()            - validates input and fetches results from admin.php
    loadCurrentBookings()       - loads all unassigned/upcoming bookings into the current section
    loadPastRides()             - loads all past bookings into the past rides section
    buildTable(bookings, showAssignBtn) - builds an HTML table from a bookings array
    formatDateTime(date, time)  - formats date and time for display
    assignTaxi(brn, btn)        - sends an assign request to admin.php
*/

// formatDateTime - converts YYYY-MM-DD and HH:MM:SS into DD/MM/YYYY HH:MM
function formatDateTime(date, time) {
  var parts = date.split('-');
  var displayDate = parts[2] + '/' + parts[1] + '/' + parts[0];
  var displayTime = time.substring(0, 5);
  return displayDate + ' ' + displayTime;
}

// buildTable - builds an HTML table from a bookings array
// showAssignBtn controls whether the Assign button column is shown
function buildTable(bookings, showAssignBtn) {
  if (bookings.length === 0) {
    return '<p>No bookings found.</p>';
  }

  var html = '<table>';
  html += '<tr>';
  html += '<th>Booking Reference Number</th>';
  html += '<th>Customer Name</th>';
  html += '<th>Phone</th>';
  html += '<th>Pickup Suburb</th>';
  html += '<th>Destination Suburb</th>';
  html += '<th>Pickup Date and Time</th>';
  html += '<th>Status</th>';
  if (showAssignBtn) {
    html += '<th>Assign</th>';
  }
  html += '</tr>';

  for (var i = 0; i < bookings.length; i++) {
    var b = bookings[i];
    var displayDateTime = formatDateTime(b.pickup_date, b.pickup_time);
    var isAssigned  = (b.status === 'assigned');
    var statusClass = isAssigned ? 'status-assigned' : 'status-unassigned';

    html += '<tr id="row-' + b.booking_ref + '">';
    html += '<td>' + b.booking_ref + '</td>';
    html += '<td>' + b.cname + '</td>';
    html += '<td>' + b.phone + '</td>';
    html += '<td>' + (b.sbname  || '-') + '</td>';
    html += '<td>' + (b.dsbname || '-') + '</td>';
    html += '<td>' + displayDateTime + '</td>';
    html += '<td class="' + statusClass + '" id="status-' + b.booking_ref + '">' + b.status + '</td>';

    if (showAssignBtn) {
      var btnDisabled = isAssigned ? 'disabled' : '';
      var btnLabel    = isAssigned ? 'Assigned' : 'Assign';
      html += '<td><button name="Assign" ' + btnDisabled +
              ' onclick="assignTaxi(\'' + b.booking_ref + '\', this)">' +
              btnLabel + '</button></td>';
    }

    html += '</tr>';
  }

  html += '</table>';
  return html;
}

// searchBookings - called when the Search Bookings button is clicked
async function searchBookings() {
  document.getElementById('err-bsearch').textContent = '';
  document.getElementById('resultsContainer').innerHTML = '';
  document.getElementById('assignMessage').textContent = '';

  var searchVal = document.getElementById('bsearch').value.trim();

  // If user typed something, check it matches the BRN format
  if (searchVal !== '') {
    if (!/^BRN\d{5}$/.test(searchVal)) {
      document.getElementById('err-bsearch').textContent =
        'Invalid format. Must be like BRN00001 (BRN + 5 digits).';
      return;
    }
  }

  var response = await fetch('admin.php?action=search&bsearch=' + encodeURIComponent(searchVal));
  var result   = await response.json();

  if (result.success) {
    // Show assign button in search results so staff can assign from here
    document.getElementById('resultsContainer').innerHTML = buildTable(result.bookings, true);
  } else {
    document.getElementById('resultsContainer').innerHTML = '<p>' + result.message + '</p>';
  }
}

// loadCurrentBookings - loads all current and upcoming bookings into the current section
// Shows all bookings from now onwards (not just within 2 hours)
async function loadCurrentBookings() {
  var response = await fetch('admin.php?action=current');
  var result   = await response.json();

  if (result.success) {
    // Show assign button so staff can assign directly from this section
    document.getElementById('currentBookings').innerHTML = buildTable(result.bookings, true);
  } else {
    document.getElementById('currentBookings').innerHTML = '<p>' + result.message + '</p>';
  }
}

// loadPastRides - loads all past bookings into the past rides section
async function loadPastRides() {
  var response = await fetch('http://webdev.aut.ac.nz/~cdt6246/assign/admin.php?action=');
  var result   = await response.json();

  if (result.success) {
    // No assign button for past rides - they are already done
    document.getElementById('pastRides').innerHTML = buildTable(result.bookings, false);
  } else {
    document.getElementById('pastRides').innerHTML = '<p>' + result.message + '</p>';
  }
}

// assignTaxi - sends an assign request to admin.php when Assign is clicked
async function assignTaxi(brn, btn) {
  btn.disabled = true;
  btn.textContent = 'Assigning...';

  var response = await fetch('http://webdev.aut.ac.nz/~cdt6246/assign/admin.php?action=' + encodeURIComponent(brn));
  var result   = await response.json();

  if (result.success) {
    // Update every status cell on the page that matches this BRN
    var statusCells = document.querySelectorAll('#status-' + brn);
    statusCells.forEach(function(cell) {
      cell.textContent = 'assigned';
      cell.className = 'status-assigned';
    });
    btn.textContent = 'Assigned';

    // Show confirmation message
    document.getElementById('assignMessage').textContent =
      'Congratulations! Booking request ' + brn + ' has been assigned!';

    // Refresh both sections so the data stays accurate
    loadCurrentBookings();
    loadPastRides();

  } else {
    btn.disabled = false;
    btn.textContent = 'Assign';
    document.getElementById('assignMessage').textContent = 'Error: ' + result.message;
  }
}
