document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("calendar");
  var selectedClasses = [];
  var proceedToPaymentBtn = document.getElementById("submitBtn");

  // Update submit button state
  function updateButtonState() {
    if (selectedClasses.length > 0) {
      proceedToPaymentBtn.disabled = false;
    } else {
      proceedToPaymentBtn.disabled = true;
    }
  }

  // Convert to local time
  function toLocalISOString(date) {
    var tzOffset = date.getTimezoneOffset() * 60000; // Convert offset to milliseconds
    var localISOTime = new Date(date - tzOffset).toISOString().slice(0, -1);
    return localISOTime;
  }

  // Create the calendar from fullcalendar API
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "timeGridWeek",
    allDaySlot: false,
    events: [],

    // When clicking on a date
    eventClick: function (info) {
      var uniqueEventId =
        info.event.id +
        "-" +
        FullCalendar.formatDate(info.event.start, {
          year: "numeric",
          month: "2-digit",
          day: "2-digit",
        });

      var eventIndex = selectedClasses.findIndex(function (classInfo) {
        return classInfo.uniqueEventId === uniqueEventId;
      });
      if (eventIndex !== -1) {
        selectedClasses.splice(eventIndex, 1);
        info.event.setProp("backgroundColor", "#78C2AD");
      } else {
        selectedClasses.push({
          uniqueEventId: uniqueEventId,
          id: info.event.id,
          title: info.event.title,
          price: parseFloat(info.event.extendedProps.price),
          startTime: toLocalISOString(info.event.start),
          endTime: toLocalISOString(info.event.end),
        });
        info.event.setProp("backgroundColor", "#ff9f89");
      }
      updateButtonState();
    },
  });

  // Fetch all classes when the page loads and populate the calendar
  fetch("bookings/bookings_get_slots.php")
    .then((response) => response.json())
    .then((data) => {
      calendar.removeAllEvents();
      let now = new Date();
      let endOfMay = new Date(new Date().getFullYear(), 4, 31);

      data.forEach((classInfo) => {
        let yearStart = new Date(new Date().getFullYear(), 0, 1);

        // Loop over each week in the year and set the days on Monday to Fridays
        for (
          let dt = new Date(yearStart);
          dt <= endOfMay;
          dt.setDate(dt.getDate() + 1)
        ) {
          if (dt.getDay() >= 2 && dt.getDay() <= 6) {
            let dateStr = dt.toISOString().split("T")[0];
            let eventStartStr = dateStr + "T" + classInfo.start;
            let eventEndStr = dateStr + "T" + classInfo.end;
            let eventStart = new Date(eventStartStr);
            let eventEnd = new Date(eventEndStr);

            // Check if the event start time is in the future and add it
            if (eventStart <= endOfMay && eventStart >= now) {
              let uniqueId = `${classInfo.id}-${dateStr}`;

              calendar.addEvent({
                id: uniqueId,
                title: classInfo.title,
                start: eventStartStr,
                end: eventEndStr,
                extendedProps: {
                  price: classInfo.price,
                },
                backgroundColor: "#78C2AD",
                borderColor: "#78C2AD",
              });
            }
          }
        }
      });

      calendar.render();
    })
    .catch((error) => {
      console.error("Error fetching class slots:", error);
    });

  calendar.render();

  // Navigate to payment after clicking the button
  proceedToPaymentBtn.addEventListener("click", function () {
    if (selectedClasses.length > 0) {
      var selectedClassesJSON = JSON.stringify(selectedClasses);

      var form = document.createElement("form");
      form.method = "POST";
      form.action = "payment.php";

      var hiddenField = document.createElement("input");
      hiddenField.type = "hidden";
      hiddenField.name = "selectedClasses";
      hiddenField.value = selectedClassesJSON;
      form.appendChild(hiddenField);

      document.body.appendChild(form);
      form.submit();
    }
  });
});
