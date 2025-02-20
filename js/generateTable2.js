/* TABLE FUNCTIONS */
// For timetable

let selectedClassId = null;

function formatJSONTime(time) {
  let formattedHour = parseInt(time.split(":")[0], 10);
  return formattedHour < 10 ? `0${formattedHour}:00` : `${formattedHour}:00`;
}

function convertDateToDay(date, days) {
  let dateObject = new Date(date);
  let dayIndex = dateObject.getDay();
  
  return days[dayIndex - 1];
}

function formatDate(date) {
  let options = { weekday: 'long', month: 'short', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

function formatTime(hours, minutes) {
  // Ensure hours and minutes are formatted with leading zeros if necessary
  let formattedHours = hours < 10 ? `0${hours}` : `${hours}`;
  let formattedMinutes = minutes < 10 ? `0${minutes}` : `${minutes}`;

  // Return the formatted time string in HH:MM format
  return `${formattedHours}:${formattedMinutes}`;
}

function getWeekRange(startDate) {
  let start = new Date(startDate);
  start.setDate(start.getDate() - (start.getDay() === 0 ? 6 : start.getDay() - 1));
  let end = new Date(start);
  end.setDate(end.getDate() + 6);
  return { start, end };
}


function updateClassCell(classObject, cell) {
  // Extract start hour and minute
  let [startHour, startMinute] = classObject.startTime.split(":").map(Number);

  // Extract end hour and minute
  let [endHour, endMinute] = classObject.endTime.split(":").map(Number);

  // Calculate the duration in minutes
  let durationMinutes = (endHour - startHour) * 60 + (endMinute - startMinute);

  // Calculate the height of the block based on duration
  let blockHeight = durationMinutes * 2; // Assuming each minute corresponds to 2 pixels

  // Set the block's height and content directly on the cell
  cell.style.height = `${blockHeight}px`;
  cell.textContent = `${formatTime(startHour, startMinute)} - ${formatTime(endHour, endMinute)}`;
}

function calculateClassDuration(startTime, endTime) {
  let startHour = parseInt(startTime.split(":")[0], 10);
  let endHour = parseInt(endTime.split(":")[0], 10);
  return endHour - startHour;
}

function addDayCellColour(day) {
  switch (day) {
    case 'Monday':
      return 'lightblue';
    case 'Tuesday':
      return 'lightcoral';
    case 'Wednesday':
      return 'lightgreen';
    case 'Thursday':
      return 'lightpink';
    case 'Friday':
      return 'lightgoldenrodyellow';
  }
}

function createDateHeaders(daysOfWeek, startDate) {
  let monday = new Date(startDate);
  monday.setDate(monday.getDate() - (monday.getDay() === 0 ? 6 : monday.getDay() - 1));

  let headerRow = document.createElement("tr");
  let timeHeaderCell = document.createElement("th");
  timeHeaderCell.textContent = `Time/Date`;
  headerRow.appendChild(timeHeaderCell);

  daysOfWeek.forEach((day, index) => {
    let date = new Date(monday);
    date.setDate(monday.getDate() + index);
    let dateHeaderCell = document.createElement("th");
    dateHeaderCell.innerHTML = `${day}<br>${date.getDate()}/${date.getMonth() + 1}`;
    headerRow.appendChild(dateHeaderCell);
  });

  return headerRow;
}



// convert DB time to formatted time of HH:MM instead of HH:MM:SS
function populateTimetable(classes, startDate, userTypeID) {
  const timetableBody = document.getElementById("timetable-body");
  timetableBody.innerHTML = '';

  let dateHeaderRow = createDateHeaders(daysOfWeek, startDate);
  dateHeaderRow.classList.add('thead-dark');
  timetableBody.appendChild(dateHeaderRow);

  let spanMap = new Map();
  let weekRange = getWeekRange(startDate);

  for (let hour = 8; hour <= 18; hour++) {
    let row = document.createElement("tr");
    let timeCell = document.createElement("td");
    timeCell.textContent = `${hour}:00`;
    row.appendChild(timeCell);

    for (let dayOffset = 0; dayOffset < daysOfWeek.length; dayOffset++) {
      let dayDate = new Date(weekRange.start);
      dayDate.setDate(dayDate.getDate() + dayOffset);

      let dayDateString = dayDate.toISOString().split('T')[0]; // Format as "YYYY-MM-DD"

      if (spanMap.has(`${dayDateString}${hour}`)) {
        let spanCount = spanMap.get(`${dayDateString}${hour}`);
        if (spanCount > 1) {
          spanMap.set(`${dayDateString}${hour}`, spanCount - 1);
        } else {
          spanMap.delete(`${dayDateString}${hour}`);
        }
        continue;
      }

      let cell = document.createElement("td");
      cell.classList.add('align-middle');
      let checkedHour = hour < 10 ? `0${hour}:00` : `${hour}:00`;

      let classForTime = classes.find(c => {
  
        if (userTypeID === 2) {
          let classDate = c.lessonDate.split('T')[0]; // Assuming c.date is in "YYYY-MM-DD" format
          return classDate === dayDateString && formatJSONTime(c.startTime) === checkedHour;
        }else if (userTypeID === 3) {
          let classDate = c.date.split('T')[0]; // Assuming c.date is in "YYYY-MM-DD" format
        return classDate === dayDateString && formatJSONTime(c.startTime) === checkedHour;
        }
        
      });

      if (classForTime) {
        let duration = calculateClassDuration(classForTime.startTime, classForTime.endTime);
        cell.rowSpan = duration;

        cell.style.backgroundColor = addDayCellColour(daysOfWeek[dayOffset]);
        cell.innerHTML = `${classForTime.className}<br>${formatTime(hour, 0)} - ${formatTime(hour + duration, 0)}<br> ${classForTime.fname} ${classForTime.lname}`;

        cell.addEventListener('click', () => {
          classId = classForTime.classId;

          // Enable buttons
          document.getElementById('editButton').disabled = false;
          document.getElementById('deleteButton').disabled = false;

        });

        for (let spanHour = 1; spanHour < duration; spanHour++) {
          spanMap.set(`${dayDateString}${hour + spanHour}`, duration - spanHour);
        }
      }

      row.appendChild(cell);
    }

    timetableBody.appendChild(row);
  }
}


function convertDateToDayList (date, days) {
  let dateObject = new Date(date);
  let dayIndex = dateObject.getDay();
  dayIndex = dayIndex === 0 ? 6 : dayIndex - 1; // Adjust so that 0 (Sunday) becomes 6, and others are shifted by -1
  return days[dayIndex];
}

function populateListTimetable(data, daysOfWeek) {
  const timetableContainer = document.getElementById('timetable-container');
  timetableContainer.innerHTML = ''; // Clear any existing content

  let classesByDay = {
    "Monday": [],
    "Tuesday": [],
    "Wednesday": [],
    "Thursday": [],
    "Friday": [],
    "Saturday": [],
    "Sunday": []
  };

  // Populate classesByDay with the classes, grouped by day
  data.forEach(classObject => {
    let classDay = convertDateToDayList(classObject.date, daysOfWeek);
    classesByDay[classDay].push(classObject);
  });

  // Now create the list view grouped by days of the week
  daysOfWeek.forEach(day => {
    const dayCardContainer = document.createElement('div');
    dayCardContainer.className = 'day-card-container';

    const dayHeader = document.createElement('h3');
    dayHeader.textContent = day;
    dayCardContainer.appendChild(dayHeader);

    classesByDay[day].sort((a, b) => a.startTime.localeCompare(b.startTime)).forEach(classObject => {
      // Create card div
      const card = document.createElement('div');
      card.className = 'card mb-3'; // Bootstrap classes for margin bottom
      card.setAttribute('data-class-id', classObject.classId); // Set the class ID as a data attribute
      
      // Card body
      const cardBody = document.createElement('div');
      cardBody.className = 'card-body';

      // Card title and text
      const cardTitle = document.createElement('h5');
      cardTitle.className = 'card-title';
      cardTitle.textContent = `${classObject.className} with ${classObject.fname} ${classObject.lname}`;

      const cardText = document.createElement('p');
      cardText.className = 'card-text';
      cardText.textContent = `${formatJSONTime(classObject.startTime)} - ${formatJSONTime(classObject.endTime)}`;

      cardBody.appendChild(cardTitle);
      cardBody.appendChild(cardText);
      card.appendChild(cardBody);

      // Event listener for when the card is clicked
      card.addEventListener('click', function() {
        selectedClassId = this.getAttribute('data-class-id');
        classId = classObject.classId;
        document.getElementById('editButton').disabled = false;
        document.getElementById('deleteButton').disabled = false;
      
        // Remove the 'active' class from all cards
        document.querySelectorAll('.card').forEach(function(cardElement) {
          cardElement.classList.remove('active');
        });
      
        // Add the 'active' class to the clicked card
        this.classList.add('active');
      });

      // Add the card to the day container
      dayCardContainer.appendChild(card);
    });

    if (classesByDay[day].length === 0) {
      const noClassesCard = document.createElement('div');
      noClassesCard.className = 'card mb-3';
      const noClassesCardBody = document.createElement('div');
      noClassesCardBody.className = 'card-body';
      const noClassesCardText = document.createElement('p');
      noClassesCardText.className = 'card-text';
      noClassesCardText.textContent = 'No classes';
      noClassesCardBody.appendChild(noClassesCardText);
      noClassesCard.appendChild(noClassesCardBody);
      dayCardContainer.appendChild(noClassesCard);
    }

    timetableContainer.appendChild(dayCardContainer);
  });
}





